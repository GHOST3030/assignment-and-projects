// Try me: open with  ./tide examples/hello.cpp  then press Ctrl+R
#include <iostream>
#include <string>
#include <vector>

int main() {
    std::vector<std::string> greetings = {"Hello", "Marhaba", "Salam"};

    for (const auto& g : greetings) {
        std::cout << g << ", world!\n";
    }

    int sum = 0;
    for (int i = 1; i <= 10; i++) sum += i;
    std::cout << "1 + 2 + ... + 10 = " << sum << '\n';

    return 0;
}
