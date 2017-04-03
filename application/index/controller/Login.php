<?php
/**
 * author      : Yxhsea.
 * email       : Yxhsea@foxmail.com
 * createTime  : 2017/4/3 22:40
 * description :
 */
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;

//登录控制器
class Login extends Common{
    //ajax异步登录验证
    public function ajax_login(){
        if(!IS_AJAX){
            $this->error('页面不存在');
        }
        $username = Request::instance()->post('username');
        $pwd = md5(Request::instance()->post('pwd'));

        $passwd = db::name('user')->where(array('username'=>$username))->field('passwd')->find();
        //$passwd = M('user')->where(array('username'=>$username))->getField('passwd');

        if($pwd != $passwd){
            echo 0;
        }else{
            echo 1;
        }
    }

    //登录
    public function login(){
        if(!IS_POST){
            $this->error('页面不存在');
        }
        $username = Request::instance()->post('username');
        $pwd = md5(Request::instance()->post('pwd'));

        $user = db::name('user')->where(array('username'=>$username))->field('passwd,lock,uid')->find();
        //$user = M('user')->where(array('username'=>$username))->field('passwd,lock,uid')->find();

        if(empty($user)){
            $this->error('用户不存在');
        }
        if($pwd != $user['passwd']){
            $this->error('用户名或密码错误');
        }
        if($user['lock'] == 1){
            $this->error('用户被锁定,请联系管理员');
        }

        //增加经验
        $this->eve_exp($user['uid']);

        //登录信息
        $loginData = array(
            'logintime' => time(),
            'loginip'	=> get_client_ip()
        );

        db::name('user')->where(array('uid' => $user['uid']))->update($loginData);
        //M('user')->where(array('uid' => $user['uid']))->save($loginData);

        $auto = Request::instance()->post('auto');

        if($auto == 'on'){
            setcookie(session_name(),session_id(),time()+3600*24,'/');
        }
        Request::instance()->session('username',$username);
        //session('username',$username);

        Request::instance()->session('uid',$user['uid']);
        //session('uid',$user['uid']);

        $this->success('登录成功');
    }

    //每天登录增加经验
    private function eve_exp($uid){
        //获取当前时间戳
        $zero = strtotime(date('Y-m-d'));
        //获取用户上次登录时间
        $logintime = db::name('user')->where(array('uid'=>$uid))->field('logintime')->find();
        //$logintime = M('user')->where(array('uid'=>$uid))->getField('logintime');

        //时间比对
        if($logintime < $zero){
            db::name('user')->where(array('uid' => $uid))->setInc('exp',1);
            //M('user')->where(array('uid' => $uid))->setInc('exp',1);
        }
    }

    //用户退出
    public function out(){
        unset($_SESSION['username']);
        unset($_SESSION['uid']);
        $this->success('退出成功');
    }
}