<?php

require_once __DIR__ . '/../vendor/autoload.php';

use \Doctrine\ORM\Tools\Setup;
use \Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;

/**
 * @return \Doctrine\Sharding\SqlShardManager
 */
function getShardManager()
{
	$conn    = getConnection();
	$manager = new \Doctrine\Sharding\SqlShardManager($conn, [
			'global' => Setup::createAnnotationMetadataConfiguration([__DIR__ .'/Entity'], true),
			'shards' => Setup::createAnnotationMetadataConfiguration([__DIR__ .'/Entity'], true)
	]);

	return $manager;
}

function getConnection()
{
	$conn = DriverManager::getConnection([
		 'wrapperClass' => 'Doctrine\DBAL\Sharding\PoolingShardConnection',
		 'driver'       => 'pdo_mysql',
		 'global'       => [
			 'driver'   => 'pdo_mysql',
			 'username' => 'root',
			 'password' => 'root',
			 'dbname' 	=> 'db1',
			 'host'     => '192.168.1.103'
		 ],
		 'shards'       => [
			[
				 'id'		=> 1,
				 'driver'   => 'pdo_mysql',
				 'username' => 'root',
				 'password' => 'root',
				 'dbname' 	=> 'db2',
				 'host'     => '192.168.1.103'
			 ],
			[
				'id'		=> 2,
				'driver'   	=> 'pdo_mysql',
				'username' 	=> 'root',
				'password' 	=> 'root',
				'dbname' 	=> 'db3',
				'host'     	=> '192.168.1.103'
			],
			[
				'id'		=> 3,
				'driver'   	=> 'pdo_mysql',
				'username' 	=> 'root',
				'password' 	=> 'root',
				'dbname' 	=> 'db4',
				'host'     	=> '192.168.1.103'
			]
		 ],
		 'shardChoser' => 'Doctrine\DBAL\Sharding\ShardChoser\MultiTenantShardChoser',
	 ]);

	return $conn;
}

/**
 * @param null $shardId
 *
 * @return \Doctrine\ORM\EntityManager
 * @throws \Doctrine\ORM\ORMException
 */
function getEntityManager($shardId = null)
{
	$conn 		= getConnection();

	if($shardId)
	{
		$conn->connect($shardId);
	}

	$paths 		= [__DIR__ . '/Entity'];
	$isDevMode 	= false;
	$config 		= Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);

	return EntityManager::create($conn, $config);
}