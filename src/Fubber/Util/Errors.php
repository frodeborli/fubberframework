<?php
namespace Fubber\Util;

/**
* This class provides validation functionality on objects and arrays.
*
* Example:
*
* <code>
* <?php
* $errors = new Errors($array);			// $array is a set of key-value pairs. However, the argument is optional
*
* $errors->password('pass');			// Requires that the 'pass' property is at minimum 8 characters long and contains one number and one character
* $errors->required('username');			// The 'username' property is required
* $errors->minLen('username', 3');		// The 'username' property must be at least 3 characters
* $errors->email('email');			// The 'email' property must be a proper e-mail address
*
* if($errors->pass != $errors->passConfirm)
*	$errors->addError('passConfirm', 'Passwords do not match');
*
* var_dump($errors->isInvalid());			// Prints NULL or an array of errors, where the key is the property name
* ?>
* </code>
*/
class Errors {
	protected $_errors = array();
	protected $_val = NULL;

	/**
	*	@param array|object|null $val Pre-initialize the values.
	*/
	public function __construct($val = null) {
		if(!$val)
			$this->_val = array();
		else
			$this->_val = $val;
	}

	public function __get($fieldname) {
		if(is_array($this->_val))
			return $this->_val[$fieldname];
		else if(is_object($this->_val))
			return $this->_val->$fieldname;
		else
			return NULL;
	}

	public function __isset($fieldname) {
		if(is_array($this->_val)) {
			return isset($this->_val[$fieldname]);
		} else if(is_object($this->_val)) {
			return !!($this->_val->$fieldname);
		} else {
			return FALSE;
		}
	}

	/**
	*	@param string $fieldname
	*	@return bool
	*/
	public function hasError($fieldname) {
		return isset($this->_errors[$fieldname]);
	}

	/**
	*	Add a custom validation error
	*
	*	@param string $fieldname
	*	@param string $error
	*	@return $this
	*/
	public function addError($fieldname, $error) {
		$this->_errors[$fieldname] = $error;
		return $this;
	}

	/**
	*	Check if there are any validation errors
	*
	*	@return array|false
	*/
	public function isInvalid() {
		if(sizeof($this->_errors)===0) return FALSE;
		return $this->_errors;
	}

	/**
	*	Password validator for field
	*
	*	@param string $fieldname
	*	@return $this
	*/
	public function password($fieldname) {
		if(empty($this->$fieldname)) return $this;
		$value = $this->$fieldname;

		if (strlen($value) < 8) return $this->addError($fieldname, 'Passwords must be 8 characters or more');
		if (!preg_match('#[0-9]+#', $value)) return $this->addError($fieldname, 'Password must contain a number');
		if (!preg_match('#[a-zA-Z]+#', $value)) return $this->addError($fieldname, 'Password must contain at least one character');
		return $this;
	}

	/**
	*	Minimum length validator for field
	*
	*	@param string $fieldname
	*	@param int $minLen
	*	@return $this
	*/
	public function minLen($fieldname, $minLen) {
		if(empty($this->$fieldname)) return $this;
		$value = $this->$fieldname;
		if(strlen($value)<$minLen) $this->_errors[$fieldname] = 'Minimum length is '.$minLen.' characters';
		return $this;
	}

	/**
	*	Maximum length validator for field
	*
	*	@param string $fieldname
	*	@param int $maxLen
	*	@return $this
	*/
	public function maxLen($fieldname, $maxLen) {
		if(empty($this->$fieldname)) return $this;
		$value = $this->$fieldname;
		if(strlen($value)>$maxLen) $this->_errors[$fieldname] = 'Maximum length is '.$maxLen.' characters';
		return $this;
	}

	/**
	*	Field must not be empty
	*
	*	@param string $fieldname
	*	@return $this
	*/
	public function required($fieldname) {
		if(empty($this->$fieldname) || trim($this->$fieldname)=='') $this->_errors[$fieldname] = 'Required';
		return $this;
	}

	/**
	*	Field must be a valid e-mail address
	*
	*	@param string $fieldname
	*	@return $this
	*/
	public function email($fieldname) {
		if(empty($this->$fieldname)) return $this;
		$value = $this->$fieldname;
		if(empty($this->$fieldname)) return $this;
		if(!filter_var($value, FILTER_VALIDATE_EMAIL))
			$this->_errors[$fieldname] = 'Invalid e-mail address';
		return $this;
	}

	/**
	*	Field must be one of the supplied values
	*
	*	@param string $fieldname
	*	@param array $values
	*	@return $this
	*/
	public function oneOf($fieldname, array $values) {
		if(empty($this->$fieldname)) return $this;
		$value = $this->$fieldname;
		if(empty($this->$fieldname)) return $this;
		if(!in_array($value, $values))
			$this->_errors[$fieldname] = 'Illegal value';
		return $this;
	}
}
