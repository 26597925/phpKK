<?php
namespace framework;

use framework\core\Loader;
use framework\core\Dispatcher;
use framework\util\Collection;

class Engine {
	
    protected $vars;

    protected $loader;

    protected $dispatcher;

    public function __construct() {
        $this->vars = array();

        $this->loader = new Loader();
        $this->dispatcher = new Dispatcher();

		
        $this->init();
    }

    public function __call($name, $params) {
        $callback = $this->dispatcher->get($name);

        if (is_callable($callback)) {
            return $this->dispatcher->run($name, $params);
        }

        $shared = (!empty($params)) ? (bool)$params[0] : true;

        return $this->loader->load($name, $shared);
    }

    public function init() {
		static $initialized = false;
        $self = $this;
		if( !defined('BASE_PATH') ) define('BASE_PATH',  realpath('./').DIRECTORY_SEPARATOR);
		if( !defined('APP_PATH') ) define('APP_PATH',  BASE_PATH."source/app");
		if( !defined('CONFIG_PATH') ) define('CONFIG_PATH',  BASE_PATH."source/config");
        if( !defined('CACHE_PATH') ) define('CACHE_PATH',  BASE_PATH."source/cache");
		if( !defined('BASE_URL') ) define('BASE_URL',  rtrim(dirname($_SERVER["SCRIPT_NAME"]), '\\/').'/');

        if ($initialized) {
            $this->vars = array();
            $this->loader->reset();
            $this->dispatcher->reset();
        }
		
        $this->loader->register('request', '\framework\net\Request');
        $this->loader->register('response', '\framework\net\Response');
        $this->loader->register('router', '\framework\net\Router');

        $methods = array(
            'start','stop','route','halt','error','notFound',
			'redirect','etag','lastModified','json','jsonp'
        );
        foreach ($methods as $name) {
            $this->dispatcher->set($name, array($this, '_'.$name));
        }
		
		$this->_loadConfig(CONFIG_PATH."/system.php");
        $this->_loadConfig(CONFIG_PATH."/database.php");
        $this->_loadConfig(CONFIG_PATH."/cache.php");

		$this->_loadRouter(APP_PATH."/controller");

        $initialized = true;
		
    }
	
	public function controller($name){
		$class = "app\controller\\$name";
		return $this->loader->newInstance($class);
	}

    public function model($name){
        $class = "app\model\\$name";
        return $this->loader->newInstance($class);
    }

	public function middleware($name){
		$class = "app\middleware\\$name";
		return $this->loader->newInstance($class);
	}
	
