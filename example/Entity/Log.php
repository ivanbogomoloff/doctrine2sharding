<?php

namespace Entity;

/**
 * @Entity
 * @Table(name="user_logs")
 */
class Log {
	/**
	 * @Id
	 * @GeneratedValue (strategy="AUTO")
	 * @Column(type="integer", name="user_id")
	 */
	protected $userId;
	/**
	 * @Column(type="string", name="user_log")
	 */
	protected $userName;


	/**
	 * @return mixed
	 */
	public function getUserName()
	{
		return $this->userName;
	}

	/**
	 * @param mixed $userName
	 */
	public function setLogId($userName)
	{
		$this->userName = $userName;
	}

	/**
	 * @return mixed
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * @param mixed $userId
	 */
	public function setUserId($userId)
	{
		$this->userId = $userId;
	}
}