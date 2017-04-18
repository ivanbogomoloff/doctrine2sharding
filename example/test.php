<?php

require_once __DIR__ . '/boot.php';


$manager = getShardManager();
/**
 * Get Global connection
 */
$manager->selectGlobal();
$globalEm = $manager->getEntityManager();

/**
 * Get Shard database for user id = 1
 */
$manager->selectShard(1);
$shardEm = $manager->getEntityManager();

$globalEm->beginTransaction();
$shardEm->beginTransaction();
try
{
	$user = new \Entity\User();
	$user->setUserName('shard user');

	$shardEm->persist($user);
	$shardEm->flush();
	$userLog = new \Entity\Log();
	$userLog->setLogId('logged');

	$globalEm->persist($userLog);
	$globalEm->flush();

	$shardEm->commit();
	$globalEm->commit();
}
catch (\Exception $e)
{
	$shardEm->rollback();
	$globalEm->rollback();
	throw $e;
}



//$em->persist($user);
//$em->flush();

