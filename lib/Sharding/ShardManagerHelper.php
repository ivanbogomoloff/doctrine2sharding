<?php

namespace Doctrine\Sharding;

use Doctrine\DBAL\Sharding\ShardManager;
use Symfony\Component\Console\Helper\Helper;

/**
 * Class ShardManagerHelper
 *
 * @package Doctrine\Sharding
 */
class ShardManagerHelper extends Helper
{
	/**
	 * @var ShardManager
	 */
	protected $sm;

	/**
	 * @param \Doctrine\DBAL\Sharding\ShardManager $sm
	 */
	public function __construct(ShardManager $sm)
	{
		$this->sm = $sm;
	}

	/**
	 * @return \Doctrine\DBAL\Sharding\ShardManager
	 */
	public function getShardManager()
	{
		return $this->sm;
	}

	/**
	 * Returns the canonical name of this helper.
	 *
	 * @return string The canonical name
	 */
	public function getName()
	{
		return 'ShardManager';
	}

}