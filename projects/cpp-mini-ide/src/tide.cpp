// tide - a tiny terminal IDE for C++ (Termux & Linux)
//
// A single-file ncurses editor with an integrated build/run pipeline:
// it edits C++ source, compiles it with clang++/g++ and runs the result
// without leaving the editor.
//
// Keys:
//   Ctrl+S  save            Ctrl+O  open file        Ctrl+N  new file
//   Ctrl+B  build (compile) Ctrl+R  build + run      Ctrl+F  find
//   Ctrl+K  delete line     Ctrl+G  help             Ctrl+Q  quit
//
// Build:  g++ -std=c++17 -O2 -Wall -o tide src/tide.cpp -lncurses

#include <ncurses.h>

#include <algorithm>
#include <cctype>
#include <cstdio>
#include <cstdlib>
#include <cstring>
#include <ctime>
#include <fstream>
#include <string>
#include <sys/wait.h>
#include <unordered_set>
#include <vector>

#define CTRL(c) ((c) & 0x1f)

static const int TAB_WIDTH = 4;

// ---------------------------------------------------------------------------
// Syntax highlighting
// ---------------------------------------------------------------------------

enum Highlight : unsigned char {
    HL_NORMAL = 0,
    HL_KEYWORD,
    HL_TYPE,
    HL_NUMBER,
    HL_STRING,
    HL_COMMENT,
    HL_PREPROC,
};

static const std::unordered_set<std::string> KEYWORDS = {
    "alignas", "alignof", "and", "asm", "break", "case", "catch", "class",
    "concept", "const", "consteval", "constexpr", "constinit", "const_cast",
    "continue", "co_await", "co_return", "co_yield", "decltype", "default",
    "delete", "do", "dynamic_cast", "else", "enum", "explicit", "export",
    "extern", "false", "final", "for", "friend", "goto", "if", "inline",
    "mutable", "namespace", "new", "noexcept", "not", "nullptr", "operator",
    "or", "override", "private", "protected", "public", "register",
    "reinterpret_cast", "requires", "return", "sizeof", "static",
    "static_assert", "static_cast", "struct", "switch", "template", "this",
    "thread_local", "throw", "true", "try", "typedef", "typeid", "typename",
    "union", "using", "virtual", "volatile", "while",
};

static const std::unordered_set<std::string> TYPES = {
    "auto", "bool", "char", "char8_t", "char16_t", "char32_t", "double",
    "float", "int", "long", "short", "signed", "unsigned", "void", "wchar_t",
    "size_t", "int8_t", "int16_t", "int32_t", "int64_t", "uint8_t",
    "uint16_t", "uint32_t", "uint64_t", "string", "vector", "map", "set",
    "pair", "array", "deque", "list", "queue", "stack", "unordered_map",
    "unordered_set", "shared_ptr", "unique_ptr", "optional", "variant",
};

static bool isSeparator(char c) {
    return isspace((unsigned char)c) || c == '\0' ||
           strchr(",.()+-/*=~%<>[]{};:!?&|^", c) != nullptr;
}

