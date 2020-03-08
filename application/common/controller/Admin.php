<?php


namespace app\common\controller;

use think\Controller;
use think\Request;
use think\Response;
class  Admin extends Controller{



	public $param;
    public function _initialize()
    {
        parent::_initialize();
        /*防止跨域*/
        header('Access-Control-Allow-Origin: *');    
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authKey, sessionId, Authorization");
        if (strtoupper(Request::instance()->method()) == "OPTIONS") {
            //return Response::create()->send();
            exit();
        }
        $param =  Request::instance()->param();            
        $this->param = $param;
    }

    public function object_array($array) 
    {  
        if (is_object($array)) {  
            $array = (array)$array;  
        } 
        if (is_array($array)) {  
            foreach ($array as $key=>$value) {  
                $array[$key] = $this->object_array($value);  
            }  
        }  
        return $array;  
    }



}