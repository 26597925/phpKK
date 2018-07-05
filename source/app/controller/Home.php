<?php
namespace app\controller;

use framework\core\Controller;
use framework\net\Request;

class Home extends Controller {

    /**
     * @router /
	 * @middleware Authenticated
     */

    public function index(Request $request){
       $data = $this->model("Activity")->loadList();
	   print_r($data);
    }

    /**
     * @router /Home/@live/@clid
     */

    public function test(Request $request, $live, $clid) {
        echo $live.$clid;
    }

    
}