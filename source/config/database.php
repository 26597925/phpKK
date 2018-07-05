<?php
return array(
	"app.default" => array(						
		'app.db_type' => 'mysqlPdo',
		'app.db_host' => 'localhost',
		'app.db_user' => 'root',
		'app.db_pwd'  => '',
		'app.db_port' => 3306,
		'app.db_name' => 'mysql',
		'app.db_charset' => 'utf8',
		'app.db_prefix' => '',
		'app.db_cache' => 'app.db_cache',						
		'app.db_slave' => array(),
		/* 
		'app.db_slave' => array(
							array(
									'app.db_host' => '127.0.0.1',
								),
							array(
									'app.db_host' => '127.0.0.2',
								),
						),
		*/							
	),		
);
