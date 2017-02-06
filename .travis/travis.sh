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
        vendor/bin/codecept run unit --coverage-xml
        xdebug-disable
    else
       vendor/bin/codecept run unit
    fi
}
