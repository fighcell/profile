<?php
class Profile implements ArrayAccess, Iterator, Countable {
	private $props = array(
		"width","height","color","xhr","json","swf","svg",
		"font","canvas","video","touch","layout","offline",
		"location","workers","storage"
	);

	private $data;
	private $profile;
	
	public function __construct($data) {
		try {
			$json_data = file_get_contents($data);
			$this->data = json_decode($json_data);
		} catch (Exception $e) {
    		echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
		$this->profile = array();
		$this->init();
	}
	public function init() {
		if (empty($_COOKIE['profile'])) {
			$this->create();
		} else {
			$this->get();
		}
		$this->update();
	}
	public function get() {
		$data = json_decode(stripslashes($_COOKIE['profile']), true);
		foreach ($data as $property => $value) {
			$this->profile[$property] = $value;
		}
	}
	public function set() {
		setcookie('profile', json_encode($this->profile), time() + 3600 * 24 *30, '/');
	}
	public function update() {
		$this->profile['layout'] = $this->detect('layout');
		$this->set();
	}
	public function create() {
		$basic = $this->detect('default');
		$useragent = $this->detect('useragent');
		$this->profile = array_merge($basic,$useragent);
	}
	public function detect($what){
		switch($what) {
			case "useragent":
				$ua = $_SERVER['HTTP_USER_AGENT'];
				$profile = array();
				$matches = array();
				foreach ($this->data->profiles as $device => $fragment) {
					if (preg_match($fragment->match, $ua)){array_push($matches, $fragment);}
				}
				foreach ($matches as $device){
					$new = (array) $device->profile;
					if ($new) {
						$old = $profile;
						$profile = array_merge($old,$new);
					}
				}
				// remove $GLOBALS -- only used for testing
				$GLOBALS['fragments'] = $matches;
				return $profile;
				break;
			case 'layout':
				$layout = "";
				$width = $this->profile['width'];
				foreach ($this->data->layouts as $name => $value) {
					if ($width >= $value->min && $width <= $value->max) {
						$layout = $name;
					}
				}
				return $layout;
				break;
			default:
				$profile = (array) $this->data->default;
				return $profile;
		}	
	}
	public function offsetExists($offset){
		return isset($this->profile[$offset]);	
	}
	public function offsetGet($offset){
		return isset($this->profile[$offset]) ? $this->profile[$offset] : null;
	}
	public function offsetSet($offset, $value){
		return $this->profile[$offset] = $value;	
	}
	public function offsetUnset($offset){
		unset($this->profile[$offset]);	
	}

    public function rewind() {
        reset($this->profile);
    }

    public function current() {
        return current($this->profile);
    }

    public function key() {
        return key($this->profile);
    }

    public function next() {
        return next($this->profile);
    }

    public function valid() {
        return $this->current() !== false;
    }    

    public function count() {
     return count($this->profile);
    }
}
$profile = new Profile('./profile.json');
?>