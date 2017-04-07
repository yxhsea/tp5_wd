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
        if(!Request()->isAjax()){
            $this->error('页面不存在');
        }
        $username = Request::instance()->param('username');

        if(db::name('user')->where(array('username'=>$username))->field('uid')->find()){
            echo 0;
        }else{
            echo 1;
        }
    }

    //判断验证码
    public function ajax_code(){
        if(!Request()->isAjax()){
            $this->error('页面不存在');
        }

        $captcha = Request::instance()->param('verify');

        if(!captcha_check($captcha)){
            echo 0;
        }else{
            echo 1;
        }
    }

    //注册
    public function register(){
        if(!Request()->isPost()){
            $this->error('页面不存在');
        }
        $data = array(
            'username' => Request::instance()->param('username'),
            'passwd'   => md5(Request::instance()->param('pwd')),
            'restime'  => time()
        );
        if(db::name('user')->insert($data)){
            $this->success('注册成功');
        }else{
            $this->error('注册失败');
        }
    }
}