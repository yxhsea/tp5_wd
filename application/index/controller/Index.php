<?php
namespace app\index\controller;

use \think\Controller;
use \think\Db;
use \think\Request;

//前台控制器
class Index extends Common{
    public function index(){
        //调用公共的部分
        $this->assign_data();

        $allcate = db::name('category')->where(['pid'=>0])->select();
        //$allcate = M('category')->where(array('pid'=>0))->select();

        foreach ($allcate as $k => $v) {
            $allcate[$k]['down'] = db::name('category')->where(['pid'=>$v['cid']])->select();
            //$allcate[$k]['down'] = M('category')->where(array('pid'=>$v['cid']))->select();
        }
        $this->assign('allcate',$allcate);

        //未解决低悬赏
        $where = array(
            'solve'  => 0,
            'reward' => array('elt',20)
        );
        $field = 'reward,content,answer,asid,cid';
        $askDb = db::name('ask');
        //$askDb = M('ask');
        $noSolvel = $askDb->where($where)->order('time desc')->field($field)->limit(5)->select();
        //$noSolvel = $askDb->where($where)->order('time desc')->field($field)->limit(5)->select();
        $this->assign('noSolvel',$noSolvel);

        //未解决高分悬赏
        $where['reward'] = array('gt',20);
        $noSolvelH = $askDb->where($where)->order('time desc')->field($field)->limit(5)->select();
        $this->assign('noSolvelH',$noSolvelH);

        return $this->fetch();
        //$this->display();
    }

    //搜索
    public function search(){
        $searcon = Request::instance()->post('searcon');
        //$searcon = I('post.searcon');
        $searcon = '%'.$searcon.'%';
        $sql = "select 
                    wd_answer.content,
                    wd_answer.asid,
                    wd_ask.time,
                    wd_ask.answer,
                    wd_ask.content as acontent,
                    wd_category.cid,
                    wd_category.title
                from 
                    wd_answer inner join wd_ask 
                    on wd_answer.asid = wd_ask.asid 
                    inner join wd_category 
                    on wd_ask.cid = wd_category.cid
                where 
                    wd_ask.content like '$searcon'
                order by
                    wd_ask.time desc
                limit 
                    10";
        $info = db::query($sql);
        //$info = M()->query($sql);
        $this->assign('info',$info);

        return $this->fetch();
        //$this->display();
    }
}