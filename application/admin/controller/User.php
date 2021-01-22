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


class User extends Common {

    public function userlist() {
        $lists = db('user')->select();
        //var_dump($lists);
        $this->assign("lists", $lists);
        return $this->fetch();  
    }

    public function student() {
        return $this->fetch();  
    }

}
