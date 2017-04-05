<?php
/**
 * author      : Yxhsea.
 * email       : Yxhsea@foxmail.com
 * createTime  : 2017/3/26 15:22
 * description :
 */
namespace app\index\controller;

use \think\Controller;
use \think\Db;
use \think\Request;

//公共控制器
class Common extends Controller{

    public function assign_data(){
        //顶级分类
        $this->top_cate();

        //右侧用户信息
        $this->right_Info();

        //本日回答最多的人
        $this->eve_star();

        //历史回答最多的人
        $this->his_star();

        //助人光荣榜
        $this->helper();
    }

    //顶级分类
    public function top_cate(){
        //顶级分类
        $topcate = db::name('category')->where(['pid'=>0])->select();
        //$topcate = M('category')->where(array('pid'=>0))->select();
        $this->assign('topcate',$topcate);

        //总的提问数
        $askNum = db::name('ask')->count();
        //$askNum = M('ask')->count();
        $this->assign('askNum',$askNum);
    }

    //获得父级分类
    public function father_cate($arr,$pid){
        $array = array();
        foreach ($arr as $v) {
            if($v['cid'] == $pid){
                $array[] = $v;
                $array = array_merge($array,$this->father_cate($arr,$v['pid']));
            }
        }
        return $array;
    }

    //经验转换为等级
    public function exp_to_level($user){
        $exp = $user['exp'];
        for($i = 0;$i<21;$i++){
            if($exp <= Config('LV'.$i)){
                return $i;
            }
        }

        if($exp > Config('LV20')){
            return 20;
        }
    }

    //头像处理
    public function face($user){
        if(!empty($user['face'])){
            return $user['face'];
        }
        return 'Home/Images/noface.gif';
    }

    //采纳率换算
    public function ratio($user){
        if(!empty($user) && !empty($user['answer'])){
            $num = round($user['uaccept'] / $user['answer'],4);
            $ratio = $num * 100;
        }else{
            $ratio = 0;
        }
        return $ratio;
    }

    //右侧用户信息
    public function right_info(){
        $uid = Request::instance()->session('uid');
        //$uid = I('session.uid');
        $userInfo = '';
        if($uid){
            $userInfo = db::name('user')->where(['uid'=>$uid])->find();
            //$userInfo = M('user')->where(array('uid'=>$uid))->find();
            $userInfo['uaccept'] = $userInfo['accept'];
            $userInfo['face'] = $this->face($userInfo);
            $userInfo['ratio'] = $this->ratio($userInfo);
            $userInfo['lv'] = $this->exp_to_level($userInfo);
        }
        $this->assign('userInfo',$userInfo);
    }

    //本日回答最多的人
    public function eve_star(){
        $zero = strtotime(date('Y-m-d'));
        $eveStar = Db::table('wd_answer')
                   ->alias('a')
                   ->join('wd_user u','a.uid = u.uid')
                   ->where('a.time','>',$zero)
                   ->group('a.uid')
                   ->order('count(a.uid) desc')
                   ->find();
        //$eveStar = D('answer')->relation('User')->where(array('time'=>array('gt',$zero)))->group('uid')->order('count(uid) desc')->find();
        if(!empty($eveStar)){
            $eveStar = db::name('user')->where(['uid'=>$eveStar['uid']])->find();
            //$eveStar = M('user')->where(array('uid'=>$eveStar['uid']))->find();
            $eveStar['uaccept'] = $eveStar['accept'];
            $eveStar['face'] = $this->face($eveStar);
            $eveStar['ratio'] = $this->ratio($eveStar);
            $eveStar['lv'] = $this->exp_to_level($eveStar);
        }
        $this->assign('eveStar',$eveStar);
    }

    //历史回答最多的人
    public function his_star(){
        $hisStar = db::name('user')->order('answer desc')->find();
        //$hisStar = M('user')->order('answer desc')->find();
        if(!empty($hisStar)){
            $hisStar['uaccept'] = $hisStar['accept'];
            $hisStar['face'] = $this->face($hisStar);
            $hisStar['ratio'] = $this->ratio($hisStar);
            $hisStar['lv'] = $this->exp_to_level($hisStar);
        }
        $this->assign('hisStar',$hisStar);
    }

    //助人光荣榜
    public function helper(){
        $helper = db::name('user')->order('accept desc')->limit(5)->select();
        //$helper = M('user')->order('accept desc')->limit(5)->select();
        foreach ($helper as $key => $value) {
            $helper[$key]['uaccept'] = $value['accept'];
            $helper[$key]['face'] = $this->face($value);
            $helper[$key]['ratio'] = $this->ratio($helper[$key]);
            $helper[$key]['lv'] = $this->exp_to_level($value);
        }
        $this->assign('helper',$helper);
    }
}