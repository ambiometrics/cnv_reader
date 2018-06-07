<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 04-06-18
 * Time: 16:43
 */

namespace edwrodrig\cnv_parser;


class HeaderParser
{
    private $indexed_data = [];

    private $data = [];

    /**
     * @var MetricInfoParser[]
     */
    private $metrics = [];

    /**
     * @var resource
     */
    private $stream;

    /**
     * HeaderParser constructor.
     * @param $stream
     * @throws exception\InvalidStreamException
     */
    public function __construct($stream) {
        if ( !is_resource($stream) ) {
            throw new exception\InvalidStreamException;
        }
        $this->stream = $stream;

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
     * @throws exception\InvalidHeaderLineFormatException
     */
    public function parse() {
        do {
            $line = fgets($this->stream);
            $line_parser = new HeaderLineParser($line);


            if ( $line_parser->isEnd() ) {
                break;

            } else if ( !$line_parser->isIndexed() ) {
                $this->data[] = $line_parser->getValue();

            } else if ( MetricParser::isMetric($line_parser) ) {
                $metric_parser = new MetricParser($line_parser);
                $this->metrics[$metric_parser->getIndex()] = $metric_parser->getInfo();

            } else {
                $this->indexed_data[$line_parser->getKey()] = $line_parser->getValue();

            }

        } while ( true );
    }

}