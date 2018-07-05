<?php
namespace framework\core;

class Dispatcher {

    protected $events = array();

    protected $filters = array();

    public function run($name, array $params = array()) {
        $output = '';

        if (!empty($this->filters[$name]['before'])) {
            $this->filter($this->filters[$name]['before'], $params, $output);
        }

        $output = $this->execute($this->get($name), $params);

        if (!empty($this->filters[$name]['after'])) {
            $this->filter($this->filters[$name]['after'], $params, $output);
        }

        return $output;
    }

    public function set($name, $callback) {
        $this->events[$name] = $callback;
    }

    public function get($name) {
        return isset($this->events[$name]) ? $this->events[$name] : null;
    }

    public function has($name) {
        return isset($this->events[$name]);
    }

    public function clear($name = null) {
        if ($name !== null) {
            unset($this->events[$name]);
            unset($this->filters[$name]);
        }
        else {
            $this->events = array();
            $this->filters = array();
        }
    }

    public function hook($name, $type, $callback) {
        $this->filters[$name][$type][] = $callback;
    }

    public function filter($filters, &$params, &$output) {
        $args = array(&$params, &$output);
        foreach ($filters as $callback) {
            $continue = $this->execute($callback, $args);
            if ($continue === false) break;
        }
    }

    public static function execute($callback, array &$params = array()) {
        if (is_callable($callback)) {
            return is_array($callback) ?
                self::invokeMethod($callback, $params) :
                self::callFunction($callback, $params);
        }
        else {
            throw new \Exception('Invalid callback specified.');
        }
    }

    public static function callFunction($func, array &$params = array()) {
		if(count($params) == 0){
			return $func();
		}
		 return call_user_func_array($func, $params);
    }

    public static function invokeMethod($func, array &$params = array()) {

        list($class, $method) = $func;

		$instance = is_object($class);
		
		if(count($params) == 0){
			 return ($instance) ?
                    $class->$method() :
                    $class::$method();
		}
		return call_user_func_array($func, $params);
    }

    public function reset() {
        $this->events = array();
        $this->filters = array();
    }
}
