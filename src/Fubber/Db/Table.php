<?php
namespace Fubber\Db;
use Fubber\Parsing\StringParser;

/**
*	Represents tabular data from any database backend.
*/
abstract class Table {
	use \Fubber\Util\LazyProp;

	/**
	*	Interface for querying arbitrary date from various backends.
	*
	*	Table::q('SELECT nickname FROM User'); // All User rows, but we only fetch the nickname column.
	*
	*	Table::q('User'); // All User rows
	*
	*	@param string $query
	*	@param array $vars
	*	@return TableSet Returns 
	*/
	public static final function q($query, array $vars=array()) {
		// Table::q('User WHERE id=?')
		$p = new StringParser($query, TRUE);
		$table = $p->consume();
		$cols = FALSE;
		if($p->type === StringParser::CONSUMED_WORD && $table === 'SELECT') {
			$cols = array();
			// This is a special query, selecting only a specific column or more
			while(TRUE) {
				$col = $p->consume();
				if($p->type !== StringParser::CONSUMED_WORD) throw new QueryException("Expects string column names after SELECT keyword, separated by commas.");
				$cols[] = $col;
				$peek = $p->consume();
				if($p->type !== StringParser::CONSUMED_SYMBOLS || $peek !== ',') {
					$p->push($peek);
					break;
				}
			}
			$from = $p->consume();
			if($p->type !== StringParser::CONSUMED_WORD || $from !== 'FROM') throw new QueryException("Expects FROM keyword after SELECT clause.");
			$table = $p->consume();
		}
		if(!$table) throw new QueryException("Empty query");
		if($p->type !== StringParser::CONSUMED_WORD) throw new QueryException("Expected class name or SELECT keyword.");
		if(!class_exists($table)) throw new QueryException("Class '".$table."' not found.");
		if(!is_subclass_of($table, 'Table')) throw new QueryException("Class '".$table."' must extend Table");

		$rows = $table::all();

		if($cols !== FALSE) {
			$rows->fields(implode(",", $cols));
		}

		$wheres = array();

		while($word = $p->consume()) {
			if($p->type !== StringParser::CONSUMED_WORD) throw new QueryException("Expects keyword after class name.");
			switch($word) {
				case 'WHERE' :
					while(true) {
						// Consume WHERE parts
						$col = $p->consume();
						if($p->type !== StringParser::CONSUMED_WORD) throw new QueryException("Expects column name.");
//						if(!in_array($col, $table::$_cols)) throw new QueryException("Column '".$col."' not found in '".$table."'.");
						$op = $p->consume();
						if($p->type !== StringParser::CONSUMED_SYMBOLS) throw new QueryException("Expects operator.");
						switch($op) {
							case '=?' :
							case '>?' :
							case '<?' :
								$rows->where($col,$op[0],array_shift($vars));
								break;
							case '<=?' :
							case '>=?' :
								$rows->where($col,$op[0].$op[1],array_shift($vars));
								break;
							default :
								throw new QueryException("Unknown operator '$op'.");
						}
						$peek = $p->consume();
						if($peek !== 'AND') {
							$p->push($peek);
							break;
						}
					}
					break;
				case 'LIMIT' :
					$limit = intval($p->consume());
					$offset = FALSE;
					if($p->type !== StringParser::CONSUMED_INTEGER) throw new QueryException("Expects integer after LIMIT keyword.");
					$peek = $p->consume();
					if($peek === ',') {
						$offset = $limit;
						$limit = intval($p->consume());
						if($p->type !== StringParser::CONSUMED_INTEGER) throw new QueryException("Expects integer offset and length after LIMIT keyword.");
					} else {
						$p->push($peek);
					}
					if($offset !== FALSE) {
						$rows->limit($limit, $offset);
					} else {
						$rows->limit($limit);
					}
					break;
				case 'ORDER' :
					$col = $p->consume();
					if($p->type !== StringParser::CONSUMED_WORD) throw new QueryException("Expects column name after ORDER keyword.");
					$peek = $p->consume();
					if($p->type === StringParser::CONSUMED_WORD && $peek === 'DESC') {
						$rows->order($col, TRUE);
					} else {
						$p->push($peek);
						$rows->order($col);
					}
					break;
				default :
					throw new QueryException('Unexpected keyword "'.$word.'" encountered. (Queries are case sensitive!)');
			}
		}

		return $rows;
	}

	public static $_table = NULL;
	public static $_primaryKey = NULL;		// ONLY SET THIS IF USING MULTIPLE COLUMN PRIMARY KEYS
	public static $_cols = NULL;

