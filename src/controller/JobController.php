<?php

namespace easyyuan\crontab\controller;

use easyyuan\crontab\job\JobTable;

class JobController extends Controller
{

    /**
     * @var JobTable
     */
    protected $jobTable;

    public function init()
    {
        parent::init();
        $this->jobTable = JobTable::getInstance();
    }

    /**
     * find
     * @return array
     */
    public function find(): array
    {
        $id = (string)( $this->postData['id'] ?? null );
        if ($id && !empty( $data = $this->jobTable->get( $id ) )) {
            return $this->success( $data );
        }
        return $this->error( '定时任务('.$id.'):不存在.' );
    }

    /**
     * create
     * @return array
     */
    public function create(): array
    {
        $data = $this->postData;

        $res = $this->jobTable->checkData( $data );

        if (is_string( $res )) {
            return $this->error( $res );
        }
        $id = uniqid();
        //默认执行
        $data['status'] = 1;
        if ($this->jobTable->set( $id,$data )) {
            return $this->success( [
                'id' => $id,
            ] );
        }
        return $this->error();
    }

    /**
     * all
     * @return array
     */
    public function all(): array
    {
        $jobList = $this->jobTable->each();
        return $this->success( ['job_list' => $jobList] );
    }

    /**
     * delete
     * @return array
     */
    public function delete(): array
    {
        $id = (string)( $this->postData['id'] ?? null );
        if ($id && $this->jobTable->del( $id )) {
            return $this->success();
        }
        return $this->error();
    }

    /**
     * count
     * @return array
     */
    public function count(): array
    {
        return $this->success( [
            'count' => $this->jobTable->count(),
        ] );
    }

    /**
     * stop
     * @return array
     */
    public function start(): array
    {
        $id = (string)( $this->postData['id'] ?? null );
        if ($id && !empty( $data = $this->jobTable->get( $id ) )) {
            if ($data['status'] || $this->jobTable->start( $id,$data )) {
                return $this->success();
            }
        }
        return $this->error();
    }

    /**
     * stop
     * @return array
     */
    public function stop(): array
    {
        $id = (string)( $this->postData['id'] ?? null );
        if ($id && !empty( $data = $this->jobTable->get( $id ) )) {
            if (!$data['status'] || $this->jobTable->stop( $id,$data )) {
                return $this->success();
            }
        }
        return $this->error();
    }
}