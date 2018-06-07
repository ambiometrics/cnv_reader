<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 07-06-18
 * Time: 15:05
 */

namespace edwrodrig\cnv_parser;


use Location\Coordinate;
use Location\Factory\CoordinateFactory;

class CoordinateParser
{
    /**
     * @var Coordinate
     */
    private $coordinate;

    public function __construct(string $latitude, string $longitude) {
        $this->coordinate = CoordinateFactory::fromString(sprintf('%s, %s', $latitude, $longitude));
    }

    public function getCoordinate() : Coordinate {
        return $this->coordinate;
    }

    public static function isLatitude(HeaderLineParser $header) : bool {
        if ( !$header->isIndexed() ) return false;

        if ( strpos($header->getKey(), 'NMEA Latitude') === 0 )
            return true;
        else return false;
    }

    public static function isLongitude(HeaderLineParser $header) : bool {
        if ( !$header->isIndexed() ) return false;

        if ( strpos($header->getKey(), 'NMEA Longitude') === 0 )
            return true;
        else return false;
    }
}