// Highlights one line. `inComment` carries /* ... */ state across lines.
static std::vector<unsigned char> highlightLine(const std::string& line,
                                                bool& inComment) {
    std::vector<unsigned char> hl(line.size(), HL_NORMAL);
    size_t i = 0;
    bool prevSep = true;

    // Preprocessor directive: color the whole line until a comment starts.
    size_t firstNonSpace = line.find_first_not_of(" \t");
    bool preproc = !inComment && firstNonSpace != std::string::npos &&
                   line[firstNonSpace] == '#';

    while (i < line.size()) {
        char c = line[i];

        if (inComment) {
            hl[i] = HL_COMMENT;
            if (c == '*' && i + 1 < line.size() && line[i + 1] == '/') {
                hl[i + 1] = HL_COMMENT;
                i += 2;
                inComment = false;
                prevSep = true;
                continue;
            }
            i++;
            continue;
        }

        if (c == '/' && i + 1 < line.size() && line[i + 1] == '/') {
            for (size_t j = i; j < line.size(); j++) hl[j] = HL_COMMENT;
            break;
        }
        if (c == '/' && i + 1 < line.size() && line[i + 1] == '*') {
            hl[i] = hl[i + 1] = HL_COMMENT;
            i += 2;
            inComment = true;
            continue;
        }

        if (c == '"' || c == '\'') {
            char quote = c;
            hl[i] = HL_STRING;
            i++;
            while (i < line.size()) {
                hl[i] = HL_STRING;
                if (line[i] == '\\' && i + 1 < line.size()) {
                    hl[i + 1] = HL_STRING;
                    i += 2;
                    continue;
                }
                if (line[i] == quote) {
                    i++;
                    break;
                }
                i++;
            }
            prevSep = true;
            continue;
        }

        if (preproc) {
            hl[i] = HL_PREPROC;
            i++;
            continue;
        }

        if (isdigit((unsigned char)c) && prevSep) {
            while (i < line.size() &&
                   (isalnum((unsigned char)line[i]) || line[i] == '.' ||
                    line[i] == 'x' || line[i] == '\'')) {
                hl[i] = HL_NUMBER;
                i++;
            }
            prevSep = false;
            continue;
        }

        if (prevSep && (isalpha((unsigned char)c) || c == '_')) {
            size_t start = i;
            while (i < line.size() &&
                   (isalnum((unsigned char)line[i]) || line[i] == '_'))
                i++;
            std::string word = line.substr(start, i - start);
            unsigned char kind = HL_NORMAL;
            if (KEYWORDS.count(word)) kind = HL_KEYWORD;
            else if (TYPES.count(word)) kind = HL_TYPE;
            if (kind != HL_NORMAL)
                for (size_t j = start; j < i; j++) hl[j] = kind;
            prevSep = false;
            continue;
        }

        prevSep = isSeparator(c);
        i++;
    }
    return hl;
}

// ---------------------------------------------------------------------------
// Editor
// ---------------------------------------------------------------------------

struct Editor {
    std::vector<std::string> rows{""};
    std::string filename;
    int cx = 0, cy = 0;        // cursor (column, row) in the buffer
    int rowoff = 0, coloff = 0;
    bool dirty = false;
    std::string statusMsg;
    time_t statusTime = 0;
    std::string lastSearch;

    int screenRows() const { return LINES - 2; }  // status + message bars
    int gutterWidth() const {
        int digits = 1, n = (int)rows.size();
        while (n >= 10) { n /= 10; digits++; }
        return digits + 1;
    }
    int textCols() const { return COLS - gutterWidth(); }

    void setStatus(const std::string& msg) {
        statusMsg = msg;
        statusTime = time(nullptr);
    }

    // --- file I/O ---

    bool load(const std::string& path) {
        filename = path;
        rows.clear();
        std::ifstream in(path);
        if (!in) {
            rows.push_back("");
            setStatus("New file: " + path);
            dirty = false;
            return false;
        }
        std::string line;
        while (std::getline(in, line)) {
            if (!line.empty() && line.back() == '\r') line.pop_back();
            // Tabs are stored as spaces to keep cursor math simple.
            std::string expanded;
            for (char c : line) {
                if (c == '\t')
                    expanded.append(TAB_WIDTH - expanded.size() % TAB_WIDTH, ' ');
                else
                    expanded.push_back(c);
            }
            rows.push_back(expanded);
        }
        if (rows.empty()) rows.push_back("");
        dirty = false;
        cx = cy = rowoff = coloff = 0;
        return true;
    }

    bool save() {
        if (filename.empty()) {
            std::string name = prompt("Save as: ");
            if (name.empty()) {
                setStatus("Save aborted");
                return false;
            }
            filename = name;
        }
        std::ofstream out(filename);
        if (!out) {
            setStatus("Cannot write " + filename);
            return false;
        }
        for (const auto& r : rows) out << r << '\n';
        dirty = false;
        setStatus("Saved " + filename + " (" + std::to_string(rows.size()) +
                  " lines)");
        return true;
    }

    // --- editing primitives ---

    void insertChar(int c) {
        rows[cy].insert(rows[cy].begin() + cx, (char)c);
        cx++;
        dirty = true;
    }

