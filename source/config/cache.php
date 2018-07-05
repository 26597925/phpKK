<?php
return array(
	"app.tpl" => array(
		"app.tpl_path" => APP_PATH,
		"app.tpl_suffix" => ".html",
		"app.tpl_cache" => "app.tpl_cache",
		"app.tpl_depr" => "_"
		),
	'app.cache'=>array(
		'app.tpl_cache' => array(
			'app.cache_type' => 'FileCache',
			'app.cache_path' => CACHE_PATH,
			'app.group' => 'tpl',
			'app.hash_deep' => 1,
		),
		'app.db_cache' => array(
			'app.cache_type' => 'FileCache',
			'app.cache_path' => CACHE_PATH,
			'app.group' => 'db',
			'app.hash_deep' => 2,
		),
		'app.mem_cache' => array(
			'app.mem_server' => array(array('127.0.0.1', 11211)),
			'app.mem_group' => 3
		),
	),
	'app.storage'=>array(
		'app.default'=>array('app.storage_type'=>'File'),
	),			
);
