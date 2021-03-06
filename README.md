# Dcotrine2 Shard Manager
For me and my use cases

# Usage
Create your own connection constructor, for example
```php
/**
 * Return new connection
 * @return \Doctrine\DBAL\Connection
 * @throws \Doctrine\DBAL\DBALException
 */
function getConnection()
{
	$conn = DriverManager::getConnection([
		 'wrapperClass' => 'Doctrine\DBAL\Sharding\PoolingShardConnection',
		 'driver'       => 'pdo_mysql',
		 'global'       => [
			 'driver'   => 'pdo_mysql',
			 'username' => 'root',
			 'password' => 'root',
			 'dbname' 	=> 'central_database',
			 'host'     => '192.168.1.104'
		 ],
		 'shards'       => [
			[
				 'id'		=> 1,
				 'driver'   => 'pdo_mysql',
				 'username' => 'root',
				 'password' => 'root',
				 'dbname' 	=> 'db2',
				 'host'     => '192.168.1.105'
			 ],
			[
				'id'		=> 2,
				'driver'   	=> 'pdo_mysql',
				'username' 	=> 'root',
				'password' 	=> 'root',
				'dbname' 	=> 'db3',
				'host'     	=> '192.168.1.112'
			],
			//more connection here...
		 ],
		 'shardChoser' => 'Doctrine\Sharding\SqlShardChoser',
	 ]);

	
	return $conn;
}
```
Then create your own ShardManager constructor
```php
/**
 * @return \Doctrine\Sharding\SqlShardManager
 */
function getShardManager()
{
	$conn    	= getConnection();
	$globalConn = getConnection();

	$manager = new \Doctrine\Sharding\SqlShardManager($conn, $globalConn, [
			'global' => Setup::createAnnotationMetadataConfiguration(['path/to/Entities/dir'], true),
			'shards' => Setup::createAnnotationMetadataConfiguration(['another/path/to/Entities/dir'], true)
	]);

	return $manager;
}
```
And then you can use it
```php
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

$globalEm->beginTransaction(); //START TRANSACTION on 192.168.1.104 host
$shardEm->beginTransaction(); //START TRANSACTION on "same user" host
try
{
	$user = new \Entity\User();
	$user->setUserName('shard user');

	$shardEm->persist($user);
	$shardEm->flush(); //INSERT INTO users ...
	
	$userLog = new \Entity\Log();
	$userLog->setLogId('logged');

	$globalEm->persist($userLog);
	$globalEm->flush(); //INSERT INTO user_logs ...
        
	$shardEm->commit(); //COMMIT on "same user" host
	$globalEm->commit(); //COMMIT on 192.168.1.104 host
}
catch (\Exception $e)
{
	$shardEm->rollback();
	$globalEm->rollback();
	throw $e;
}
```
# Command for update across all shards and global
```php
php bin/doctrine.php orm:sharding:schema-tool:update [--dump-sql|--force]
```
