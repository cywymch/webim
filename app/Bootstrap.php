<?php

class Bootstrap extends Yaf\Bootstrap_Abstract
{

    public function _initConfig(Yaf\Dispatcher $dispatcher)
    {
        $config = Yaf\Application::app()->getConfig();
        Yaf\Registry::set("config", $config);
        //$dispatcher->getInstance()->disableView();
    }

    public function _initPlugin(Yaf\Dispatcher $dispatcher)
    {}
}

