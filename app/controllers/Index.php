<?php

class IndexController extends Yaf\Controller_Abstract
{

    public function indexAction()
    {

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