    void insertNewline() {
        std::string& row = rows[cy];
        // Keep the indentation of the current line.
        std::string indent = row.substr(0, row.find_first_not_of(' ') ==
                                               std::string::npos
                                           ? row.size()
                                           : row.find_first_not_of(' '));
        if ((size_t)cx < indent.size()) indent.resize(cx);
        std::string rest = row.substr(cx);
        row.resize(cx);
        rows.insert(rows.begin() + cy + 1, indent + rest);
        cy++;
        cx = (int)indent.size();
        dirty = true;
    }

    void deleteChar() {  // backspace
        if (cx == 0 && cy == 0) return;
        if (cx > 0) {
            rows[cy].erase(rows[cy].begin() + cx - 1);
            cx--;
        } else {
            cx = (int)rows[cy - 1].size();
            rows[cy - 1] += rows[cy];
            rows.erase(rows.begin() + cy);
            cy--;
        }
        dirty = true;
    }

    void deleteForward() {  // DEL key
        if ((size_t)cx < rows[cy].size()) {
            rows[cy].erase(rows[cy].begin() + cx);
            dirty = true;
        } else if (cy + 1 < (int)rows.size()) {
            rows[cy] += rows[cy + 1];
            rows.erase(rows.begin() + cy + 1);
            dirty = true;
        }
    }

    void deleteLine() {
        if (rows.size() == 1) {
            if (rows[0].empty()) return;
            rows[0].clear();
        } else {
            rows.erase(rows.begin() + cy);
            if (cy >= (int)rows.size()) cy = (int)rows.size() - 1;
        }
        cx = std::min(cx, (int)rows[cy].size());
        dirty = true;
    }

    void moveCursor(int key) {
        switch (key) {
            case KEY_LEFT:
                if (cx > 0) cx--;
                else if (cy > 0) { cy--; cx = (int)rows[cy].size(); }
                break;
            case KEY_RIGHT:
                if ((size_t)cx < rows[cy].size()) cx++;
                else if (cy + 1 < (int)rows.size()) { cy++; cx = 0; }
                break;
            case KEY_UP:
                if (cy > 0) cy--;
                break;
            case KEY_DOWN:
                if (cy + 1 < (int)rows.size()) cy++;
                break;
            case KEY_HOME: cx = 0; break;
            case KEY_END: cx = (int)rows[cy].size(); break;
        }
        cx = std::min(cx, (int)rows[cy].size());
    }

    // --- search ---

    void find() {
        std::string q = prompt("Find: ", lastSearch);
        if (q.empty()) return;
        lastSearch = q;
        int n = (int)rows.size();
        // Start just after the cursor, wrap around the whole buffer.
        for (int step = 0; step <= n; step++) {
            int row = (cy + step) % n;
            size_t from = (step == 0) ? (size_t)cx + 1 : 0;
            if (from > rows[row].size()) continue;
            size_t pos = rows[row].find(q, from);
            if (pos != std::string::npos) {
                cy = row;
                cx = (int)pos;
                setStatus("Found \"" + q + "\" (Ctrl+F again for next)");
                return;
            }
        }
        setStatus("Not found: " + q);
    }

    // --- drawing ---

    void scrollView() {
        if (cy < rowoff) rowoff = cy;
        if (cy >= rowoff + screenRows()) rowoff = cy - screenRows() + 1;
        if (cx < coloff) coloff = cx;
        if (cx >= coloff + textCols()) coloff = cx - textCols() + 1;
    }

