<?php

require_once __DIR__ . '/../example/boot.php';

use Symfony\Component\Console\Helper\HelperSet as HelperSet;
use Doctrine\ORM\Tools\Console\ConsoleRunner as ConsoleRunner;

$manager   = getShardManager();
$em 	   = $manager->getEntityManager();

$helperSet = ConsoleRunner::createHelperSet($em);
$helperSet->set(new \Doctrine\Sharding\ShardManagerHelper($manager), 'sm');

$commands  = [
	new \Doctrine\Sharding\SqlShardCreateCommand(),
	new \Doctrine\Sharding\SqlShardUpdateCommand(),
];

ConsoleRunner::run($helperSet, $commands);
