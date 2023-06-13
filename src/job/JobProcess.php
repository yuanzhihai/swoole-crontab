<?php

namespace easyyuan\crontab\job;

use easyyuan\crontab\Config;
use easyyuan\crontab\execute\JobFacade;
use Swoole\Http\Server;
use Swoole\Process;

class JobProcess
{
    /**
     * JobProcess constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->_server = $server;
        $this->initTable();
    }

    /**
     * initTable
     */
    protected function initTable()
    {
        $jobDataFile = Config::getInstance()->jobConfig['data_file'];
        try {
            touch( $jobDataFile );

            if (!is_writable( $jobDataFile )) {
                throw new \Exception( 'data_file is not writable.' );
            }

            $jobTable = JobTable::getInstance( Config::getInstance()->jobConfig['table_size'] );
            $data     = @unserialize( @file_get_contents( $jobDataFile ) );
            if (!empty( $data )) {
                foreach ( $data as $key => $value ) {
                    $jobTable->set( $key,$value,false );
                }
            }
        } catch ( \Exception $e ) {
            print_ln( $e->getMessage() );
            exit();
        }
    }

    /**
     * getProcess
     * @return Process
     */
    public function getProcess()
    {
        $process = new Process( function () {
            while ( true ) {
                $this->sleep();
                JobTable::getInstance()->each( function ($key,&$value) {
                    JobFacade::getExecute( $key,$value );
                } );
            }
        },false,0,true );

        $process->name( 'SwooleCrontabProcess' );

        return $process;
    }

    private function sleep()
    {
        $current = date( 's',time() );
        $sleep   = 60 - $current;
        $sleep > 0 && \Swoole\Coroutine::sleep( $sleep );

    }
}