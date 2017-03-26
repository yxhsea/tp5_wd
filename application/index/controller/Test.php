<?php
/**
 * author      : Yxhsea.
 * email       : Yxhsea@foxmail.com
 * createTime  : 2017/3/26 15:33
 * description :
 */
namespace app\index\controller;

use think\Controller;
use think\Db;

class Test extends Controller{
    public function index(){
        $res = db::name('user')->select();
        p($res);
    }
}