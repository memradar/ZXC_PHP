<?php

namespace ZXC\Mod;


use DateTime;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger implements LoggerInterface
{
    public $dateFormat = DateTime::RFC2822;
    public $filePath;
    public $template = "{date} {level} {message} {context}";
    private $level;

    public function __construct(array $attributes = [])
    {
        $this->initialize($attributes = []);
    }

    public function initialize(array $attributes = [])
    {
        $this->level = isset($attributes['applevel']) ? $attributes['applevel'] : 'production';
        if (isset($attributes['settings']['filePath'])) {
            if (isset($attributes['settings']['root']) && $attributes['settings']['root'] === true) {
                $this->filePath = ZXC_ROOT . DIRECTORY_SEPARATOR . $attributes['settings']['filePath'];
            } else {
                $this->filePath = $attributes['settings']['filePath'];
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

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }
}