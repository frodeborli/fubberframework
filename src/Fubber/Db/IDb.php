<?php
namespace Fubber\Db;

/**
*	Generic DB driver interface. Can be mapped directly to most SQL database engines.
*/
interface IDb {
	/**
	*	Return the primary key of the last inserted row
	*
	*	@return mixed
	*/
	public function lastInsertId(); // Return the primary key of last inserted row

	/**
	*	Perform an SQL query
	*
	*	@param string $sql
	*	@param array $vars
	*	@param string $className
	*	@return array Returns an array of stdClass objects, or whatever class provided in $className
	*/
	public function query($sql, array $vars=array(), $className='stdClass'); // Return array of objects

	/**
	*	Perform an SQL query, but only return the first field of the first row.
	*
	*	@param string $sql
	*	@param array $vars
	*	@return mixed
	*/
	public function queryField($sql, array $vars=array()); // Select one column of the first row

	/**
	*	Start a database transaction
	*/
	public function beginTransaction(); // Start a database transaction

	/**
	*	Commit the database transaction
	*/
	public function commit(); // Commit a database transaction

	/**
	*	Rollback the database transaction
	*/
	public function rollBack(); // Roll back a database transaction

	/**
	*	Select the first row in the result as an instance of $className
	*
	*	@param string $sql
	*	@param array $vars
	*	@param string $className
	*	@return mixed
	*/
	public function queryOne($sql, array $vars=array(), $className='stdClass'); // Return on instance of $className

	/**
	*	Execute a database statement
	*
	*	@param string $sql
	*	@param array $vars
	*	@return bool
	*/
	public function exec($sql, array $vars=array()); // Execute a statement
}