    void draw() {
        scrollView();
        erase();
        int gutter = gutterWidth();

        // Carry the block-comment state into the first visible row.
        bool inComment = false;
        for (int i = 0; i < rowoff && i < (int)rows.size(); i++)
            highlightLine(rows[i], inComment);

        for (int y = 0; y < screenRows(); y++) {
            int fileRow = rowoff + y;
            if (fileRow >= (int)rows.size()) {
                mvaddch(y, 0, '~');
                continue;
            }
            const std::string& row = rows[fileRow];
            auto hl = highlightLine(row, inComment);

            attron(COLOR_PAIR(8));
            mvprintw(y, 0, "%*d ", gutter - 1, fileRow + 1);
            attroff(COLOR_PAIR(8));

            for (int x = 0; x < textCols(); x++) {
                int fileCol = coloff + x;
                if (fileCol >= (int)row.size()) break;
                int pair = hl[fileCol];
                if (pair) attron(COLOR_PAIR(pair));
                mvaddch(y, gutter + x, row[fileCol]);
                if (pair) attroff(COLOR_PAIR(pair));
            }
        }

        // Status bar.
        attron(A_REVERSE);
        std::string left = " " + (filename.empty() ? "[No Name]" : filename) +
                           (dirty ? " [+]" : "") + "  " +
                           std::to_string(rows.size()) + " lines";
        std::string right = "Ln " + std::to_string(cy + 1) + ", Col " +
                            std::to_string(cx + 1) + " ";
        if ((int)left.size() > COLS) left.resize(COLS);
        mvprintw(screenRows(), 0, "%-*s", COLS, left.c_str());
        if ((int)(left.size() + right.size()) < COLS)
            mvprintw(screenRows(), COLS - (int)right.size(), "%s",
                     right.c_str());
        attroff(A_REVERSE);

        // Message bar.
        std::string msg = statusMsg;
        if (time(nullptr) - statusTime > 6)
            msg = "Ctrl+S save | Ctrl+B build | Ctrl+R run | Ctrl+G help | "
                  "Ctrl+Q quit";
        if ((int)msg.size() > COLS) msg.resize(COLS);
        mvprintw(screenRows() + 1, 0, "%s", msg.c_str());

        move(cy - rowoff, cx - coloff + gutter);
        refresh();
    }

    // Reads a line of input in the message bar. ESC cancels.
    std::string prompt(const std::string& label, std::string initial = "") {
        std::string input = initial;
        while (true) {
            std::string line = label + input;
            if ((int)line.size() > COLS - 1) line.resize(COLS - 1);
            move(screenRows() + 1, 0);
            clrtoeol();
            mvprintw(screenRows() + 1, 0, "%s", line.c_str());
            refresh();
            int c = getch();
            if (c == '\r' || c == '\n' || c == KEY_ENTER) return input;
            if (c == 27) return "";
            if (c == KEY_BACKSPACE || c == 127 || c == CTRL('h')) {
                if (!input.empty()) input.pop_back();
            } else if (c >= 32 && c < 127) {
                input.push_back((char)c);
            }
        }
    }
};

// ---------------------------------------------------------------------------
// Scrollable text pane (build output, help)
// ---------------------------------------------------------------------------

static void showPane(const std::string& title,
                     const std::vector<std::string>& lines) {
    int off = 0;
    while (true) {
        erase();
        attron(A_REVERSE);
        mvprintw(0, 0, "%-*s", COLS, (" " + title).c_str());
        attroff(A_REVERSE);
        int visible = LINES - 2;
        for (int y = 0; y < visible; y++) {
            int i = off + y;
            if (i >= (int)lines.size()) break;
            std::string line = lines[i];
            if ((int)line.size() > COLS) line.resize(COLS);
            bool isError = line.find("error:") != std::string::npos;
            bool isWarn = line.find("warning:") != std::string::npos;
            if (isError) attron(COLOR_PAIR(10) | A_BOLD);
            else if (isWarn) attron(COLOR_PAIR(11));
            mvprintw(y + 1, 0, "%s", line.c_str());
            if (isError) attroff(COLOR_PAIR(10) | A_BOLD);
            else if (isWarn) attroff(COLOR_PAIR(11));
        }
        attron(A_REVERSE);
        mvprintw(LINES - 1, 0, "%-*s", COLS,
                 " Up/Down/PgUp/PgDn scroll | Enter/q close");
        attroff(A_REVERSE);
        refresh();

        int maxOff = std::max(0, (int)lines.size() - visible);
        int c = getch();
        switch (c) {
            case KEY_UP: off = std::max(0, off - 1); break;
            case KEY_DOWN: off = std::min(maxOff, off + 1); break;
            case KEY_PPAGE: off = std::max(0, off - visible); break;
            case KEY_NPAGE: off = std::min(maxOff, off + visible); break;
            case 'q': case 27: case '\r': case '\n': case KEY_ENTER: return;
        }
    }
}

