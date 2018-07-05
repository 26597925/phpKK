<?php

/**
 * Memcache缓存驱动
 */

namespace framework\core\cache;

class MemcacheDriver implements CacheInterface{
	protected $mmc = NULL;
    protected $group = ''; 
    protected $ver = 0;
	
    public function __construct($config = array()) {
		$this->mmc = new Memcache;
		
		if( empty($config) ) {
			$config['app.mem_server'] = array(array('127.0.0.1', 11211));
			$config['app.mem_group'] = 3;
		}
		
		foreach($config['app.mem_server'] as $v) {
			call_user_func_array(array($this->mmc, 'addServer'), $v);
		}
		
		if( isset($config['app.mem_group']) ){
			$this->group = $config['app.mem_group'];
		}
		$this->ver = intval( $this->mmc->get($this->group.'_ver') );
    }

    public function get($key) {
		return $this->mmc->get($this->group.'_'.$this->ver.'_'.$key);
    }
	
    public function set($key, $value, $expire = 1800) {
		return $this->mmc->set($this->group.'_'.$this->ver.'_'.$key, $value, 0, $expire);
    }
	
	public function inc($key, $value = 1) {
		 return $this->mmc->increment($this->group.'_'.$this->ver.'_'.$key, $value);
    }
	
	public function des($key, $value = 1) {
		 return $this->mmc->decrement($this->group.'_'.$this->ver.'_'.$key, $value);
    }
	
	public function del($key) {
		return $this->mmc->delete($this->group.'_'.$this->ver.'_'.$key);
	}
	
    public function clear() {
        return  $this->mmc->set($this->group.'_ver', $this->ver+1); 
    }	
}