<?
/**
 * Core class -- Handles links and class-loading amongst other things.
 */
class core {
	static $alphabet_en		= array("a","e","f","g","h","i","k","l","m","n","o","p","r","s","t","u","v");
	static $alphabet_sm		= array("a","e","i","o","u","f","g","l","m","n","p","s","t","v","h","k","r");
	
	private static $config = null;
	
	function __autoload($className) {
		$sp = explode("_", $className);
		
		if(count($sp) == 1) {
			/* If there are no underscores, it should be in util */
			$sp[0] = core::alphanumeric($sp[0]);
			$fn = dirname(__FILE__)."/util/".$sp[0].".php";
		} else {
			/* Otherwise look in the folder suggested by the name */
			$sp[0] = core::alphanumeric($sp[0]);
			$sp[1] = core::alphanumeric($sp[1]);
			$fn = dirname(__FILE__)."/".$sp[1]."/".$sp[0]."_".$sp[1].".php";
		}
	
		if(file_exists($fn)) {
			require_once($fn);
			
			/* Call init function if one is defined */
			if(is_callable($className . "::init")) {
				try {
					call_user_func($className . "::init");
				} catch(Exception $e) {
					/* If init() threw an exception, chuck a hissy fit */
					core::fizzle("The class '$className' did not initialise: " . $e);
				}
			}
		} else {
			throw new Exception("The class '$className' could not be found at $fn.");
		}
	}
	
	static function loadClass($className) {
		if(!class_exists($className)) {
			core::__autoload($className);
		}
	}
	
	static function fizzle($info = '') {
		header("HTTP/1.0 404 Not Found");
		echo "404 at fizzle($info)";
		die();
	}
	
	static function constructURL($controller, $action, $arg, $fmt) {
		$config = core::getConfig('core');
		$part = array();
		
		if(count($arg) == 1 && $action == $config['default']['action']) {
			/* We can abbreviate if there is only one argument and we are using the default view */
			if($controller != $config['default']['controller'] ) {
				/* The controller isn't default, need to add that */
				array_push($part, urlencode($arg[0]));
				array_unshift($part, urlencode($controller));
			} else {
				/* default controller and action. Check for default args */
				if($arg[0] != $config['default']['arg'][0]) {
					array_push($part, urlencode($arg[0]));
				}
			}
		} else {
			/* urlencode all arguments */
			foreach($arg as $a) {
				array_push($part, urlencode($a));
			}
			
			/* Nothing is default: add controller and view */
			array_unshift($part, urlencode($controller), urlencode($action));
		}
		
		/* Only add format suffix if the format is non-default (ie, strip .html) */
		$fmt_suff = (($fmt != $config['default']['format'])? "." . urlencode($fmt) : "");
		return $config['webroot'] . implode("/", $part) . $fmt_suff;
	}
	
	static function redirect($to) {
		global $config;
		header('location: ' . $to);
		die();
	}
	
	static private function alphanumeric($inp) {
		return preg_replace("#[^-a-zA-Z0-9]+#", "-", $inp);
	}
	
	static public function getConfig($className) {
		if(core::$config == null) {
			/* Load config if it is needed */
			include(dirname(__FILE__).'/config.php');
			core::$config = $config;
		}
		
		if(isset(core::$config[$className])) {
			return core::$config[$className];
		} else {
			core::fizzle("No configuration for $className");
			return false;
		}
	}
	
	public static function escapeHTML($inp) {
		return htmlentities($inp, null,'UTF-8');	
	}
	
	public static function getAlphabet() {
		return self::$alphabet_sm;
	}
	
	public static function getPermissions($area) {
		$permission = array();
		$permission['page']['edit'] = true;
		$permission['page']['create'] = true;
		$permission['page']['delete'] = true;
		$permission['word']['edit'] = true;
		$permission['word']['create'] = true;
		$permission['word']['delete'] = true;
		return $permission[$area];
	}
}
?>