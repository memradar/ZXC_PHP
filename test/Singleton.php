<?php

trait Singleton
{
    static private $instance = null;

//    protected function init($params)
//    {
//        $stop = $params;
//    }

    private function __construct($p)
    {
        self::init($p);
        /* ... @return Singleton */
    }  // Защищаем от создания через new Singleton

    private function __clone()
    { /* ... @return Singleton */
    }  // Защищаем от создания через клонирование

    private function __wakeup()
    { /* ... @return Singleton */
    }  // Защищаем от создания через unserialize

    static public function getInstance($p = null)
    {
        return
            self::$instance === null
                ? self::$instance = new static($p)//new self()
                : self::$instance;
    }
}
