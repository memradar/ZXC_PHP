<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 01/02/2018
 * Time: 21:35
 */

namespace ZXC;


class Exception extends \Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        //TODO
        parent::__construct($message, $code, $previous);
    }
}