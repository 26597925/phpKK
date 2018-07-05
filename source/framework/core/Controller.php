<?php
namespace framework\core;

use framework\App;

class Controller
{
	protected function model($name){
		return App::model($name);
	}
	
	public function stop($code = 200){
		return App::stop($code);
	}
	
	public function halt($code = 200, $message = ''){
		return App::halt($code, $message);
	}
	
	public function error($e) {
		return App::error($e);
	}
	
	public function notFound(){
		return App::notFound();
	}
	
	public function redirect($url, $code = 303) {
		return App::redirect($url, $code);
	}
	
	public function json($data, $code = 200, $encode = true, $charset = 'utf-8') {
		return App::json($data, $code, $encode, $charset);
	}
	
	public function jsonp($data, $param = 'jsonp', $code = 200, $encode = true, $charset = 'utf-8') {
		return App::jsonp($data, $param, $code, $encode, $charset);
	}
	
	public function etag($id, $type = 'strong') {
		return App::etag($id, $type);
	}
	
	public function lastModified($time) {
		return App::lastModified($time);
	}
}
