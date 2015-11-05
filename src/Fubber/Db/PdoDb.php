<?php
namespace Fubber\Db;

/**
*	PDO database wrapper using the IDb interface
*/
class PdoDb implements IDb {
	protected static $idx = 1;
	public $pdo;
	protected $_lastInsertId = NULL;

	protected $statements = array();

	/**
	*	@param PDO $pdo
	*/
	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	/**
	*	@see IDb::lastInsertId()
	*/
	public function lastInsertId() {
		if($this->_lastInsertId)
			return $this->_lastInsertId;
		return NULL;
	}

	protected function prepare($statement) {
		if(isset($this->statements[$statement]))
			return $this->statements[$statement];
		else
			return $this->statements[$statement] = $this->pdo->prepare($statement);
	}

	protected static function sendDebug($statement, $vars) {
		return;
		if(self::$idx > 15) return;
		$sql = $statement;
		foreach($vars as $var) {
			$pos = strpos($sql, '?');
			$sql = substr($sql, 0, $pos).$var.substr($sql, $pos+1);
		}
		$prefix = self::$idx < 10 ? '0' : '';
		header("X-Sql-".$prefix.(self::$idx++).": ".$sql);
	}

	/**
	*	Query the database and return an array of objects
	*
	*	@see IDb::query()
	*/
	public function query($statement, array $vars=array(), $className='stdClass') {
		self::sendDebug($statement, $vars);
		$prepared = $this->prepare($statement);
		$res = $prepared->execute($vars);
		if(!$res) throw new Exception("Invalid query ($sql).");
		$result = $prepared->fetchAll(PDO::FETCH_CLASS, $className);;
		$prepared->closeCursor();
		return $result;
	}

	/**
	*	Query the database and return a single object that is the first row in the result set
	*
	*	@see IDb::queryOne()
	*/
	public function queryOne($statement, array $vars=array(), $className='stdClass') {
		self::sendDebug($statement, $vars);
		$prepared = $this->prepare($statement);
		$res = $prepared->execute($vars);
		if(!$res) throw new Exception("Invalid query ($sql).");
		$result = $prepared->fetchObject($className);
		$prepared->closeCursor();
		return $result;
	}

	/**
	*	Query the database and return the first column of the first row
	*
	*	@see IDb::queryField()
	*/
	public function queryField($statement, array $vars=array()) {
		self::sendDebug($statement, $vars);
		$prepared = $this->prepare($statement);
		$res = $prepared->execute($vars);
		if(!$res) throw new Exception("Invalid query ($sql).");
		return $prepared->fetchColumn();
	}

	/**
	*	Query the database and return an array of values with the first column of the result
	*
	*	@see IDb::queryColumn()
	*/
	public function queryColumn($statement, array $vars=array()) {
		self::sendDebug($statement, $vars);
		$prepared = $this->prepare($statement);
		$res = $prepared->execute($vars);
		if(!$res) throw new Exception("Invalid query ($sql).");
		return $prepared->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	*	Execute a query. If the query fails, throws an exception. Else it returns the number of affected rows.
	*
	*	@param string $statement
	*	@param array $vars
	*	@return bool|int
	*/
	public function exec($statement, array $vars=array()) {
		self::sendDebug($statement, $vars);
		$prepared = $this->pdo->prepare($statement);
		$res = $prepared->execute($vars);
		if($res) {
			$this->_lastInsertId = $this->pdo->lastInsertId();
			return $prepared->rowCount();
		} else {
			$this->_lastInsertId = NULL;
		}
		return FALSE;
	}

	/**
	*	@see IDb:::beginTransaction()
	*/
	public function beginTransaction() {
		return $this->pdo->beginTransaction();
	}

	/**
	*	@see IDb::commit()
	*/
	public function commit() {
		return $this->pdo->commit();
	}

	/**
	*	@see IDb::rollBack();
	*/
	public function rollBack() {
		return $this->pdo->rollBack();
	}

	protected function _rewrite($statement, array $vars) {
		$parts = explode("?", $statement);
		if(sizeof($parts) !== sizeof($vars)+1)
			throw new Exception("The number of ? does not match the number of vars");
		$sql = '';
		foreach($vars as $var) {
			if($var === NULL)
				$sql .= array_shift($parts).'NULL';
			else
				$sql .= array_shift($parts).$this->pdo->quote($var);
		}
		$sql .= array_shift($parts);
		return $sql;
	}
}
