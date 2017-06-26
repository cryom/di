<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require __DIR__ . '/module/api/ApiInterface.php';
require __DIR__ . '/module/api/service/Api.php';
require __DIR__ . '/module/api/service/Cache.php';
require __DIR__ . '/module/api/Main.php';
require __DIR__ . '/service/A.php';
require __DIR__ . '/service/B.php';
require __DIR__ . '/Main.php';

$app = new \vivace\di\example\base\Main();

echo $app->boot();
