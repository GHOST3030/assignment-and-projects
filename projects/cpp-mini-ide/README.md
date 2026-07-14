# tide — tiny terminal IDE for C++ (Termux & Linux)

`tide` is a small IDE that runs entirely in the terminal. You write C++ in it,
press **Ctrl+B** to compile and **Ctrl+R** to run — without ever leaving the
editor. It is a single C++ source file (~600 lines) built on ncurses, so it
compiles in a couple of seconds even on a phone.

## What you get

- Text editor with **C++ syntax highlighting** (keywords, types, strings,
  numbers, comments, preprocessor lines)
- **One-key compile** (Ctrl+B) using `clang++` or `g++` with
  `-std=c++17 -Wall -Wextra -g`
- **One-key run** (Ctrl+R): builds, clears the screen, runs your program on
  the real terminal, shows its exit code, then returns to the editor
- **Error panel**: compiler errors/warnings shown in a scrollable, colored
  pane, and the cursor jumps to the first error's line and column
- Find (Ctrl+F), line numbers, auto-indent on Enter, delete-line (Ctrl+K),
  unsaved-changes protection on quit

### About the "compiler" part

`tide` uses **clang++ / g++ as its compiler backend** — the same approach
every real IDE (VS Code, CLion, Code::Blocks) takes, because a from-scratch
compiler covering the whole C++ language is a multi-year project. On Termux
the compiler is Clang (`pkg install clang`), which is a full, real C++
toolchain running natively on your phone.

## Install & build

### Termux (Android)

```sh
pkg update
pkg install clang make ncurses ncurses-utils
cd projects/cpp-mini-ide
make CXX=clang++ LDLIBS=-lncursesw
./tide examples/hello.cpp
```

Optional — put it on your PATH so you can type `tide` anywhere:

```sh
make install CXX=clang++ LDLIBS=-lncursesw
```

Tip: in Termux, swipe the extra-keys row to get **CTRL**; all tide shortcuts
are Ctrl-based on purpose, since phones have no function keys.

### Debian / Ubuntu Linux

```sh
sudo apt install g++ make libncurses-dev
cd projects/cpp-mini-ide
make
./tide examples/hello.cpp
```

(Fedora: `sudo dnf install gcc-c++ make ncurses-devel`,
Arch: `sudo pacman -S gcc make ncurses`.)

## Usage

```sh
./tide myprogram.cpp      # open (or create) a file
./tide                    # start with an empty buffer
```

| Key | Action |
|-----|--------|
| `Ctrl+S` | Save |
| `Ctrl+O` | Open another file |
| `Ctrl+N` | New empty buffer |
| `Ctrl+B` | Build (saves first, shows errors in a pane) |
| `Ctrl+R` | Build **and run** in the terminal |
| `Ctrl+F` | Find (press again to jump to the next match) |
| `Ctrl+K` | Delete current line |
| `Ctrl+G` | Help screen |
| `Ctrl+Q` | Quit (twice to discard unsaved changes) |

Arrows, Home/End, PgUp/PgDn move around. Tab inserts spaces and Enter keeps
the current line's indentation.

The compiled binary is written next to your source file (`hello.cpp` →
`hello`). To use a specific compiler, set `CXX`:

```sh
CXX=g++ ./tide main.cpp
```

## Code tour (if you want to hack on it)

Everything lives in `src/tide.cpp`:

- `highlightLine()` — hand-written lexer that classifies each character
  (keyword / type / string / number / comment / preprocessor) and carries
  `/* ... */` state across lines
- `struct Editor` — text buffer (`std::vector<std::string>`), cursor,
  scrolling, editing primitives, drawing, and the input prompt
- `build()` — runs the compiler via `popen`, captures the output, parses
  `file:line:col:` from the first error and moves the cursor there
- `run()` — suspends ncurses (`def_prog_mode`/`endwin`), runs your program
  on the real terminal, then restores the editor
- `showPane()` — scrollable viewer used for build output and help

Good first hacks: more keywords, a "recent files" list, Ctrl+Z undo (keep a
stack of `rows` snapshots), or passing command-line arguments to Ctrl+R.
