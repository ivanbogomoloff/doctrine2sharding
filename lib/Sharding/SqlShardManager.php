<?php

namespace Doctrine\Sharding;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Sharding\PoolingShardConnection;
use Doctrine\DBAL\Sharding\ShardChoser\ShardChoser;
use Doctrine\DBAL\Sharding\ShardingException;
use Doctrine\DBAL\Sharding\ShardManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;

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
	 * @var Connection|PoolingShardConnection
	 */
	protected $globalConnection;
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
		'shards' => []
	];

	/**
	 * @var int
	 */
	protected $distributionValue;

	/**
	 * @param \Doctrine\DBAL\Driver\Connection $conn
	 * @param \Doctrine\DBAL\Driver\Connection $globalConn
	 * @param array                            $emConf
	 *
	 * @throws \Doctrine\DBAL\Sharding\ShardingException
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function __construct(Connection $conn, Connection $globalConn, array $emConf = [])
	{
		$this->connection 		= $conn;
		$this->globalConnection = $globalConn;
		$this->emConfig   		= $emConf;
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

		$this->entityManager['global'] = EntityManager::create($this->globalConnection, $emConf['global']);
	}

	/**
	 * @return ShardChoser
	 */
	public function getShardChoser()
	{
		return $this->connection->getParams()['shardChoser'];
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
		$this->distributionValue = 0;
		$this->globalConnection->connect(0);
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
		$this->distributionValue = $this->getShardChoser()->pickShard($distributionValue, $this->connection);
		$this->connect();
	}

	/**
	 * @throws \Doctrine\DBAL\Sharding\ShardingException
	 */
	private function connect()
	{
		$this->connection->connect($this->distributionValue);
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
	 * @param int $shardId - specify connection
	 * @return EntityManager
	 */
	public function getEntityManager($shardId = null)
	{
		if(is_int($shardId))
		{
			$this->distributionValue = $shardId;
			$this->connection->connect($shardId);
		}

		if($this->distributionValue === 0)
		{
			return $this->entityManager['global'];
		}

		if(!isset($this->entityManager['shards'][$shardId]))
		{
			$this->entityManager['shards'][$shardId] = EntityManager::create(
				$this->connection,
				$this->emConfig['shards']
			);
		}

		return $this->entityManager['shards'][$shardId];
	}

	/**
	 * @void
	 */
	public function closeConnections()
	{
		$this->connection->close();
	}
}