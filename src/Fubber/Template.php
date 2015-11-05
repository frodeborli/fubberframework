<?php
namespace Fubber;

/**
* Simple PHP-based template engine. Example usage:
*
* <code>
* <?php
* $template = new Template($filePath);
* $template->someValue = 'value';
* Template::headJs('path');
* $template->render();
* ?>
* </code>
*/
class Template {
	public static $head = array();

	/**
	*	Add a script to be loaded in <head>
	*
	*	@param string $path
	*/
	public static function headJs($path) {
		self::$head[$path] = '<script src="'.htmlspecialchars($path).'"></script>';
	}

	/**
	*	Add a css file to be loaded in <head>
	*
	*	@param string $path
	*/
	public static function headCss($path) {
		self::$head[$path] = '<link rel="stylesheet" type="text/css" href="'.htmlspecialchars($path).'" />';
	}

	protected $_templateFile;
	protected $_vars = array();

	/**
	*	@param string $templateFile
	*/
	public function __construct($templateFile) {
		$this->_templateFile = $templateFile;
	}

	public function __set($name, $value) {
		$this->_vars[$name] = $value;
	}

	public function __get($name) {
		if(!isset($this->_vars[$name])) return '$this->'.$name.' not set';
		if(is_callable($this->_vars[$name])) {
			ob_start();
			$res = call_user_func($this->_vars[$name]);
			$output = ob_get_contents();
			ob_end_clean();
			if($output) return $output;
			return $res;
		}
		return $this->_vars[$name];
	}

	public function __isset($name) {
		return isset($this->_vars[$name]);
	}

	/**
	*	Render the template
	*/
	public function render() {
		require($this->_templateFile);
	}
}
