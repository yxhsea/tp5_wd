<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

//绑定Index
define('BIND_MODULE','index');

//定义请求数据的方法
define('IS_POST',strtolower($_SERVER["REQUEST_METHOD"]) == 'post');//判断是否是post方法
define('IS_GET',strtolower($_SERVER["REQUEST_METHOD"]) == 'get');//判断是否是get方法
define('IS_AJAX',isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');//判断是否是ajax请求

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
