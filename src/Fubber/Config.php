<?php
namespace Fubber;
/**
*	The Config class relies on LazyProp to store configuration data in a unique way. For example, the database connection is configured
*	by adding a lazily evaluated property: $config->lazy('db', function() { return new PDO( ... ); });
*
*	Whenever some component need to access the database, the $config->db will be the PDO-instance requested. The connection is, however,
* 	not created until the $config->db property is accessed for the first time.
*/
class Config {
	use LazyProp;

	public function __construct(array $initial=array()) {
		foreach($initial as $key => $value) {
			if(is_callable($value))
				$this->lazy($key, $value);
			else
				$this->$key = $value;
		}
	}

}
