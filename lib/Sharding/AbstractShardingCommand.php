<?php

namespace Doctrine\Sharding;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\AbstractCommand;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractShardingCommand
 *
 * @package Doctrine\Sharding
 */
abstract class AbstractShardingCommand extends AbstractCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/**
		 * @var ShardManagerHelper $sharManagerHelper
		 */
		$shardManagerHelper = $this->getHelper('sm');

		/**
		 * @var SqlShardManager $sm
		 */
		$sm 	= $shardManagerHelper->getShardManager();
		$shards = $sm->getShards();

		if(empty($shards))
		{
			$output->writeln('No shards to process.');
			exit(0);
		}

		foreach($shards as $shard)
		{
			$shardId   = $shard['id'];
			$em 	   = $sm->getEntityManager($shardId);
			$metadatas = $em->getMetadataFactory()->getAllMetadata();

			if ( ! empty($metadatas)) {
				// Create SchemaTool
				$tool = new SchemaTool($em);
				$this->executeSchemaCommand($input, $output, $tool, $metadatas);
				$output->writeln("<info>Success process shard {$shardId}...</info>");
			} else {
				$output->writeln('No Metadata Classes to process.');
				$output->writeln('Go to next shard.');
			}

			//$sm->closeConnections();
		}

	}
}