<?php
namespace Fubber\Util;

/**
*	Class that creates UUIDs, and helps with managing them. Simple example:
*
*	$uuid = new UUID();
*	echo $uuid->toString();	// outputs a string representation of the UUID.
*	echo $uuid->toBinary(); // binary representation
*/
class UUID {
	protected $uuid;

	/**
	*	@param string $uuid
	*	@param bool $isBinary
	*/
	public function __construct($uuid=NULL, $isBinary=FALSE) {
		if($uuid === NULL) {
			$this->uuid = self::_createNew();
		} else {
			if($isBinary) {
				$this->uuid = self::_fromBinary($uuid);
			} else {
				$this->uuid = $uuid;
			}
		}
	}

	/**
	*	Return the UUID as a string
	*
	*	@return string
	*/
	public function toString() {
		return $this->uuid;
	}

	public function __toString() {
		return $this->uuid;
	}

	/**
	*	Return the UUID as a binary string
	*
	*	@return string
	*/
	public function toBinary() {
		return self::stringToBinary($this->uuid);
	}

	protected static function _createNew() {

		$data = openssl_random_pseudo_bytes(16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	/**
	*	Convert a UUID binary to string
	*
	*	@param string $uuid
	*	@return string
	*/
	public static function binaryToString($uuid) {
		$string = unpack("H*", $uuid);
		$string = $string[1];
		if(strlen($string)!==32) return NULL;
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($string, 4));
	}

	/**
	*	Convert a UUID string to binary
	*
	*	@param string $uuid
	*	@return string
	*/
	public static function stringToBinary($uuid) {
		return pack("H*", str_replace('-', '', strtolower($uuid)));
	}
}
