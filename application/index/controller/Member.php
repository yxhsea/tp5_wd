<?php
/**
 * author      : Yxhsea.
 * email       : Yxhsea@foxmail.com
 * createTime  : 2017/3/27 14:58
 * description :
 */
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;

//个人中心控制器
class Member extends Common{

    //初始化,公共部分
    public function _initialize(){
        //顶级分类
        $this->top_cate();

        //调用左侧用户信息
        $this->_left_info();
    }

    //我的首页
    public function index(){
        //我的提问列表展示
        $uid = Request::instance()->get('uid');
        //$uid = I('get.uid');
        $askShow = db::name('ask')->alias('a')->join('wd_category c','a.cid = c.uid')->where(['uid'=>$uid])->order('time desc')->limit(10)->select();
        //$askShow = D('ask')->relation('Category')->where(array('uid'=>$uid))->order('time desc')->limit(10)->select();
        $this->assign('askShow',$askShow);

        //我的回答列表展示
        $sql = "select 
					wd_answer.content,
					wd_answer.time,
					wd_answer.asid,
					wd_ask.answer,
					wd_category.cid,
					wd_category.title
				from 
					wd_answer inner join wd_ask 
					on wd_answer.asid = wd_ask.asid 
					inner join wd_category 
					on wd_ask.cid = wd_category.cid
				where 
					wd_answer.uid = ".$uid."
				order by
					wd_answer.time desc
				limit 
					5";

        $answerShow = Db::query($sql);
        $this->assign('answerShow',$answerShow);

        return $this->fetch();
        //$this->display();
    }

    //用户左侧信息
    private function _left_info(){
        $uid = Request::instance()->get('uid');
        //$uid = I('get.uid');
        $leftInfo = db::name('user')->where(['uid'=>$uid])->find();
        //$leftInfo = M('user')->where(array('uid'=>$uid))->find();
        if(!empty($leftInfo)){
            $leftInfo['uaccept'] = $leftInfo['accept'];
            $leftInfo['face'] = $this->face($leftInfo);
            $leftInfo['ratio'] = $this->ratio($leftInfo);
            $leftInfo['lv'] = $this->exp_to_level($leftInfo);
            $this->assign('leftInfo',$leftInfo);
        }else{
            $this->error('用户不存在');
        }

        //第三人称
        $rank = isset(Request::instance()->session('uid')) && (Request::instance()->session('uid') == $uid) ? '我' : 'TA';
        //$rank = isset($_SESSION['uid']) && ($_SESSION['uid'] == $uid) ? '我' : 'TA';
        $this->assign('rank',$rank);
    }

    //我的提问
    public function my_ask(){
        $uid = Request::instance()->get('uid');
        //$uid = I('get.uid');
        $where = Request::instance()->get('where');
        //$where = I('get.where');

        if($where == 0 || $where == null){
            //待解决问题的条件
            $arr = array('uid'=>$uid,'solve'=>0);
        }elseif ($where == 1) {
            //已解决问题的条件
            $arr = array('uid'=>$uid,'solve'=>1);
        }
        //记录的条数
        $count = db::name('ask')->where($arr)->count();
        //$count = M('ask')->where($arr)->count();
        //数据分页显示
        //$Page = new \Think\Page($count,5);
        //$show = $Page->show();

        //待解决和已解决的问题
        $Ask = db::name('ask')->alias('a')->join('wd_category c','a.cid = c.cid')->where($arr)->order('time desc')->paginate(5);
        //$Ask = D('ask')->relation('Category')->where($arr)->order('time desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        // 获取分页显示
        $show = $Ask->render();

        $this->assign('Ask',$Ask);
        $this->assign('Page',$show);

        return $this->fetch();
        //$this->display();
    }

