#!/bin/sh

die ()
{
    echo $1 1>2
    exit $2
}

MY_PATH="$(dirname "$0")"

if [ $# -gt 2 ]; then
    echo -e "Usage: $(basename "$0") php phpunit\n"
    exit 1;
fi

if [ $# -eq 2 ]; then
    PHPUNIT="$(which "$2")"; test $? || die "Supplied phpunit not found." 4
else
    PHPUNIT="$(which phpunit)"; test $? || die "Failed to find phpunit." 5
fi

if [ $# -gt 0 ]; then
    PHP="$(which "$1")"; test $? || die "Supplied php binary not found." 2
else
    PHP="$(which php)"; test $? || die "Failed to find php binary." 3
fi

"$PHP" --define error_reporting='E_ALL|ESTRICT' "$PHPUNIT" --configuration "$MY_PATH/tests/phpunit.xml" --coverage-text
