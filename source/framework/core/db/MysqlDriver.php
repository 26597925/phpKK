<?php

/**
 * mysql数据库驱动
 */

namespace framework\core\db;
use framework\App;

class MysqlDriver implements DbInterface {
	protected $config =array();
	protected $writeLink = NULL;
	protected $readLink = NULL;
	protected $sqlMeta = array('sql'=>'', 'params'=>array(), 'link'=>NULL);
	
	public function __construct($config = array()) {
		$this->config = $config;
	}

	public function select($table, array $condition = array(), $field='*', $order=NULL, $limit=NULL){
		$field = !empty($field) ? $field : '*';
		$order = !empty($order) ? ' ORDER BY '.$order : '';
		$limit = !empty($limit) ? ' LIMIT '.$limit : '';
		$table = $this->_table($table);
		$condition = $this->_where($condition);
		return $this->query("SELECT {$field} FROM {$table} {$condition['_where']} {$order} {$limit}", $condition['_bindParams']);		
	}
	
	public function query($sql, array $params = array()) {
		$this->_bindParams( $sql, $params, $this->_getReadLink());
		
		App::before('dbQueryBegin', array($sql, $params));
		$query = mysql_query( $this->getSql(), $this->_getReadLink() );
		if($query){
			$data = array();
			while($row = mysql_fetch_array($query, MYSQL_ASSOC)){
				$data[] = $row;
			}
			App::after('dbQueryEnd', array($this->getSql(), $data));
			return $data;
		}

		$err = mysql_error();
		App::exception('dbException', array($this->getSql(), $err));
		throw new \Exception('Database SQL: "' . $this->getSql(). '". ErrorInfo: '. $err, 500);
	}
	
	public function execute($sql, array $params = array()) {
		$this->_bindParams( $sql, $params, $this->_getWriteLink());
		
		App::before('dbExecuteBegin', array($sql, $params));
		$query = mysql_query( $this->getSql(), $this->_getWriteLink() );
		if($query){
			$affectedRows = mysql_affected_rows( $this->_getWriteLink() );
			App::after('dbExecuteEnd', array($this->getSql(), $affectedRows));
			return $affectedRows;
		}
		
		$err = mysql_error();
		App::exception('dbException', array($this->getSql(), $err));
		throw new \Exception('Database SQL: "' . $this->getSql(). '". ErrorInfo: '. $err, 500);
	}
	
	public function insert($table, array $data) {
		$values = array();
		foreach($data as $k=>$v){
			$keys[] = "`{$k}`"; 
			$values[":{$k}"] = $v; 
			$marks[] = ":{$k}";
		}
		$table = $this->_table($table);
		$status = $this->execute("INSERT INTO {$table} (".implode(', ', $keys).") VALUES (".implode(', ', $marks).")", $values);
		$id = mysql_insert_id( $this->_getWriteLink() );
		if($id){
			return $id;
		}else{
			return $status;
		}
	}
	
	public function update($table, array $condition = array(), array $data = array()) {
		if( empty($condition) ) return false;
		$values = array();
		foreach ($data as $k=>$v){
			$keys[] = "`{$k}`=:__{$k}";
			$values[":__{$k}"] = $v;			
		}
		$table = $this->_table($table);
		$condition = $this->_where( $condition );
		return $this->execute("UPDATE {$table} SET ".implode(', ', $keys) . $condition['_where'], $condition['_bindParams'] + $values);
	}
	
	public function delete($table, array $condition = array()) {
		if( empty($condition) ) return false;
		$table = $this->_table($table);
		$condition = $this->_where( $condition );
		return $this->execute("DELETE FROM {$table} {$condition['_where']}", $condition['_bindParams']);
	}

	public function count($table, array $condition = array()) {
		$table = $this->_table($table);
		$condition = $this->_where( $condition );
		$count = $this->query("SELECT COUNT(*) AS __total FROM {$table} ".$condition['_where'], $condition['_bindParams']);
		return isset($count[0]['__total']) && $count[0]['__total'] ? $count[0]['__total'] : 0;
	}
	
