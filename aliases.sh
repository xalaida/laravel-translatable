alias tf='docker run --rm -it -v ${PWD}:/app app vendor/bin/phpunit --filter'
alias tfc='docker run --rm -it -v ${PWD}:/app app vendor/bin/phpunit --coverage-html tests/report --filter'
