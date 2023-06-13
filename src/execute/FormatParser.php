<?php

namespace easyyuan\crontab\execute;

use easyyuan\crontab\base\Instance;

class FormatParser
{
    use Instance;

    /**
     *  Finds next execution time(stamp) parsin crontab syntax.
     *
     * @param string $crontabString :
     *   0    1    2    3    4    5
     *   *    *    *    *    *    *
     *   -    -    -    -    -    -
     *   |    |    |    |    |    |
     *   |    |    |    |    |    +----- day of week (0 - 6) (Sunday=0)
     *   |    |    |    |    +----- month (1 - 12)
     *   |    |    |    +------- day of month (1 - 31)
     *   |    |    +--------- hour (0 - 23)
     *   |    +----------- min (0 - 59)
     *   +------------- sec (0-59)
     *
     * @param null|int $start_time
     * @return int[]
     * @throws \InvalidArgumentException
     */
    public function parse($crontabString,$start_time = null)
    {
        if (!$this->isValid( $crontabString )) {
            throw new \InvalidArgumentException( 'Invalid cron string: '.$crontabString );
        }
        $start_time = $start_time ? $start_time : time();
        $date       = $this->parseDate( $crontabString );

        if (in_array( (int)date( 'i',$start_time ),$date['minutes'] )
            && in_array( (int)date( 'G',$start_time ),$date['hours'] )
            && in_array( (int)date( 'j',$start_time ),$date['day'] )
            && in_array( (int)date( 'w',$start_time ),$date['week'] )
            && in_array( (int)date( 'n',$start_time ),$date['month'] )
        ) {
            $result = [];
            foreach ( $date['second'] as $second ) {
                $result[] = $start_time + $second;
            }
            return $result;
        }
        return [];
    }

    /**
     * isValid
     * @param string $crontabString
     * @return bool
     */
    public function isValid(string $crontabString): bool
    {
        if (!preg_match( '/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i',trim( $crontabString ) )) {
            if (!preg_match( '/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i',trim( $crontabString ) )) {
                return false;
            }
        }
        return true;
    }

    /**
     * parseSegment
     * @param string $string
     * @param int $min
     * @param int $max
     * @param int|null $start
     * @return array
     */
    protected function parseSegment(string $string,int $min,int $max,int $start = null)
    {
        if ($start === null || $start < $min) {
            $start = $min;
        }
        $result = [];
        if ($string === '*') {
            for ( $i = $start; $i <= $max; ++$i ) {
                $result[] = $i;
            }
        } elseif (strpos( $string,',' ) !== false) {
            $exploded = explode( ',',$string );
            foreach ( $exploded as $value ) {
                if (!$this->between( (int)$value,(int)( $min > $start ? $min : $start ),(int)$max )) {
                    continue;
                }
                $result[] = (int)$value;
            }
        } elseif (strpos( $string,'/' ) !== false) {
            $exploded = explode( '/',$string );
            if (strpos( $exploded[0],'-' ) !== false) {
                [$nMin,$nMax] = explode( '-',$exploded[0] );
                $nMin > $min && $min = $nMin;
                $nMax < $max && $max = $nMax;
            }
            $start > $min && $min = $start;
            for ( $i = $start; $i <= $max; ) {
                $result[] = $i;
                $i        += $exploded[1];
            }
        } elseif ($this->between( (int)$string,$min > $start ? $min : $start,$max )) {
            $result[] = (int)$string;
        }
        return $result;
    }

    /**
     * between
     * @param int $value
     * @param int $min
     * @param int $max
     * @return bool
     */
    private function between(int $value,int $min,int $max): bool
    {
        return $value >= $min && $value <= $max;
    }


    /**
     * parseDate
     * @param string $crontabString
     * @return array
     */
    private function parseDate(string $crontabString): array
    {
        $cron = preg_split( '/[\\s]+/i',trim( $crontabString ) );
        if (count( $cron ) == 6) {
            $date = [
                'second'  => $this->parseSegment( $cron[0],0,59 ),
                'minutes' => $this->parseSegment( $cron[1],0,59 ),
                'hours'   => $this->parseSegment( $cron[2],0,23 ),
                'day'     => $this->parseSegment( $cron[3],1,31 ),
                'month'   => $this->parseSegment( $cron[4],1,12 ),
                'week'    => $this->parseSegment( $cron[5],0,6 ),
            ];
        } else {
            $date = [
                'second'  => [1 => 0],
                'minutes' => $this->parseSegment( $cron[0],0,59 ),
                'hours'   => $this->parseSegment( $cron[1],0,23 ),
                'day'     => $this->parseSegment( $cron[2],1,31 ),
                'month'   => $this->parseSegment( $cron[3],1,12 ),
                'week'    => $this->parseSegment( $cron[4],0,6 ),
            ];
        }
        return $date;
    }
}