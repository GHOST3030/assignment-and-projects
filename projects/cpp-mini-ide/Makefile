# tide - tiny terminal IDE for C++
#
# Linux:  make            (needs g++/clang++ and libncurses-dev)
# Termux: make            (pkg install clang make ncurses)

CXX      ?= g++
CXXFLAGS ?= -std=c++17 -O2 -Wall -Wextra
LDLIBS   ?= -lncurses
# Termux already exports PREFIX; on regular Linux default to /usr/local.
PREFIX   ?= /usr/local

tide: src/tide.cpp
	$(CXX) $(CXXFLAGS) -o $@ $< $(LDLIBS)

install: tide
	install -m 755 tide $(PREFIX)/bin/tide

clean:
	rm -f tide

.PHONY: install clean
