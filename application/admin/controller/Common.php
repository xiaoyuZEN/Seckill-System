<?php

/**
 * 后台公共文件 
 * @file   Common.php  
 * @date   2016-8-24 18:28:34 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace app\admin\controller;

use think\Controller;
use think\Loader;

class Common extends Controller {

    public function _initialize() {
        // session失效，根据cookie重构session
        if (!session('valid'))
        {
            if (!cookie('valid')){
                $this->error('请登陆', 'login/index', '', 0);
            }
            else{
                $lastlogin = db('user')->field('token, lastlogin')->where('uid', cookie('uid'))->find();
                if ((cookie('token') != $lastlogin['token']) || (cookie('time') != $lastlogin['lastlogin']) || (cookie('time') + 604800 < time())) {
                    $this->error('请登陆', 'login/index', '', 0);
                }
                else {
                    $info = db('urlist')->field('rid')->where('uid', cookie('uid'))->find();
                    session('uid', cookie('uid'));
                    session('rid', $info['rid']);
                }
            }
        }
        // session未失效，直接使用session
        $rid = session('rid');
        $uid = session('uid');
        if (!$this->_checkAuthor()) {
            $this->error('该页面不存在');
        }
        $this->_addLog();
        $data = db('nodelist')->alias('a')
            ->join('rnlist b','a.nid = b.nid')
            ->field('a.name, a.url')
            ->where('b.rid', session('rid'))
            ->select();
        $this->assign("nodelist", $data);
        $cur_role = db('rolelist')->field('rname')->where('rid', $rid)->find();
        $this->assign("cur_role", $cur_role);
        $cur_name = db('user')->field('username')->where('uid', $uid)->find();
        $this->assign("cur_name", $cur_name);
        $cur_reg = db('expert')->field('leftnum, totalnum')->where('eid', $uid)->find();
        $this->assign("cur_reg", $cur_reg);
        if ($rid == 1) 
        {
            $AllSta = db('expert')->field('ename, dname, leftnum, totalnum, status')->where(1)->select();
            $this->assign("AllSta", $AllSta);
        }
        return $this->fetch();
    }

    private function _checkAuthor(){
        $c = strtolower(request()->controller());
        $a = strtolower(request()->action());
        $rid = session('rid');
        if($rid == 0){
            if($a=='allstatisyic' || $a=='reserve' || $a=='cancelreserve'){
                return false;
            }
        }
        else if($rid == 1){
            if($a=='ownstatistics' || $a=='modifyown'){
                return false;
            }
        }
        return true;
    }

    private function _addLog() {

        $data = array();
        $data['querystring'] = request()->query()?'?'.request()->query():'';
        $data['m'] = request()->module();
        $data['c'] = request()->controller();
        $data['a'] = request()->action();
        $data['uid'] = session('uid');
        $data['ip'] = ip2long(request()->ip());
	    $data['time'] = time();
        $arr = array('Index/index','Log/index','Menu/index');
        if (!in_array($data['c'].'/'.$data['a'], $arr)) {
            db('admin_log')->insert($data);
        } 
    }

}