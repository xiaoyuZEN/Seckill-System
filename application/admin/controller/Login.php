<?php
namespace app\admin\controller;

use think\Controller;
use think\Loader;

class Login extends Controller {
    //登录页面
    public function index() {
        return $this->fetch();
    }

    public function test() {
        $password = '123456';
        $key = 'seckillzxy';
        $password = md5(md5($password)+$key);
        $data = [
            'uid' => 0,
            'username' => '张三',
            'password'=>$password,
        ];
        $res1 = db('user')->insert($data);
    }
    //登录逻辑处理
    public function dologin() {
        $username = input('post.username');
        $password = input('post.password');
        $key = 'seckillzxy';

        $info = db('user')->field('uid,username,password')->where('username', $username)->where('password', md5(md5($password)+$key))->find();

        if (!$info) {
            $this->error('用户或密码不正确，请重新登录');
        }

        if (md5(md5($password)+$key) != $info['password']) {
            $this->error('用户或密码不正确，请重新登录');
        } 
        else {
            session('uid', $info['uid']);
            $info2 = db('urlist')->where('uid', $info['uid'])->find();
            session('rid', $info2['rid']);
            session('valid', 1);
            $time = time();
            $token = md5($time+$info['uid']);
            db('user')->where('username', $username)->update(['token' => $token, 'lastlogin' => $time]);
            cookie("Verification",session_id(),time()+604800);
            cookie("token",$token,time()+604800);
            cookie("valid", 1, time()+604800);
            cookie("uid", $info['uid'], time()+604800);
            $this->success('登入成功', 'main/index');
        }
    }

    public function dochange(){
        $username = input('post.username');
        $oldp = input('post.oldpassword');
        $newp1 = input('post.newpassword1');
        $newp2 = input('post.newpassword2');
        $key = 'seckillzxy';

        if ($newp1 != $newp2) {
            $this->error('新密码两次输入不统一');
        }

        $info = db('user')->field('password')->where('username', $username)->where('password', md5(md5($oldp)+$key))->find();

        if (!$info) {
            $this->error('用户或原密码不正确，请重新输入');
        } 
        else {
            $newp = md5($newp1);
            $res = db('user')->where('uid',session('uid'))->update(['password'=>md5(md5($newp)+$key)]);
            $this->success('修改密码成功，请重新登录', 'login/index');
        }
    }

    public function doown(){
        $leftnum = input('post.leftnum');
        $uid = session('uid');
        $info = db('expert')->field('leftnum')->where('eid', $uid)->find();

        if ($info['leftnum'] > $leftnum) {
            $this->error('非法修改，修改后余号数不可小于当前余号');
        }
        else {
            $totalnum = ($leftnum - $info['leftnum']) + $info['totalnum'];
            $res = db('expert')->where('eid',$uid)->update(['leftnum'=>$leftnum, 'totalnum'=>$totalnum]);
            $this->success('修改余号成功', 'Main/ownstatistics');
        }
    }

    public function doreserve(){
        $pid = input('post.pid');
        $pname = input('post.pname');
        $ename = input('post.ename');

        $info1 = db('patient')->where('pid', $pid)->where('pname', $pname)->find();
        $info2 = db('expert')->field('leftnum')->where('ename', $ename)->find();

        if (!$info1){
            $this->error('病患名或医保号错误，请重新输入');
        }
        else if (!$info2){
            $this->error('专家不存在，请重新输入');
        }
        else if ($info2['leftnum'] == 0){
            $this->error('专家已无余号，挂号失败');
        }
        else {
            $info3 = db('reserve')->where('pid', $pid)->where('pname', $pname)->where('ename', $ename)->where('valid', 1)->find();
            if (!$info3){
                $data = [
                    'pid' => $pid,
                    'pname' => $pname,
                    'ename'=>$ename,
                    'valid'=> 1,
                ];
                $res1 = db('reserve')->insert($data);
                $leftnum = $info2['leftnum'] - 1;
                $res2 = db('expert')->where('ename',$ename)->update(['leftnum'=>$leftnum]);
                $this->success('挂号成功', 'Main/allstatisyic');
            }
            else{
                $this->error('操作非法，重复挂号');
            }
        }
    }

    public function docancel(){
        $pid = input('post.pid');
        $ename = input('post.ename');

        $info1 = db('patient')->where('pid', $pid)->find();
        $info2 = db('expert')->field('leftnum')->where('ename', $ename)->find();
        $info3 = db('reserve')->where('pid', $pid)->where('ename', $ename)->where('valid', 1)->find();

        if (!$info1){
            $this->error('医保号错误，请重新输入');
        }
        else if (!$info2){
            $this->error('专家不存在，请重新输入');
        }
        else if (!$info3){
            $this->error('无相关挂号记录，请重新确认');
        }
        else {
            $res1 = db('reserve')->where('pid', $pid)->where('ename', $ename)->where('valid', 1)->delete();
            $leftnum = $info2['leftnum'] + 1;
            $res2 = db('expert')->where('ename',$ename)->update(['leftnum'=>$leftnum]);
            $this->success('取消成功', 'Main/allstatisyic');
        }
    }

    //登出
    public function logout() {
        session('uid', null);
        session('rid', null);
        session('valid', null);
        cookie("Verification",null);
        cookie("token", null);
        cookie("valid", null);
        $this->success('退出成功', 'login/index');
    }
}
