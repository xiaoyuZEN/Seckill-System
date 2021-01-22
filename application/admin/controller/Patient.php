<?php
namespace app\admin\controller;

use think\Controller;
use think\Loader;

class Patient extends Controller {
    //登录页面
    public function index() {
        $AllSta = db('expert')->field('ename, dname, leftnum, totalnum, status')->where(1)->select();
        $this->assign("AllSta", $AllSta);
        return $this->fetch();
    }

    public function pdoreserve(){
        $pid = input('post.pid');
        $pname = input('post.pname');
        $ename = input('post.ename');
        $verifycode = input('post.verifycode');

        $info1 = db('patient')->where('pid', $pid)->where('pname', $pname)->find();
        $info2 = db('expert')->field('leftnum')->where('ename', $ename)->find();
        if(!captcha_check($verifycode)){
            $this->error('验证码错误，请重新输入！');
        }
        else if (!$info1){
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
                $this->success('挂号成功', 'Patient/index');
            }
            else{
                $this->error('操作非法，重复挂号');
            }
        }
    }
}
