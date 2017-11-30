<?php

require_once 'Singleton.php';

class Foo
{



    use Singleton;

    private $bar = 0;

    public function incBar()
    {
        $this->bar++;
    }

    public function getBar()
    {
        return $this->bar;
    }

    protected function init($e)
    {
        $stop = $e;
    }
}
