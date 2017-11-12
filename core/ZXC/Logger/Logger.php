<?php

namespace ZXC\Logger;


use DateTime;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger implements LoggerInterface
{
    public $dateFormat = DateTime::RFC2822;

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = [])
    {

    }

    public function getDate()
    {
        return (new DateTime())->format($this->dateFormat);
    }

    public function contextStringify(array $context = [])
    {
        return !empty($context) ? json_encode($context) : null;
    }
}