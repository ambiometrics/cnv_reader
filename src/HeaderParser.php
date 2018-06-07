<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 04-06-18
 * Time: 16:43
 */

namespace edwrodrig\cnv_parser;


use DateTime;
use Location\Coordinate;

class HeaderParser
{
    private $indexed_data = [];

    private $data = [];

    /**
     * @var MetricInfoParser[]
     */
    private $metrics = [];

    /**
     * @var null|Coordinate
     */
    private $coordinate = null;

    /**
     * @var null|DateTime
     */
    private $datetime = null;

    /**
     * @var resource
     */
    private $stream;

    /**
     * HeaderParser constructor.
     * @param $stream
     * @throws exception\InvalidStreamException
     * @throws exception\InvalidHeaderLineFormatException
     */
    public function __construct($stream) {
        if ( !is_resource($stream) ) {
            throw new exception\InvalidStreamException;
        }
        $this->stream = $stream;
        $this->parse();
    }

    /**
     * Get header parsed data that is in a key value fashion
     * @return array
     */
    public function getIndexedData() : array {
        return $this->indexed_data;
    }

    /**
     * Get header parsed data that is not in key value fashion
     * @return array
     */
    public function getData() : array {
        return $this->data;
    }

    /**
     * Get metrics
     * @return array
     */
    public function getMetrics() : array {
        return $this->metrics;
    }

    /**
     * Get a metric by column index
     * @param int $index
     * @return MetricInfoParser
     */
    public function getMetricByColumn(int $index) : MetricInfoParser {
        return $this->metrics[$index];
    }

    /**
     * Get the GPS coordinate of this file
     * @return null|Coordinate
     */
    public function getCoordinate() : ?Coordinate {
        return $this->coordinate;
    }

    public function getDateTime() : ?DateTime {
        return $this->datetime;
    }

    /**
     * @throws exception\InvalidHeaderLineFormatException
     */
    private function parse() {

        /**
         * @var null|string $latitude
         */
        $latitude = null;
        /**
         * @var null|string $longitude
         */
        $longitude = null;
        do {
            $line = fgets($this->stream);
            $line_parser = new HeaderLineParser($line);

            if ( $line_parser->isEmpty() ) {
                continue;
            }

            if ( $line_parser->isEnd() ) {
                break;

            } else if ( !$line_parser->isIndexed() ) {
                $this->data[] = $line_parser->getValue();

            } else if ( MetricParser::isMetric($line_parser) ) {
                $metric_parser = new MetricParser($line_parser);
                $this->metrics[$metric_parser->getIndex()] = $metric_parser->getInfo();

            } else if ( CoordinateParser::isLatitude($line_parser) ) {
                $latitude = $line_parser->getValue();

            } else if ( CoordinateParser::isLongitude($line_parser) ) {
                $longitude = $line_parser->getValue();

            } else if ( DateTimeParser::isDateTime($line_parser) ) {
                $this->datetime = (new DateTimeParser($line_parser->getValue()))->getDateTime();

            } else {
                $this->indexed_data[$line_parser->getKey()] = $line_parser->getValue();

            }
        } while ( true );

        if ( !is_null($latitude) && !is_null($longitude) ) {
            $this->coordinate = (new CoordinateParser($latitude, $longitude))->getCoordinate();
        }


    }

}