<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
require_once __DIR__ . '/../example/boot.php';

// replace with mechanism to retrieve EntityManager in your app
$entityManager = getConnection();

return ConsoleRunner::createHelperSet($entityManager);
