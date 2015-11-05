<?php
namespace Fubber;
/**
*	Exception to be used whenever user provided data does not validate. Accepts the validation errors as an associative array.
*/
class ValidationException extends Exception {
	public $errors;

	/**
	*	@param array $errors
	*	@param int $code
	*/
	public function __construct(array $errors, $code=0) {
		$this->errors = $errors;
		$errStr = '';
		foreach($errors as $k => $v)
			$errStr .= $k.": ".$v."<br>\n";
		$errStr = trim($errStr);
		parent::__construct($errStr, $code);
	}
}
