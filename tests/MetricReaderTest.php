<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 06-06-18
 * Time: 17:47
 */

namespace test\edwrodrig\cnv_reader;

use edwrodrig\cnv_reader\HeaderLineaReader;
use edwrodrig\cnv_reader\MetricReader;
use PHPUnit\Framework\TestCase;

class MetricReaderTest extends TestCase
{

    /**
     * @testWith [true, "# name 1 = something"]
     *              [false, "# name = something"]
     *              [false, "## hola"]
     *              [false, "# hola"]
     * @param bool $expected
     * @param string $header_line
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     */
    public function testIsMetric(bool $expected, string $header_line) {
        $header_line_parser = new HeaderLineaReader($header_line);
        $this->assertEquals($expected, MetricReader::isMetric($header_line_parser));
    }

    /**
     * @testWith [1, "something", "# name 1 = something"]
     *           [2, "name", "# name 2 = name: other data"]
     *           [14, "chance", "# name 14 = chance: hola[12]"]
     *           [10, "black", "## name 10 = black"]
     * @param int $expectedIndex
     * @param string $expectedName
     * @param string $header_line
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     */
    public function testGetIndex(int $expectedIndex, string $expectedName, string $header_line) {
        $header_line_parser = new HeaderLineaReader($header_line);
        $this->assertTrue( MetricReader::isMetric($header_line_parser));
        $metric_parser = new MetricReader($header_line_parser);
        $this->assertEquals($expectedIndex, $metric_parser->getIndex());
        $this->assertEquals($expectedName, $metric_parser->getInfo()->getName());
    }
}
