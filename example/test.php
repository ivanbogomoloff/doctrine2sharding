<?php

require_once __DIR__ . '/boot.php';

$manager = getShardManager();
$manager->selectGlobal();
$manager->selectShard(1);

