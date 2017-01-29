<?php

require_once __DIR__ . '/boot.php';


$manager = getShardManager();

$user = new \Entity\User();
$user->setUserName('shard user');

$manager->selectShard(1);
$em = $manager->getEntityManager();

//$em->persist($user);
//$em->flush();

