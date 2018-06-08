<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 07-06-18
 * Time: 15:49
 */

namespace test\edwrodrig\cnv_reader;

use edwrodrig\cnv_reader\CoordinateReader;
use edwrodrig\cnv_reader\HeaderLineaReader;
use PHPUnit\Framework\TestCase;

class CoordinateReaderTest extends TestCase
{
    /**
     * @testWith  [true, "* NMEA Latitude = 20 06.01 S"]
     *            [false, "* NMEA Longitude = 070 14.97 W"]
     * @param bool $expected
     * @param string $line
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     */
    public function testIsLatitude(bool $expected, string $line) {
        $line = new HeaderLineaReader($line);
        $this->assertEquals($expected, CoordinateReader::isLatitude($line));
    }

    /**
     * @testWith [true, "* NMEA Longitude = 070 14.97 W"]
     *           [false, "* NMEA Latitude = 20 06.01 S" ]
     * @param bool $expected
     * @param string $line
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     */
    public function testIsLongitude(bool $expected, string $line) {
        $line = new HeaderLineaReader($line);
        $this->assertEquals($expected, CoordinateReader::isLongitude($line));
    }

    /**
     * @testWith [-20.100166666667, -70.2495, "20 06.01 S", "070 14.97 W"]
     * @param float $expectedLat
     * @param float $expectedLng
     * @param string $stringLat
     * @param string $stringLng
     */
    public function testParseCoordinate(float $expectedLat, float $expectedLng, string $stringLat, string $stringLng) {
        $position = new CoordinateReader($stringLat, $stringLng);
        $coordinate = $position->getCoordinate();
        $this->assertEquals($expectedLat, $coordinate->getLat());
        $this->assertEquals($expectedLng, $coordinate->getLng());
    }
}
