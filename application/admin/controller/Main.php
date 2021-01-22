<?php

/**
 *  登陆页
 * @file    
 * @date    
 * @author  
 * @version    
 */

namespace app\admin\controller;

use think\Request;
use think\Db;
use think\Controller;
use think\Loader;

class Main extends Common{
    public function index() {
        return $this->fetch(); 
    }
    public function ownstatistics() {
        return $this->fetch(); 
    }
    public function modifyown() {
        return $this->fetch(); 
    }

    public function allstatisyic() {
        return $this->fetch(); 
    }

    public function cancelreserve() {
        return $this->fetch(); 
    }
    public function reserve() {
        return $this->fetch(); 
    }
    
    //修改密码
    public function change(){
        return $this->fetch();
    }

}
