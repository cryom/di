#!/usr/bin/env bash

config="/home/travis/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini"

function xdebug-disable() {
    if [[ -f $config ]]; then
        mv $config "$config.bak"
    fi
}

function xdebug-enable() {
    if [[ -f "$config.bak" ]]; then
        mv "$config.bak" $config
    fi
}

function run-tests() {
    if [[ "$WITH_COVERAGE" == "true" ]]; then
        xdebug-enable
        vendor/bin/phpunit --testsuite=unit --coverage-text --coverage-clover build/logs/clover.xml
        CODECLIMATE_REPO_TOKEN="f759558562762398c32879d47ef81bf3ac597cb09e36b1b57685cf2b68264479" vendor/bin/test-reporter --stdout > codeclimate.json
        curl -X POST -d @codeclimate.json -H "Content-Type: application/json" -H "User-Agent: Code Climate (PHP Test Reporter v0.1.1)" https://codeclimate.com/test_reports;
        xdebug-disable
    else
       vendor/bin/phpunit --testsuite=unit
    fi
}
