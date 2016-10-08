<?php
set_time_limit(0);

require __DIR__ . '/vendor/autoload.php';

$reader = new Loader\Reader(new Loader\Config\Reader('./values.php'));
$reader->run($_GET);