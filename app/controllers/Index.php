<?php

class IndexController extends Yaf\Controller_Abstract
{

    public function indexAction()
    {
        $redis = new Redis();
        $redis->connect('116.62.58.170', '6379');
        $redis->auth('chen123456');
        $data = $redis->keys("fd_*");
        foreach ($data as $key=>$vo){
            $re = $redis->get($vo);
            $result[$key]['fd'] = $re['fd'];
            $result[$key]['member_id'] = $re['member_id'];
            $result[$key]['nickname'] = $re['nickname'];
        }
//         var_dump($data);
//         var_dump(Yaf\Session::getInstance()->get());
    }
    
    public function loginAction()
    {
        if ($this->getRequest()->isPost()){
            $data = $_POST;
            $model = new Model();
            $where['mobile'] = $data['mobile'];
            $info = $model->table('member')->where($where)->find();
            if ($info['password'] == $data['password']){
                Yaf\Session::getInstance()->set("member_id", $info['id']);
                Yaf\Session::getInstance()->set('nickname', $info['nickname']);
                $this->redirect("index");
            }
        }
    }
}