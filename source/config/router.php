<?php
use framework\App;

//App::route('/greeting', array('app\controller\Greeting', 'hello'));
    App::route('/aa', function($route){
       print_r($route);
    }, true, array("middleware"=>array("Authenticated","Test")));

