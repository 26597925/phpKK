<?php

require 'source/framework/App.php';

use framework\App;

date_default_timezone_set('PRC');

if( !defined('EXPIRY_1') ) define('EXPIRY_1',  1200);
if( !defined('EXPIRY_2') ) define('EXPIRY_2',  1800);
if( !defined('EXPIRY_3') ) define('EXPIRY_3',  3600);
if( !defined('EXPIRY_4') ) define('EXPIRY_4',  3600 * 24);
if( !defined('EXPIRY_5') ) define('EXPIRY_5',  2400);
if( !defined('EXPIRY_6') ) define('EXPIRY_6',  3600*6);
if( !defined('EXPIRY_7') ) define('EXPIRY_7',  60*15);
if( !defined('EXPIRY_8') ) define('EXPIRY_8',  60*60*2);

if( !defined('__URL__') ) define('__URL__',  'http://ice.cn');

App::start();

