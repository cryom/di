<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require __DIR__ . '/service/ViewInterface.php';
require __DIR__ . '/module/admin/service/View.php';
require __DIR__ . '/module/blog/service/View.php';
require __DIR__ . '/module/admin/Main.php';
require __DIR__ . '/module/blog/Main.php';
require __DIR__ . '/Main.php';

$app = new \vivace\di\example\extending\Main();

echo $app->boot();