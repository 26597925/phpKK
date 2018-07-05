<?php
namespace app\middleware;

use framework\App;
use framework\net\Request;
use Closure;
use framework\live\EncrypUtil;
use framework\util\Blowfish;

class Authenticated {
	
	public function handle(Request $request, Closure $next){
            return $next($request);
	}
}
