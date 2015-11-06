<?php
namespace Fubber\Caching;

/**
*	Cache provider interface.
*/
interface ICache {
	/**
	*	Get a key
	*
	*	@param $key
	*	@return mixed|null
	*/
	public function get($key);

	/**
	*	Set a key
	*	@param $key
	*	@param $val
	*	@param $ttl
	*	@return $this
	*/
	public function set($key, $val, $ttl);
}
