<?php
namespace vivace\di {

    const VERSION = '0.0.1';


    function wrap($value): callable
    {
        if (is_callable($value)) {
            return $value;
        }

        return function () use ($value) {
            return $value;
        };
    }
}