    //我的回答
    public function my_answer(){
        $uid = Request::instance()->get('uid');
        //$uid = I('get.uid');
        $where = Request::instance()->get('where');
        //$where = I('get.where');
        if($where == 1){
            $where = 'and wd_answer.accept = 1 ';
            $arr = array('uid'=>$uid,'accept'=>1);
        }else{
            $arr = array('uid'=>$uid);
        }
        //数据分页显示
        $count = db::name('answer')->where($arr)->count();
        //$count = M('answer')->where($arr)->count();
        $Page = new \Think\Page($count,5);
        $show = $Page->show();

        //我的回答列表展示
        $sql = "select 
					wd_answer.content,
					wd_answer.time,
					wd_answer.asid,
					wd_answer.uid,
					wd_ask.answer,
					wd_category.cid,
					wd_category.title
				from 
					wd_answer inner join wd_ask 
					on wd_answer.asid = wd_ask.asid 
					inner join wd_category 
					on wd_ask.cid = wd_category.cid
				where 
					wd_answer.uid = ".$uid." ".$where."
				order by
					wd_answer.time desc
				limit 
					".$Page->firstRow.','.$Page->listRows;
        $showAnswer = Db::query($sql);

        $this->assign('showAnswer',$showAnswer);
        $this->assign('Page',$show);

        return $this->fetch();
        //$this->display();
    }

    //我的等级
    public function my_level(){
        $uid = Request::instance()->get('uid');
        //$uid = I('get.uid');
        $user = db::name('user')->where(['uid'=>$uid])->find();
        //$user = M('user')->where(array('uid'=>$uid))->find();
        $level = $this->exp_to_level($user);
        $nextExp = Config('LV'.($level+1))-$user['exp'];
        $nextExp = ($nextExp < 0) ? 0 : $nextExp;
        $this->assign('exp',$user['exp']);
        $this->assign('level',$level);
        $this->assign('nextExp',$nextExp);

        //等级规则
        $levelExp = array();
        for($i = 0;$i < 21; $i++){
            $levelExp[$i] = Config('LV'.$i);
        }
        $this->assign('levelExp',$levelExp);

        return $this->fetch();
        //$this->display();
    }

    //我的金币
    public function my_gold(){
        $uid = Request::instance()->get('uid');
        //$uid = I('get.uid');
        $point = db::name('user')->where(['uid'=>$uid])->field('point')->find();
        //$point = M('user')->where(array('uid'=>$uid))->getField('point');
        $this->assign('point',$point);
        return $this->fetch();
        //$this->display();
    }

    //我的头像
    public function my_face(){
        $uid = Request::instance()->get('uid');
        //$uid = I('get.uid');
        if(IS_POST){
            $upload = new \Think\Upload();									// 实例化上传类
            $upload->maxSize = 3145728 ;									// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');				// 设置附件上传类型
            $upload->rootPath = './Public/Uploads/'; 						// 设置附件上传根目录
            $upload->savePath = ''; 										// 设置附件上传（子）目录

            // 上传文件
            $info = $upload->upload();

            if(!$info) {													// 	上传错误提示错误信息
                $this->error($upload->getError());
            }else{															// 上传成功
                $image = new \Think\Image();
                $smallpath = 'Uploads/'.$info['face']['savepath'].$info['face']['savename'];
                $path  = './Public/Uploads/'.$info['face']['savepath'].$info['face']['savename'];
                $image->open($path);
                // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                $image->thumb(180, 180)->save($path);

                $oldface = M('user')->where(array('uid'=>$uid))->getField('face');
                $oldpath = './Public/'.$oldface;
                if(is_file($oldpath)){
                    if(!unlink($oldpath)){
                        $this->error('你没有权限!');
                    }
                }
                $ok = M('user')->where(array('uid'=>$uid))->save(array('face'=>$smallpath));
                if($ok){
                    echo "<script type='text/javascript'>alert('头像上传成功！');</script>";
                }
            }
        }
        $user = db::name('user')->where(['uid'=>$uid])->find();
        //$user = M('user')->where(array('uid'=>$uid))->find();
        $this->assign('face',$this->face($user));
        return $this->fetch();
        //$this->display();
    }
}