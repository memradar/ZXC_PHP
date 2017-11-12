<?php

namespace ZXC\Logger;


use DateTime;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger implements LoggerInterface
{
    public $dateFormat = DateTime::RFC2822;
    public $filePath;
    public $template = "{date} {level} {message} {context}";

    public function __construct(array $attributes = [])
    {
        if (isset($attributes['filePath'])) {
            if (isset($attributes['root']) && $attributes['root'] === true) {
                $this->filePath = ZXC_ROOT .DIRECTORY_SEPARATOR. $attributes['filePath'];
            }else{
                $this->filePath = $attributes['filePath'];
            }

        }
        if (!file_exists($this->filePath)) {
            touch($this->filePath);
        }
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = [])
    {
        file_put_contents($this->filePath, trim(strtr($this->template, [
                '{date}' => $this->getDate(),
                '{level}' => $level,
                '{message}' => $message,
                '{context}' => $this->contextStringify($context),
            ])) . PHP_EOL, FILE_APPEND);
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