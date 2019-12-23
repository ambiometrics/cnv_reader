<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 04-06-18
 * Time: 16:43
 */

namespace edwrodrig\cnv_reader;


use DateTime;
use Location\Coordinate;

class HeaderReader
{
    private $indexed_data = [];

    private $data = [];

    /**
     * @var MetricInfoReader[]
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
     * @return MetricInfoReader[]
     */
    public function getMetrics() : array {
        return $this->metrics;
    }

    /**
     * Get a metric by column index
     * @param int $index
     * @return MetricInfoReader
     */
    public function getMetricByColumn(int $index) : ?MetricInfoReader {
        if ( isset($this->metrics[$index]) )
            return $this->metrics[$index];
        else
            return null;
    }

    /**
     * Get the GPS coordinate of this file
     * @return null|Coordinate
     */
    public function getCoordinate() : ?Coordinate {
        return $this->coordinate;
    }

    /**
     * Get the date of the file.
     *
     * Must return the date of the sample
     * @return DateTime|null
     */
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
        $expectedInitChar = null;
        do {
            $currentPosition = ftell($this->stream);
            $line = fgets($this->stream);
            $line_parser = new HeaderLineReader($line, $expectedInitChar);
            $expectedInitChar = $line_parser->getExpectedInitChar();

            if ( $line_parser->isEmpty() ) {
                continue;
            }

            if ( $line_parser->isDataLine() ) {
                fseek($this->stream, $currentPosition);
                break;
            }

            if ( $line_parser->isEnd() ) {
                break;

            } else if ( !$line_parser->isIndexed() ) {
                $this->data[] = $line_parser->getValue();

            } else if ( MetricReader::isMetric($line_parser) ) {
                $metric_parser = new MetricReader($line_parser);
                $this->metrics[$metric_parser->getIndex()] = $metric_parser->getInfo();

            } else if ( CoordinateReader::isLatitude($line_parser) ) {
                $latitude = $line_parser->getValue();

            } else if ( CoordinateReader::isLongitude($line_parser) ) {
                $longitude = $line_parser->getValue();

            } else if ( DateTimeReader::isDateTime($line_parser) ) {
                $this->datetime = (new DateTimeReader($line_parser->getValue()))->getDateTime();

            } else {
                $this->indexed_data[$line_parser->getKey()] = $line_parser->getValue();

            }
        } while ( true );

        if ( !is_null($latitude) && !is_null($longitude) ) {
            $this->coordinate = (new CoordinateReader($latitude, $longitude))->getCoordinate();
        }


    }

}