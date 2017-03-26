<?php
/**
 * author      : Yxhsea.
 * email       : Yxhsea@foxmail.com
 * createTime  : 2017/3/26 14:21
 * description :
 */
//[定义后台入口文件]

//定义应用目录
define('APP_PATH',__DIR__.'/../application/');

//绑定admin模块
define('BIND_MODULE','admin');

//加载框架引导文件
require __DIR__.'/../thinkphp/start.php';