<?php
namespace app\model;

use framework\core\Model;

class Activity extends Model{
	public function loadList(){
		return $this->cache()->query("select * from db");
	}
}
?>