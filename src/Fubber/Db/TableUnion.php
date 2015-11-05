<?php
namespace Fubber\Db;

/**
*	This class combines two TableSets into one. This works similarly to using the OR clause in SQL.
*/
class TableUnion extends TableSet {
	protected $_sets = array();

	public function __construct() {
		$args = func_get_args();
		foreach($args as $arg) {
			$this->_sets[] = array('set' => $arg);
			$this->_class = $arg->_class;
		}
	}

	public function count() {
		global $config;
		$statements = array();
		$vars = array();
		foreach($this->_sets as $s) {
			$set = $s['set'];
			$set->_noLimit = TRUE;
			$sql = $set->_buildSql();
			$set->_noLimit = FALSE;
			$statements[] = $sql[0];
			foreach($sql[1] as $v)
				$vars[] = $v;
		}
		$sql = "(".implode(") UNION (", $statements).")";

		$countSql = 'SELECT COUNT(*) FROM ('.$sql.') AS tmp';
		$res = $config->db->queryField($countSql, $vars);
		return intval($res);
	}

	public function where($field, $op, $value) {
		foreach($this->_sets as $set)
			$set['set']->where($field, $op, $value);
		return $this;
	}

	public function one() {
		global $config;
		$_class = $this->_class;
		$statements = array();
		$vars = array();
		foreach($this->_sets as $s) {
			$set = $s['set'];
			if($this->_order) {
				$set->order($this->_order, $this->_orderDesc);
			}
			$set->_noLimit = TRUE;
			$sql = $set->_buildSql();
			$set->_noLimit = FALSE;
			$statements[] = $sql[0];
			foreach($sql[1] as $v)
				$vars[] = $v;
		}
		$sql = "(".implode(") UNION (", $statements).")";

		if($this->_order) {
			if(!in_array($this->_order, $_class::$_cols))
				throw new Exception("Unknown column ".$where[0]." in ".$_class);
			$sql .= ' ORDER BY '.$this->_order;
			if($this->_orderDesc)
				$sql .= ' DESC';
		}

		$sql .= ' LIMIT 1';

		$res = $config->db->queryOne($sql, $vars, $_class);
		return $res;
	}

	public function getIterator() {
		// If ordering, must iterate over each set and compare
		// Else must iterate until we get a result
		global $config;
		$_class = $this->_class;
		$statements = array();
		$vars = array();
		foreach($this->_sets as $s) {
			$set = $s['set'];
			if($this->_fields) {
				$set->fields($this->_fields);
			}
			if($this->_order) {
				$set->order($this->_order, $this->_orderDesc);
			}
			$set->_noLimit = TRUE;
			$sql = $set->_buildSql();
			$set->_noLimit = FALSE;
			$statements[] = $sql[0];
			foreach($sql[1] as $v)
				$vars[] = $v;
		}
		$sql = "(".implode(") UNION (", $statements).")";

		if($this->_order) {
			if(!in_array($this->_order, $_class::$_cols))
				throw new Exception("Unknown column ".$where[0]." in ".$_class);
			$sql .= ' ORDER BY '.$this->_order;
			if($this->_orderDesc)
				$sql .= ' DESC';
		}

//		$sql .= ' LIMIT 1';

		if($this->_fields === '*') {
			$res = $config->db->query($sql, $vars, $_class);
		} else {
			$res = $config->db->query($sql, $vars, 'stdClass');
		}

		return new ArrayIterator($res);
	}
}