// ---------------------------------------------------------------------------
// Build & run
// ---------------------------------------------------------------------------

static std::string shellQuote(const std::string& s) {
    std::string out = "'";
    for (char c : s)
        out += (c == '\'') ? std::string("'\\''") : std::string(1, c);
    return out + "'";
}

static std::string detectCompiler() {
    const char* env = getenv("CXX");
    if (env && *env) return env;
    if (system("command -v clang++ >/dev/null 2>&1") == 0) return "clang++";
    return "g++";
}

static std::string binaryPath(const std::string& src) {
    size_t dot = src.find_last_of('.');
    size_t slash = src.find_last_of('/');
    if (dot == std::string::npos ||
        (slash != std::string::npos && dot < slash))
        return src + ".out";
    return src.substr(0, dot);
}

// Compiles the current file. Returns true on success; on failure shows the
// build output and jumps the cursor to the first reported error.
static bool build(Editor& ed) {
    if (ed.filename.empty() || ed.dirty) {
        if (!ed.save()) return false;
    }
    std::string cxx = detectCompiler();
    std::string bin = binaryPath(ed.filename);
    std::string cmd = cxx + " -std=c++17 -Wall -Wextra -g " +
                      shellQuote(ed.filename) + " -o " + shellQuote(bin) +
                      " 2>&1";

    ed.setStatus("Compiling with " + cxx + "...");
    ed.draw();

    std::vector<std::string> output;
    FILE* pipe = popen(cmd.c_str(), "r");
    if (!pipe) {
        ed.setStatus("Failed to run " + cxx);
        return false;
    }
    char buf[1024];
    std::string line;
    while (fgets(buf, sizeof buf, pipe)) {
        line += buf;
        if (!line.empty() && line.back() == '\n') {
            line.pop_back();
            output.push_back(line);
            line.clear();
        }
    }
    if (!line.empty()) output.push_back(line);
    int status = pclose(pipe);
    bool ok = WIFEXITED(status) && WEXITSTATUS(status) == 0;

    if (ok) {
        ed.setStatus("Build OK -> " + bin +
                     (output.empty() ? "" : " (with warnings)"));
        if (!output.empty()) showPane("Build output (success, with warnings)",
                                      output);
        return true;
    }

    // Jump to the first "file:line:col:" error location.
    for (const auto& l : output) {
        size_t p = l.find(ed.filename + ":");
        if (p != 0) continue;
        int lineNo = 0, colNo = 0;
        if (sscanf(l.c_str() + ed.filename.size(), ":%d:%d", &lineNo,
                   &colNo) >= 1 &&
            lineNo >= 1 && lineNo <= (int)ed.rows.size()) {
            ed.cy = lineNo - 1;
            ed.cx = std::max(0, std::min(colNo - 1, (int)ed.rows[ed.cy].size()));
        }
        break;
    }
    showPane("Build FAILED - " + cxx, output);
    ed.setStatus("Build failed (cursor moved to first error)");
    return false;
}

