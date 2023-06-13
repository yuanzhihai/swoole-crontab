<?php

namespace easyyuan\crontab;

use easyyuan\crontab\base\Instance;

/**
 * Class Logger
 * @package easyyuan\crontab
 */
class Logger
{
    use Instance;

    const LOG_LEVEL_DEBUG = 0;
    const LOG_LEVEL_INFO = 1;
    const LOG_LEVEL_NOTICE = 2;
    const LOG_LEVEL_WARNING = 3;
    const LOG_LEVEL_ERROR = 4;

    private $logPath = null;

    private $logConsole = true;

    private $fileName = null;

    /**
     * Logger constructor.
     */
    public function __construct()
    {
        $this->logPath = Config::getInstance()->runLogConfig['path'];
        if (!empty( $this->logPath )) {
            if (!is_dir( $this->logPath )) {
                mkdir( $this->logPath,0755,true );
            } else {
                if (!is_writable( $this->logPath )) {
                    $this->logPath = null;
                }
            }
        }
        $this->logConsole = Config::getInstance()->runLogConfig['console'];
    }

    /**
     * setFileName
     * @param string $fileName
     * @return $this
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * log
     * @param string|null $msg
     * @param int $logLevel
     * @param string $category
     */
    public function log(?string $msg,int $logLevel = self::LOG_LEVEL_DEBUG,string $category = 'debug')
    {
        if (empty( $this->logPath )) {
            return;
        }

        $prefix   = date( 'Ymd' );
        $date     = date( 'Y-m-d H:i:s' );
        $levelStr = $this->levelMap( $logLevel );

        if (empty( $this->fileName )) {
            $this->fileName = "{$prefix}.log";
        }

        $filePath = $this->logPath.DIRECTORY_SEPARATOR.$this->fileName;
        $str      = "[{$date}][{$category}][{$levelStr}]:{$msg}\n";
        file_put_contents( $filePath,"{$str}",FILE_APPEND | LOCK_EX );
    }

    /**
     * console
     * @param string|null $msg
     * @param int $logLevel
     * @param string $category
     */
    public function console(?string $msg,int $logLevel = self::LOG_LEVEL_DEBUG,string $category = 'debug')
    {
        $date     = date( 'Y-m-d H:i:s' );
        $levelStr = $this->levelMap( $logLevel );
        if ($this->logConsole) {
            print_ln( "[{$date}][{$category}][{$levelStr}]:[{$msg}]" );
        } else {
            $this->log( $msg,$logLevel,$category );
        }
    }

    public function info(?string $msg,string $category = 'info')
    {
        $this->console( $msg,self::LOG_LEVEL_INFO,$category );
    }

    public function notice(?string $msg,string $category = 'notice')
    {
        $this->console( $msg,self::LOG_LEVEL_NOTICE,$category );
    }

    public function waring(?string $msg,string $category = 'waring')
    {
        $this->console( $msg,self::LOG_LEVEL_WARNING,$category );
    }

    public function error(?string $msg,string $category = 'error')
    {
        $this->console( $msg,self::LOG_LEVEL_ERROR,$category );
    }

    private function levelMap(int $level)
    {
        switch ( $level ) {
            case self::LOG_LEVEL_DEBUG:
                return 'debug';
            case self::LOG_LEVEL_INFO:
                return 'info';
            case self::LOG_LEVEL_NOTICE:
                return 'notice';
            case self::LOG_LEVEL_WARNING:
                return 'warning';
            case self::LOG_LEVEL_ERROR:
                return 'error';
            default:
                return 'unknown';
        }
    }
}