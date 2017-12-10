<?php
class Db
{

    private static $_instance = null;

    private function __construct()
    {}

    public static function getInstance()
    {
        $config = self::getConfig();
        $class = "Db\\" . ucfirst($config['type']);
        if (class_exists($class)) {
            self::$_instance = new $class($config);
        } else {
            throw new \Exception("不存在该驱动！");
        }

        return self::$_instance;
    }

    public static function getConfig(): array
    {
        $ini = new \Yaf\Config\Ini(APP_PATH . '/conf/app.ini', 'database');
        return ($ini->toArray())['database'];
    }

    
    function __destruct()
    {}
}