static void run(Editor& ed) {
    if (!build(ed)) return;
    std::string bin = binaryPath(ed.filename);
    if (bin.find('/') == std::string::npos) bin = "./" + bin;

    // Leave curses, run the program on the real terminal, then come back.
    def_prog_mode();
    endwin();
    std::string cmd = "clear; " + shellQuote(bin) +
                      "; st=$?; printf '\\n--- program exited with code %d "
                      "--- press Enter ---\\n' \"$st\"; read _ignored";
    if (system(cmd.c_str()) != 0) { /* program's exit code already shown */ }
    reset_prog_mode();
    refresh();
    ed.setStatus("Program finished");
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

static const std::vector<std::string> HELP_TEXT = {
    "tide - tiny terminal IDE for C++",
    "",
    "  Ctrl+S   save file          Ctrl+O   open another file",
    "  Ctrl+N   new empty file     Ctrl+F   find (repeat for next match)",
    "  Ctrl+B   build current file (clang++/g++, -std=c++17 -Wall -Wextra)",
    "  Ctrl+R   build and run in the terminal",
    "  Ctrl+K   delete current line",
    "  Ctrl+Q   quit (press twice if there are unsaved changes)",
    "",
    "  Arrows / Home / End / PgUp / PgDn move around.",
    "  Tab inserts spaces; Enter keeps the current indentation.",
    "",
    "  Set the CXX environment variable to choose a compiler, e.g.:",
    "      CXX=g++ ./tide main.cpp",
    "",
    "  On build errors the cursor jumps to the first error location.",
};

int main(int argc, char** argv) {
    initscr();
    raw();  // raw (not cbreak) so Ctrl+S / Ctrl+Q reach us
    noecho();
    keypad(stdscr, TRUE);
    set_escdelay(25);

    if (has_colors()) {
        start_color();
        use_default_colors();
        init_pair(HL_KEYWORD, COLOR_YELLOW, -1);
        init_pair(HL_TYPE, COLOR_GREEN, -1);
        init_pair(HL_NUMBER, COLOR_MAGENTA, -1);
        init_pair(HL_STRING, COLOR_CYAN, -1);
        init_pair(HL_COMMENT, COLOR_BLUE, -1);
        init_pair(HL_PREPROC, COLOR_RED, -1);
        init_pair(8, COLOR_BLUE, -1);     // line numbers
        init_pair(10, COLOR_RED, -1);     // pane: errors
        init_pair(11, COLOR_YELLOW, -1);  // pane: warnings
    }

    Editor ed;
    if (argc >= 2) ed.load(argv[1]);
    ed.setStatus("Welcome to tide | Ctrl+G for help");

    int quitConfirm = 0;
    while (true) {
        ed.draw();
        int c = getch();

        if (c != CTRL('q')) quitConfirm = 0;

        switch (c) {
            case CTRL('q'):
                if (ed.dirty && quitConfirm == 0) {
                    quitConfirm = 1;
                    ed.setStatus(
                        "Unsaved changes! Press Ctrl+Q again to discard, "
                        "or Ctrl+S to save.");
                    break;
                }
                endwin();
                return 0;

            case CTRL('s'): ed.save(); break;

            case CTRL('o'): {
                std::string name = ed.prompt("Open file: ");
                if (!name.empty()) {
                    if (ed.dirty) ed.save();
                    ed.load(name);
                }
                break;
            }

            case CTRL('n'):
                if (ed.dirty) ed.save();
                ed = Editor{};
                ed.setStatus("New buffer");
                break;

            case CTRL('b'): build(ed); break;
            case CTRL('r'): run(ed); break;
            case CTRL('f'): ed.find(); break;
            case CTRL('k'): ed.deleteLine(); break;
            case CTRL('g'): showPane("Help", HELP_TEXT); break;

            case '\r': case '\n': case KEY_ENTER:
                ed.insertNewline();
                break;

            case KEY_BACKSPACE: case 127: case CTRL('h'):
                ed.deleteChar();
                break;

            case KEY_DC: ed.deleteForward(); break;

            case '\t':
                for (int i = 0; i < TAB_WIDTH; i++) ed.insertChar(' ');
                break;

            case KEY_PPAGE:
                ed.cy = std::max(0, ed.cy - ed.screenRows());
                ed.cx = std::min(ed.cx, (int)ed.rows[ed.cy].size());
                break;
            case KEY_NPAGE:
                ed.cy = std::min((int)ed.rows.size() - 1,
                                 ed.cy + ed.screenRows());
                ed.cx = std::min(ed.cx, (int)ed.rows[ed.cy].size());
                break;

            case KEY_UP: case KEY_DOWN: case KEY_LEFT: case KEY_RIGHT:
            case KEY_HOME: case KEY_END:
                ed.moveCursor(c);
                break;

            case KEY_RESIZE: break;  // next draw() adapts

            default:
                if (c >= 32 && c < 127) ed.insertChar(c);
                break;
        }
    }
}