	public function getFields($table) {
		$table = $this->_table($table);
		return  $this->query("SHOW FULL FIELDS FROM {$table}");
	}
	
	public function getSql() {
		$sql = $this->sqlMeta['sql'];
		$arr = $this->sqlMeta['params'];
		uksort($arr, function($a, $b){ return strlen($b)-strlen($a);} );
		foreach($arr as $k=>$v ){
			$sql = str_replace($k, "'" . mysql_real_escape_string($v, $this->sqlMeta['link']) . "'", $sql);
		}
		return $sql;
	}
	
	public function beginTransaction() {
		return $this->execute('SET AUTOCOMMIT=0');
	}
	
	public function commit() {
		return $this->execute('COMMIT');
	}
	
	public function rollBack() {
		return $this->execute('ROLLBACK');
	}
	
	protected function _bindParams($sql, array $params, $link=null) {
		$this->sqlMeta = array('sql'=>$sql, 'params'=>$params, 'link'=>$link);
	}

	protected function _table($table) {
		return (false===strpos($table, ' '))? "`{$table}`": $table;
	}
	
	protected function _where( array $condition ) {
		$result = array( '_where' => '', '_bindParams' => array() );	 		
		$sql = null;
		$sqlArr = array();
		$params = array();
		foreach( $condition as $k => $v ){
			if(!is_numeric($k)){
				if( false===strpos($k, ':') ){
					$k = str_replace('`', '', $k);				
					$key = ':__'.str_replace('.', '_', $k);
					$field = '`'.str_replace('.', '`.`', $k).'`';					
					$sqlArr[] = "{$field} = {$key}";
				}else{
					$key = $k;
				}
				$params[$key] = $v;
			}else{
				$sqlArr[] = $v;
			}
		}
		if(!$sql) $sql = implode(' AND ', $sqlArr);
		if($sql) $result['_where'] = " WHERE ". $sql;
		$result['_bindParams'] = $params;		
		return $result;
	}
					
	protected  function _connect($isMaster = true) {
		$dbArr = array();
		if( false==$isMaster && !empty($this->config['app.db_slave']) ) {	
			$master = $this->config;
			unset($master['app.db_slave']);
			foreach($this->config['app.db_slave'] as $k=>$v) {
				$dbArr[] = array_merge($master, $this->config['app.db_slave'][$k]);
			}
			shuffle($dbArr);
		} else {
			$dbArr[] = $this->config;
		}
		
		$link =null;
		foreach($dbArr as $db) {
			if( $link = @mysql_connect($db['app.db_host'] . ':' . $db['app.db_port'], $db['app.db_user'], $db['app.db_pwd']) ){
				break;
			}
		}
		
		if(!$link){
			throw new \Exception('connect database error :'.mysql_error(), 500);
		}

		$version = mysql_get_server_info($link);
		if($version > '4.1') {
			mysql_query("SET character_set_connection = " . $db['app.db_charset'] . ", character_set_results = " . $db['app.db_charset'] . ", character_set_client = binary", $link);		
				
			if($version > '5.0.1') {
				mysql_query("SET sql_mode = ''", $link);
			}
		}		
        mysql_select_db($db['app.db_name'], $link);
		mysql_query('set names utf8');
        return $link;
	}

    protected function _getReadLink() {
		if(!isset($this->readLink)) {
			try{
				$this->readLink = $this->_connect( false );
			}catch(Exception $e){
				$this->readLink = $this->_getWriteLink();
			}			
		}
		return $this->readLink;
    }
	
    protected function _getWriteLink() {
        if(!isset($this->writeLink)) {
            $this->writeLink = $this->_connect( true );
        }
		return $this->writeLink;
    }
	
	public function __destruct() {
		if($this->writeLink != NUll) {
			@mysql_close($this->writeLink);
		}
		if($this->readLink) {
			@mysql_close($this->readLink);
		}
	} 
}