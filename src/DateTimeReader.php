<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 07-06-18
 * Time: 16:20
 */

namespace edwrodrig\cnv_reader;


use DateTime;

class DateTimeReader
{
    /**
     * @var DateTime
     */
    private $datetime;

    public function __construct(string $datetime) {
        $this->datetime = new DateTime($datetime);
    }

    public function getDateTime() : DateTime {
        return $this->datetime;
    }

    public static function isDateTime(HeaderLineReader $header) : bool {
        if ( !$header->isIndexed() ) return false;

        $keys = [
            'NMEA UTC (Time)'
        ];

        if ( in_array($header->getKey(), $keys) )
            return true;
        else return false;
    }


}