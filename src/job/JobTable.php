<?php

namespace easyyuan\crontab\job;

use easyyuan\crontab\base\Instance;
use easyyuan\crontab\Config;
use easyyuan\crontab\execute\FormatParser;
use easyyuan\crontab\execute\JobExecute;
use Swoole\Table;

class JobTable
{
    use Instance;

    /**
     * @var Table
     */
    private $_table;

    /**
     * JobTable constructor.
     * @param $size
     */
    private function __construct($size)
    {
        $this->_table = new Table( $size );
        $this->_table->column( 'name',Table::TYPE_STRING,20 );  //名称
        $this->_table->column( 'start_time',Table::TYPE_INT,8 );  //开始执行时间
        $this->_table->column( 'stop_time',Table::TYPE_INT,8 );  //停止执行时间
        $this->_table->column( 'format',Table::TYPE_STRING,30 ); //crontab格式 加秒
        $this->_table->column( 'run_type',Table::TYPE_STRING,10 );  //运行类型
        $this->_table->column( 'command',Table::TYPE_STRING,$size );  //执行任务
        $this->_table->column( 'status',Table::TYPE_INT,1 );  //状态 1正常 0关闭
        $this->_table->create();
    }

    /**
     * each
     * @param null $callBack
     * @return mixed
     */
    public function each($callBack = null)
    {
        $data = [];
        foreach ( $this->_table as $key => $value ) {
            if (is_callable( $callBack )) {
                $callBack( $key,$value );
            }
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * get
     * @param $key
     * @return bool
     */
    public function get($key)
    {
        return $this->_table->get( $key );
    }

    /**
     * set
     * @param $key
     * @param array $data
     * @param bool $saveFile
     * @return bool
     */
    public function set($key,array $data = [],bool $saveFile = true)
    {
        if ($this->_table->set( $key,$data )) {
            return !$saveFile || $this->saveToFile();
        }
        return false;
    }

    /**
     * saveToFile
     * @return bool
     */
    protected function saveToFile()
    {
        return file_put_contents( Config::getInstance()->jobConfig['data_file'],serialize( $this->each() ) );
    }

    /**
     * start
     * @param $key
     * @param $data
     * @return bool
     */
    public function start($key,$data)
    {
        $data['status'] = 1;
        return $this->set( $key,$data );
    }

    /**
     * stop
     * @param $key
     * @param $data
     * @return bool
     */
    public function stop($key,$data)
    {
        $data['status'] = 0;
        return $this->set( $key,$data );
    }

    /**
     * del
     * @param $key
     * @return bool
     */
    public function del($key)
    {
        if ($this->_table->del( $key )) {
            return $this->saveToFile();
        }
        return false;
    }

    /**
     * count
     * @return int
     */
    public function count()
    {
        return $this->_table->count();
    }

    /**
     * checkData
     * @param $data
     * @return bool|string
     */
    public function checkData($data)
    {
        if (empty( $data['name'] ))
            return '任务名称不能为空.';

        if (strlen( $data['name'] ) > 20)
            return '任务名称不能大于20个字符.';

        if (!empty( $data['start_time'] )) {
            if (strtotime( date( 'Y-m-d H:i:s',$data['start_time'] ) ) !== $data['start_time']) {
                return '开始时间必须是时间戳或不设置.';
            }
        }

        if (!empty( $data['stop_time'] )) {
            if (strtotime( date( 'Y-m-d H:i:s',$data['stop_time'] ) ) !== $data['stop_time']) {
                return '结束时间必须是时间戳或不设置.';
            }
        }

        $command = trim( $data['command'] );

        if (empty( $command ))
            return '任务命令不能为空.';

        if (strlen( $command ) > $this->_table->size)
            return '任务命令不能大于'.$this->_table->size.'个字符.';

        if (empty( $data['run_type'] ))
            return '任务类型不能为空.';

        $runTypes     = Config::getInstance()->jobConfig['run_types'];
        $executeClass = $runTypes[$data['run_type']] ?? null;
        if (empty( $executeClass )) {
            return '任务类型错误.';
        }

        /** @var $jobExecute JobExecute */
        $jobExecute = new $executeClass();
        if (!( $jobExecute instanceof JobExecute )) {
            return '任务执行文件必须继承 JobExecute 类.';
        }

        if (!$executeClass::validate( $command )) {
            return "类型为{$data['run_type']}时,command格式校验失败.";
        }

        if (!FormatParser::getInstance()->isValid( $data['format'] ?? '' )) {
            return 'crontab格式错误.';
        }

        return true;
    }
}