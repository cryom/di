<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require __DIR__ . '/service/A.php';
require __DIR__ . '/service/B.php';
require __DIR__ . '/Main.php';

$app = new \vivace\di\example\base\Main();

echo $app->getB()->getName(), "\n";
echo $app->getB()->getA()->do(), "\n";
