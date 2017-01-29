<?php

namespace Doctrine\Sharding;

use Doctrine\DBAL\Sharding\PoolingShardConnection;
use Doctrine\DBAL\Sharding\ShardChoser\ShardChoser;

/**
 * Class SqlShardChoser
 *
 * @package Doctrine\Sharding
 */
final class SqlShardChoser implements ShardChoser
{
	/**
	 * @var int
	 */
	const MODULAR_VALUE = 256;

	/**
	 * @param string                                         $distributionValue
	 * @param \Doctrine\DBAL\Sharding\PoolingShardConnection $conn
	 *
	 * @return int|void
	 * @throws \Doctrine\Sharding\ShardChoserException
	 */
	function pickShard($distributionValue, PoolingShardConnection $conn)
	{
		$shards 		= $conn->getParams()['shards'];
		$shardKey 		= $distributionValue % self::MODULAR_VALUE;
		$shardStep  	= self::MODULAR_VALUE / count($shards);
		$currentRange 	= [0, $shardStep];

		foreach($shards as $shard)
		{
			if($shardKey >= $currentRange[0] && $shardKey < $currentRange[1])
			{
				return $shard['id'];
			}

			$currentRange = [$currentRange[0]+$shardStep, $currentRange[1]+$shardStep];
		}

		throw new ShardChoserException("Cannon pick shard for {$distributionValue} value");
	}

}