    public function handleErrors($enabled)
    {
        if ($enabled) {
            set_error_handler(array($this, 'handleError'));
            set_exception_handler(array($this, 'handleException'));
        }else {
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function handleError($errno, $errstr, $errfile, $errline) {
        if ($errno & error_reporting()) {
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        }
    }

    public function handleException($e) {
        if ($this->get('app.log_errors')) {
            error_log($e->getMessage());
        }

        $this->error($e);
    }

    public function map($name, $callback) {
        if (method_exists($this, $name)) {
            throw new \Exception('Cannot override an existing framework method.');
        }

        $this->dispatcher->set($name, $callback);
    }

    public function register($name, $class, array $params = array(), $callback = null) {
        if (method_exists($this, $name)) {
            throw new \Exception('Cannot override an existing framework method.');
        }

        $this->loader->register($name, $class, $params, $callback);
    }

    public function before($name, $callback) {
        $this->dispatcher->hook($name, 'before', $callback);
    }

    public function after($name, $callback) {
        $this->dispatcher->hook($name, 'after', $callback);
    }
	
	public function exception($name, $callback){
		$this->dispatcher->hook($name, 'exception', $callback);
	}

    public function get($key = null) {
        if ($key === null) return $this->vars;

        return isset($this->vars[$key]) ? $this->vars[$key] : null;
    }

    public function set($key, $value = null) {
        if (is_array($key) || is_object($key)) {
            foreach ($key as $k => $v) {
                $this->vars[$k] = $v;
            }
        }
        else {
            $this->vars[$key] = $value;
        }
    }

    public function has($key) {
        return isset($this->vars[$key]);
    }

    public function clear($key = null) {
        if (is_null($key)) {
            $this->vars = array();
        }
        else {
            unset($this->vars[$key]);
        }
    }

    public function path($dir) {
        $this->loader->addDirectory($dir);
    }

    public function _start() {
        $dispatched = false;
        $self = $this;
        $request = $this->request();
        $response = $this->response();
        $router = $this->router();

        if (ob_get_length() > 0) {
            $response->write(ob_get_clean());
        }

        ob_start();

        $this->handleErrors($this->get('app.handle_errors'));

        $this->after('start', function() use ($self) {
            $self->stop();
        });

        $router->case_sensitive = $this->get('app.case_sensitive');

        while ($route = $router->route($request)) {
			
			if(!empty($route->group)){
				$group = $route->group;
				
				if(isset($group['middleware']))
				{
					$middleware = new Collection($group['middleware']);
					$request->setMiddleware($middleware);
					$continue = $this->_handleMiddleware($request);
				}
				
				$dispatched = true;
				if (!$continue) break;
			}
			
			if (is_callable( $route->callback) && is_array( $route->callback)) {
				
				list($class, $method) =  $route->callback;
				
				$instance = is_object($class);
				
				if($instance){
					$cls = get_class($class);
				}else{
					$cls = $class;
				}
				
				$arr = explode("\\", $cls);
				$classname = $arr[count($arr) - 1];
				
				if( !defined('CONTROLLER_NAME') ) define('CONTROLLER_NAME',  strtolower($classname));
				if( !defined('ACTION_NAME') ) define('ACTION_NAME',  $method);
			}
			
            $params = array_values($route->params);

            $continue = $this->dispatcher->execute(
                $route->callback,
                $params
            );

            $dispatched = true;

            if (!$continue) break;

            $router->next();

            $dispatched = false;
        }

        if (!$dispatched) {
            $this->notFound();
        }
    }

    public function _stop($code = 200) {
        $this->response()
            ->status($code)
            ->write(ob_get_clean())
            ->send();
    }

    public function _halt($code = 200, $message = '') {
        $this->response()
            ->status($code)
            ->write($message)
            ->send();
    }

    public function _error($e) {
        $msg = sprintf('<h1>500 Internal Server Error</h1>'.
            '<h3>%s (%s)</h3>'.
            '<pre>%s</pre>',
            $e->getMessage(),
            $e->getCode(),
            $e->getTraceAsString()
        );

        try {
            $this->response(false)
                ->status(500)
                ->write($msg)
                ->send();
        }
        catch (\Throwable $t) {
            exit($msg);
        }
        catch (\Exception $ex) {
            exit($msg);
        }
    }

    public function _notFound() {
        $this->response(false)
            ->status(404)
            ->write(
                '<h1>404 Not Found</h1>'.
                '<h3>The page you have requested could not be found.</h3>'.
                str_repeat(' ', 512)
            )
            ->send();
    }

    public function _route($pattern, $callback, $pass_route = false, $group = array()) {
        $this->router()->map($pattern, $callback, $pass_route, $group);
    }

    public function _redirect($url, $code = 303) {
        $base = $this->get('app.base_url');

        if ($base === null) {
            $base = $this->request()->base;
        }

        if ($base != '/' && strpos($url, '://') === false) {
            $url = preg_replace('#/+#', '/', $base.'/'.$url);
        }

        $this->response(false)
            ->status($code)
            ->header('Location', $url)
            ->write($url)
            ->send();
    }

    public function _json($data, $code = 200, $encode = true, $charset = 'utf-8') {
        $json = ($encode) ? json_encode($data) : $data;

        $this->response()
            ->status($code)
            ->header('Content-Type', 'application/json; charset='.$charset)
            ->write($json)
            ->send();
    }
	
    public function _jsonp($data, $param = 'jsonp', $code = 200, $encode = true, $charset = 'utf-8') {
        $json = ($encode) ? json_encode($data) : $data;

        $callback = $this->request()->query[$param];

        $this->response()
            ->status($code)
            ->header('Content-Type', 'application/javascript; charset='.$charset)
            ->write($callback.'('.$json.');')
            ->send();
    }

    public function _etag($id, $type = 'strong') {
        $id = (($type === 'weak') ? 'W/' : '').$id;

        $this->response()->header('ETag', $id);

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
            $_SERVER['HTTP_IF_NONE_MATCH'] === $id) {
            $this->halt(304);
        }
    }

    public function _lastModified($time) {
        $this->response()->header('Last-Modified', gmdate('D, d M Y H:i:s \G\M\T', $time));

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
            strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) === $time) {
            $this->halt(304);
        }
    }
	
	private function _loadRouter($dir){
		$files = scandir($dir);
		if(!empty($files)) foreach($files as $file){
			if($file == "." || $file == ".." ) continue;
			$name = "app\controller\\".basename($file, ".php");
			if (class_exists($name)) {
				$class = new \ReflectionClass($name);
				$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
				if(!empty($methods)) foreach($methods as $method){
					$doc  = $method->getDocComment();
					if(empty($doc)) continue;
					
					preg_match_all('/@router(.*?)\n/', $doc , $matchs);
					
					$router = trim($matchs[1][0]);
					if($router == '') continue;
					
					preg_match_all('/@middleware(.*?)\n/', $doc , $matchs);
					$middlewareArr = array();
					if(!empty($matchs[1][0])){
						$middleware = trim($matchs[1][0]);
						$middlewareArr = array("middleware"=>explode(":", $middleware));
					}
					
					if($method->isStatic()){
						$callback = array($name, $method->getName());
					}else{
						$callback = array($class->newInstanceArgs(array()), $method->getName());
					}
					
					$this->_route($router, $callback, false, $middlewareArr);
				}
				
			}else{
				 throw new \Exception("Class $name does not exist.");
			}
		}
	}
	
	private function _handleMiddleware($request) {
		$middleware = $request->middleware->current();
		
		$name = $this->middleware($middleware);
		
		$class = new \ReflectionClass($name);
		$callback = array($class->newInstanceArgs(array()), "handle");
		$closure = function($request){
			$request->middleware->next();
			if(!$request->middleware->valid()) return true;
			
			return $this->_handleMiddleware($request);
		};
		
		$params = array($request, $closure);
		
		return $this->dispatcher->execute(
			$callback,
			$params
		);
	}
	
	private function _loadConfig($path){
		if(!file_exists($path)) return;
		
		$config = require($path);
		if(!empty($config)) foreach($config as $key => $val){
			$this->set($key, $val);
		}
	}
	
}