	public function trackId() {
		$primaryKeys = static::$_primaryKey;
		if(!$primaryKeys) $primaryKeys = array(static::$_cols[0]);
		$vals = array();
		foreach($primaryKeys as $pk) {
			if(!$this->$pk) {
				throw new Exception("Can't create track-id without primary keys set");
			}
			$vals[] = $this->$pk;
		}
		return get_class($this).':'.implode(",", $vals);
	}

	public function isInvalid() {
		return FALSE;
	}

	public function getClientData() {
		return array();
	}

	public static function all() {
		$res = new TableSet(get_called_class());
		return $res;
	}

	public static function tableExpose($rows) {
		$res = array();
		foreach($rows as $row)
			$res[] = $row->expose();
		return $res;
	}

	/**
	*	Return an unfiltered TableSet - bypassing any security built into overriding the all()-method
	*
	*	@see all()
	*	@return TableSet
	*/
	public static function allUnsafe() {
		$res = new TableSet(get_called_class());
		return $res;
	}

	/**
	*	Load a single row as an instance of the given class. Bypass any filtering built into the all()-method.
	*
	*	@param mixed $primaryKey,...
	*	@return Table
	*/
	public static function loadUnsafe() {
		global $config;

		$primaryKeys = static::$_primaryKey;
		if(!$primaryKeys) $primaryKeys = array(static::$_cols[0]);

		$args = func_get_args();
		if(sizeof($args)!=sizeof($primaryKeys)) throw new Exception("Invalid number of arguments. Expects ".implode(", ", $primaryKeys).".");

		$wheres = array();
		foreach($primaryKeys as $k) {
			$wheres[] = $k."=?";
		}
		$sql = 'SELECT * FROM '.static::$_table.' WHERE '.implode(" AND ",$wheres);

		return $config->db->queryOne($sql, $args, get_called_class());
	}

	/**
	*	Load a single instance of the given class.
	*
	*	@param mixed $primaryKey,...
	*/
	public static function load() {
		global $config;

		$primaryKeys = static::$_primaryKey;
		if(!$primaryKeys) $primaryKeys = array(static::$_cols[0]);

		$args = func_get_args();
		if(sizeof($args)!=sizeof($primaryKeys)) throw new Exception("Invalid number of arguments. Expects ".implode(", ", $primaryKeys).".");

		$className = get_called_class();
		$all = $className::all();
		$wheres = array();
		foreach($primaryKeys as $k) {
			$all->where($k,'=',array_shift($args));
		}
		foreach($all as $res) return $res;
		return null;
	}

	/**
	*	Delete this object
	*
	*	@see IDb:::exec()
	*	@return bool|int
	*/
	public function delete() {
		global $config;

		if(!static::$_primaryKey)
			$primaryKey = static::$_cols[0];
		else
			$primaryKey = static::$_primaryKey;

		$wheres = array();
		if(is_array($primaryKey)) {
			foreach($primaryKey as $pk) {
				$wheres[] = $pk.'=?';
				$vals[] = $this->$pk;
			}
		} else {
			$wheres[] = $primaryKey.'=?';
			$vals[] = $this->$primaryKey;
		}
		$sql = 'DELETE FROM '.static::$_table.' WHERE '.implode(" AND ", $wheres).' LIMIT 1';
		return $config->db->exec($sql, $vals);
	}

	/**
	*	Save any changes
	*
	*	@return bool
	*/
	public function save() {
		global $config;

		$errors = $this->isInvalid();
		if($errors) {
			throw new ValidationException($errors);
		}

		if(!static::$_primaryKey)
			$primaryKey = static::$_cols[0];
		else
			$primaryKey = static::$_primaryKey;

		// There is no way we can know if this is an insert or an update
		$colNames = array();
		$vals = array();
		$marks = array();
		$updates = array();
		foreach(static::$_cols as $col) {
			$vals[] = $this->$col;
			$colNames[] = $col;
			$marks[] = '?';
		}
		foreach(static::$_cols as $col) {
			// Don't update the primary key, when duplicate key
			if(is_array($primaryKey) && in_array($col, $primaryKey))
				continue;
			else if($col == $primaryKey)
				continue;

			$updates[] = $col.'=?';
			$vals[] = $this->$col;
		}

		$sql = 'INSERT INTO '.static::$_table.' ('.implode(",", $colNames).') VALUES ('.implode(",", $marks).') ON DUPLICATE KEY UPDATE '.implode(",", $updates);
		$res = $config->db->exec($sql, $vals);
		if($res===FALSE) {
			throw new Exception("Unable to insert row");
		}
		if(!is_array($primaryKey)) {
			$lastInsertId = $config->db->lastInsertId();
			if($lastInsertId)
				$this->$primaryKey = $lastInsertId;
		}
		return TRUE;
	}

}
