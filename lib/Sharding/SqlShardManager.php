<?php

namespace Doctrine\Sharding;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Sharding\PoolingShardConnection;
use Doctrine\DBAL\Sharding\ShardingException;
use Doctrine\DBAL\Sharding\ShardManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

/**
 * Class ShardEntityManager
 *
 * @package Doctrine\Sharding
 */
final class SqlShardManager implements ShardManager
{
	/**
	 * @var PoolingShardConnection|Connection
	 */
	protected $connection;
	/**
	 * @var array
	 */
	protected $emConfig = [
		'global' => null,
		'shards' => null
	];

	/**
	 * Collection of EntityManager instances
	 * @var array
	 */
	protected $entityManager = [
		'global' => null,
		'shards' => null
	];

	/**
	 * @param \Doctrine\DBAL\Driver\Connection $conn
	 * @param array                            $emConf
	 *
	 * @throws \Doctrine\DBAL\Sharding\ShardingException
	 */
	public function __construct(Connection $conn, array $emConf = [])
	{
		$this->connection = $conn;
		$this->emConfig   = $emConf;

		if(!isset($emConf['global'])) {
			throw new ShardingException("Entity manager configuration must be setup for global db");
		}

		if(!isset($emConf['shards'])) {
			throw new ShardingException("Entity manager configuration must be setup for shards");
		}

		if(!$emConf['global'] instanceof Configuration || !$emConf['shards'] instanceof Configuration)
		{
			throw new ShardingException("Entity manager configuration invalid instance");
		}

		$this->entityManager['global'] = EntityManager::create($this->connection, $emConf['global']);
		$this->entityManager['shards'] = EntityManager::create($this->connection, $emConf['shards']);
	}

	/**
	 * Selects global database with global data.
	 *
	 * This is the default database that is connected when no shard is
	 * selected.
	 *
	 * @return void
	 */
	function selectGlobal()
	{
		$this->connection->connect(0);
	}

	/**
	 * Selects the shard against which the queries after this statement will be issued.
	 *
	 * @param string $distributionValue
	 *
	 * @return void
	 *
	 * @throws \Doctrine\DBAL\Sharding\ShardingException If no value is passed as shard identifier.
	 */
	function selectShard($distributionValue)
	{
		$this->connection->connect($distributionValue);
	}

	/**
	 * Gets the distribution value currently used for sharding.
	 *
	 * @return string
	 */
	function getCurrentDistributionValue()
	{
		return $this->connection->getActiveShardId();
	}

	/**
	 * Gets information about the amount of shards and other details.
	 *
	 * Format is implementation specific, each shard is one element and has an
	 * 'id' attribute at least.
	 *
	 * @return array
	 */
	function getShards()
	{
		return $this->connection->getParams()['shards'];
	}

	/**
	 * Queries all shards in undefined order and return the results appended to
	 * each other. Restore the previous distribution value after execution.
	 *
	 * Using {@link \Doctrine\DBAL\Connection::fetchAll} to retrieve rows internally.
	 *
	 * @param string $sql
	 * @param array  $params
	 * @param array  $types
	 *
	 * @return array
	 */
	function queryAll($sql, array $params, array $types)
	{
		$result = [];
		foreach($this->getShards() as $shardParams)
		{
			$shardId = $shardParams['id'];
			$this->connection->connect($shardId);
			$result[$shardId] = $this->connection->query($sql)->fetchAll();
		}

		$this->selectGlobal();

		return $result;
	}

	/**
	 * @return EntityManager
	 */
	public function getShardEntityManager()
	{
		return $this->entityManager['shards'];
	}

	/**
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager['global'];
	}
}