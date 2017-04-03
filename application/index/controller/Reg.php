<?php
/**
 * author      : Yxhsea.
 * email       : Yxhsea@foxmail.com
 * createTime  : 2017/4/3 22:49
 * description :
 */
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;

/**
 * 注册控制器
 */
class Reg extends Common{
    //异步检测用户名
    public function ajax_username(){
        if(!IS_AJAX){
            $this->error('页面不存在');
        }
        $username = Request::instance()->post('username');

        if(db::name('user')->where(array('username'=>$username))->field('uid')->find()){
            echo 0;
        }else{
            echo 1;
        }
    }

    //验证码
    public function code(){
        $config = array(
            'fontSize' => 14,
            'length' => 4,
            'imageW' => 99,
            'imageH' => 35,
            'useNoise' => false
        );
        $verify = new \Think\Verify($config);
        $verify->entry();
    }

    //判断验证码
    public function ajax_code(){
        if(!IS_AJAX){
            $this->error('页面不存在');
        }
        $verify = new \Think\Verify();
        $code = Request::instance()->post('verify');

        if(!$verify->check($code)){
            echo 0;
        }else{
            echo 1;
        }
    }

    //注册
    public function register(){
        if(!IS_POST){
            $this->error('页面不存在');
        }
        $data = array(
            'username' => Request::instance()->post('username'),
            'passwd'   => md5(Request::instance()->post('pwd')),
            'restime'  => time()
        );
        if(db::name('user')->update($data)){
            $this->success('注册成功');
        }else{
            $this->error('注册失败');
        }
    }
}