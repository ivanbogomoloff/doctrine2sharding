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
	$conn    	= getConnection();
	$globalConn = getConnection();

	$manager = new \Doctrine\Sharding\SqlShardManager($conn, $globalConn, [
			'global' => Setup::createAnnotationMetadataConfiguration([__DIR__ .'/Entity'], true),
			'shards' => Setup::createAnnotationMetadataConfiguration([__DIR__ .'/Entity'], true)
	]);

	return $manager;
}

/**
 * @return \Doctrine\DBAL\Connection
 * @throws \Doctrine\DBAL\DBALException
 */
function getConnection()
{
	$host = '192.168.1.104';
	$conn = DriverManager::getConnection([
		 'wrapperClass' => 'Doctrine\DBAL\Sharding\PoolingShardConnection',
		 'driver'       => 'pdo_mysql',
		 'global'       => [
			 'driver'   => 'pdo_mysql',
			 'username' => 'root',
			 'password' => 'root',
			 'dbname' 	=> 'db1',
			 'host'     => $host
		 ],
		 'shards'       => [
			[
				 'id'		=> 1,
				 'driver'   => 'pdo_mysql',
				 'username' => 'root',
				 'password' => 'root',
				 'dbname' 	=> 'db2',
				 'host'     => $host
			 ],
			[
				'id'		=> 2,
				'driver'   	=> 'pdo_mysql',
				'username' 	=> 'root',
				'password' 	=> 'root',
				'dbname' 	=> 'db3',
				'host'     	=> $host
			],
			[
				'id'		=> 3,
				'driver'   	=> 'pdo_mysql',
				'username' 	=> 'root',
				'password' 	=> 'root',
				'dbname' 	=> 'db4',
				'host'     	=> $host
			]
		 ],
		 'shardChoser' => 'Doctrine\Sharding\SqlShardChoser',
	 ]);

	$conn->getConfiguration()->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
	return $conn;
}