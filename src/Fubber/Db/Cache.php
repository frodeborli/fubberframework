<?php
namespace Fubber\Db;
use Fubber\Caching\ICache;

/**
*	Cache that uses the database as a backend. Should perform quite well for a small site.
*/
class Cache implements ICache {
	protected $_db;

	/**
	*	@param PdoDb
	*/
	public function __construct(PdoDb $db) {
		$this->_db = $db;
	}

	/**
	*	@see ICache::get()
	*/
	public function get($name) {
		$res = $this->_db->queryField("SELECT val FROM cache WHERE id=? AND expires>NOW()", array($name));
		if($res) return unserialize($res);
		return NULL;
	}

	/**
	*	@see ICache::set()
	*/
	public function set($name, $val, $ttl) {
		if($ttl===NULL) throw new Exception("ttl missing in Cache::set request");
		$val = serialize($val);
		$expires = date('Y-m-d H:i:s', time()+$ttl);
		return $this->_db->exec('INSERT INTO cache (id,val,expires) VALUES (?,?,?) ON DUPLICATE KEY UPDATE val=?, expires=?', array(
			$name, $val, $expires,
			$val, $expires
		));
	}
}
