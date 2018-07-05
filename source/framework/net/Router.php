<?php
namespace framework\net;

class Router {
 
    protected $routes = array();

    protected $index = 0;

    public $case_sensitive = false;

    public function getRoutes() {
        return $this->routes;
    }

    public function clear() {
        $this->routes = array();
    }

    public function map($pattern, $callback, $pass_route = false, $group = array()) {
        $url = $pattern;
        $methods = array('*');

        if (strpos($pattern, ' ') !== false) {
            list($method, $url) = explode(' ', trim($pattern), 2);

            $methods = explode('|', $method);
        }

        $this->routes[] = new Route($url, $callback, $methods, $pass_route, $group);
    }

    public function route(Request $request) {
        while ($route = $this->current()) {
			$route->setRequest($request);
            if ($route !== false && $route->matchMethod($request->method) && $route->matchUrl($request->url, $this->case_sensitive)) {
                return $route;
            }
            $this->next();
        }

        return false;
    }

    public function current() {
        return isset($this->routes[$this->index]) ? $this->routes[$this->index] : false;
    }

    public function next() {
        $this->index++;
    }

    public  function reset() {
        $this->index = 0;
    }
}

