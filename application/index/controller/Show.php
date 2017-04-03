<?php
/**
 * author      : Yxhsea.
 * email       : Yxhsea@foxmail.com
 * createTime  : 2017/4/2 18:07
 * description :
 */
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;

//问题操作控制器
class Show extends Common{
    public function Index(){
        //调用公共的部分
        $this->assign_data();

        //接受cid
        $cid = Request::instance()->get('cid');
        //$cid = I('get.cid');
        $cate = db::name('category')->select();
        //$cate = M('category')->select();

        //获得父级分类
        $father = $this->father_cate($cate,$cid);
        $len = count($father) - 1;
        $this->assign('len',$len);
        $this->assign('fathercate',array_reverse($father));

        //获得用户名和经验值
        $asid = Request::instance()->get('asid');
        //$asid = I('get.asid');
        $ask = db::name('ask')->alias('a')->join('wd_user u','a.uid = u.uid')->where(array('asid'=>$asid))->find();
        //$ask = D('ask')->relation('User')->where(array('asid'=>$asid))->find();
        $this->assign('ask',$ask);

        //等级
        $this->assign('lv',$this->exp_to_level($ask));

        //显示当前答案
        $answerDb = db::name('answer')->where(array('asid'=>$asid))->select();
        //$answerDb = D('answer')->relation(true)->where(array('asid'=>$asid))->select();

        //显示回答问题的条数
        $count = count($answerDb);

        //数据分页显示
        $Page = new \Think\Page($count,5);
        $show = $Page->show();

        $answer = db::name('answer')->where(array('asid'=>$asid))->order('time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        //$answer = D('answer')->relation(true)->where(array('asid'=>$asid))->order('time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('count',$count);;
        $this->assign('answer',$answer);
        $this->assign('page',$show);

        //满意回答信息
        $answerOk = db::name('answer')->where(array('asid'=>$asid,'accept'=>1))->find();
        //$answerOk = D('answer')->relation(true)->where(array('asid'=>$asid,'accept'=>1))->find();
        $this->assign('answerOk',$answerOk);

        //回答者的等级
        $this->assign('alv',$this->exp_to_level($answerOk));

        //回答者头像
        $this->assign('faceOk',$this->face($answerOk));

        //采纳率
        $Aaccept = db::name('user')->where(array('uid'=>$answerOk['uid']))->getField('accept');
        //$Aaccept = M('user')->where(array('uid'=>$answerOk['uid']))->getField('accept');
        $answerOk['uaccept'] = $Aaccept;
        $this->assign('ratio',$this->ratio($answerOk));


        //相关问题
        $where = array(
            'solve' => 0,
            'cid'   => $cid,
            'asid'  => array('neq',$asid)
        );
        $alike = db::name('ask')->where($where)->order('time desc')->limit(5)->select();
        //$alike = M('ask')->where($where)->order('time desc')->limit(5)->select();
        $this->assign('alike',$alike);

        $this->display();
    }

    //回答问题
    public function answer(){
        if(!IS_POST){
            $this->error('页面不存在!');
        }
        //组合数据
        $data = array(
            'asid'    => Request::instance()->post('asid'),
            'uid'  	  => Request::instance()->session('uid'),
            'time' 	  => time(),
            'content' => Request::instance()->post('content')
        );
        db::name('answer')->insert($data);
        //M('answer')->add($data);

        //修改用户信息(金币,经验,回答数)
        $userDb = db::name('user');
        $userDb->where(array('uid'=>$data['uid']))->setInc('point',Config('GOLD_ANSWER'));
        $userDb->where(array('uid'=>$data['uid']))->setInc('exp',Config('LV_ANSWER'));
        $userDb->where(array('uid'=>$data['uid']))->setInc('answer',1);

        //回答数增加
        db::name('ask')->where(array('asid'=>$data['asid']))->setInc('answer',1);
        //M('ask')->where(array('asid'=>$data['asid']))->setInc('answer',1);

        $this->success('回答成功！');
    }

    //采纳回答
    public function accept(){
        $anid = Request::instance()->get('anid');
        $asid = Request::instance()->get('asid');
        $uid  = Request::instance()->get('uid');

        db::name('answer')->where(array('anid'=>$anid))->update(array('accept'=>1));
        //M('answer')->where(array('anid'=>$anid))->save(array('accept'=>1));

        db::name('ask')->where(array('asid'=>$asid))->update(array('solve'=>1));
        //M('ask')->where(array('asid'=>$asid))->save(array('solve'=>1));

        //增加提问者的经验，金币
        $askUid = db::name('ask')->where(array('asid'=>$asid))->field('uid')->find();
        //$askUid = M('ask')->where(array('asid'=>$asid))->getField('uid');

        db::name('user')->where(array('uid'=>$askUid))->setInc('exp',Config('GOLD_ACCEPT'));
        //M('user')->where(array('uid'=>$askUid))->setInc('exp',C('GOLD_ACCEPT'));

        db::name('user')->where(array('uid'=>$askUid))->setInc('point',Config('LV_ACCEPT'));
        //M('user')->where(array('uid'=>$askUid))->setInc('point',C('LV_ACCEPT'));

        //增加回答者的经验，金币，采纳数
        $reward = db::name('ask')->where(array('asid'=>$asid))->field('reward')->find();
        //$reward = M('ask')->where(array('asid'=>$asid))->getField('reward');

        db::name('user')->where(array('uid'=>$uid))->setInc('exp',Config('GOLD_ACCEPT'));
        //M('user')->where(array('uid'=>$uid))->setInc('exp',C('GOLD_ACCEPT'));

        db::name('user')->where(array('uid'=>$uid))->setInc('point',Config('LV_ACCEPT') + $reward);
        //M('user')->where(array('uid'=>$uid))->setInc('point',C('LV_ACCEPT') + $reward);

        db::name('user')->where(array('uid'=>$uid))->setInc('accept',1);
        //M('user')->where(array('uid'=>$uid))->setInc('accept',1);

        $this->success('回答采纳成功！');
    }
}