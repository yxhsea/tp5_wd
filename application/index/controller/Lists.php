<?php
/**
 * author      : Yxhsea.
 * email       : Yxhsea@foxmail.com
 * createTime  : 2017/3/27 14:26
 * description :
 */
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;

//分类页控制器
class Lists extends Common{
    public function index(){
        //调用公共的部分
        $this->assign_data();

        //获得父级分类
        $cid = Request::instance()->get('cid');
        //$cid = I('get.cid');

        $cate = db::name('category')->select();
        //$cate = M('category')->select();

        $this->assign('fathercate',array_reverse($this->father_cate($cate,$cid)));
        $this->assign('count',count(array_reverse($this->father_cate($cate,$cid))) - 1);

        //查找子分类
        $sonCate = db::name('category')->where(['pid'=>$cid])->select();
        //$sonCate = M('category')->where(array('pid'=>$cid))->select();
        if(empty($sonCate)){
            $pid = db::name('category')->where(['cid'=>$cid])->field('pid')->find();
            //$pid = M('category')->where(array('cid'=>$cid))->getField('pid');
            $cid = $pid;
            $sonCate = db::name('category')->where(['pid'=>$pid])->select();
            //$sonCate = M('category')->where(array('pid'=>$pid))->select();
        }
        $this->assign('sonCate',$sonCate);


        $where = Request::instance()->get('where');
        //$where = I('get.where');
        if(isset($where) && $where < 4){
            $condition = $where;
        }else{
            $condition = 0;
        }
        switch ($condition) {
            case '0':
                $where = array('a.solve'=>0,'a.reward'=>array('elt',20));
                break;
            case '1':
                $where = array('a.solve'=>1);
                break;
            case '2':
                $where = array('a.solve'=>0,'a.reward'=>array('gt',20));
                break;
            case '3':
                $where = array('a.solve'=>0,'a.answer'=>0);
                break;
        }
        if($cid != 0){
            $where['cid'] = $cid;
        }
        //$field = 'reward,content,answer,time,asid,wd_ask.cid';
        $Cask = db::name('ask')->alias('a')->join('wd_category c','a.cid = c.cid')->where($where)->select();
        //$Cask = D('ask')->relation('Category')->where($where)->select();

        //数据分页
        //$count = count($Cask);
        //$Page = new \Think\Page($count,1);
        //$show = $Page->show();
        $ask = db::name('ask')->alias('a')->join('wd_category c','a.cid = c.cid')->where($where)->order('time desc')->paginate();
        //$ask = D('ask')->relation('Category')->where($where)->order('time desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        // 获取分页显示
        $show = $ask->render();

        $this->assign('ask',$ask);
        $this->assign('page',$show);

        return $this->fetch();
        //$this->display();
    }
}