<?php
/**
 * author      : Yxhsea.
 * email       : Yxhsea@foxmail.com
 * createTime  : 2017/3/27 13:57
 * description :
 */
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;

//提问控制器
class Ask extends Common{
    public function ask(){
        //调用顶级分类
        $this->top_cate();

        //顶级分类
        $cate = db::name('category')->where(['pid'=>0])->select();
        //$cate = M('category')->where(array('pid'=>0))->select();
        $this->assign('cate',$cate);

        //获取用户金币数
        $uid = Request::instance()->session('uid');
        //$uid = I('session.uid');
        $point = db::name('user')->where(['uid'=>$uid])->field('point')->find();

        //$point = M('user')->where(array('uid'=>$uid))->getField('point');
        $this->assign('point',$point);

        return $this->fetch();
        //$this->display();
    }

    //异步调取子分类
    public function get_cate(){
        if(!IS_AJAX){
            $this->error('页面不存在');
        }
        $pid = Request::instance()->post('pid');
        //$pid = I('post.pid');
        $cate = db::name('category')->where(['pid'=>$pid])->select();
        //$cate = M('category')->where(array('pid' => $pid))->select();
        if($cate){
            echo json_encode($cate);
        }
    }

    //用户提问
    public function sub_ask(){
        if(!IS_POST){
            $this->error('页面不存在');
        }

        $data = array(
            'content' => Request::instance()->post('content'),
            'time'    => time(),
            'reward'  => Request::instance()->post('reward'),
            'uid'     => Request::instance()->session('uid'),
            'cid'     => Request::instance()->post('cid')
        );

        db::name('ask')->insert($data);
        //M('ask')->add($data);

        $db = db::name('user');
        //$db = M('user');

        //减少金币
        $db->where(['uid'=>$data['uid']])->setDec('point',$data['reward']);

        //增加提问数
        $db->where(['uid'=>$data['uid']])->setInc('ask',1);

        //增加经验值
        $db->where(['uid'=>$data['uid']])->setInc('exp',10);

        $this->success('提问成功!');
    }
}