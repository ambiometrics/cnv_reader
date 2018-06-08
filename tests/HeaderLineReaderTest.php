<?php
declare(strict_types=1);

namespace test\edwrodrig\cnv_reader;

use edwrodrig\cnv_reader\HeaderLineaReader;
use edwrodrig\cnv_reader\MetricReader;
use PHPUnit\Framework\TestCase;

class HeaderLineReaderTest extends TestCase
{
    /**
     * @testWith ["*", "* hola"]
     *          ["#", "# hola"]
     * @param string $expected
     * @param string $line
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     */
    public function testInitChar(string $expected, string $line) {
        $header = new HeaderLineaReader($line);
        $this->assertEquals($expected, $header->getInitChar());
    }

    /**
     * @testWith [true, "*END*"]
     *           [true, "%END%"]
     *           [false, "%end%"]
     * @param bool $expected
     * @param string $line
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     */
    public function testIsEnd(bool $expected, string $line) {
        $header = new HeaderLineaReader($line);
        $this->assertEquals($expected, $header->isEnd());
    }

    /**
     * @testWith    ["hola", "chao", "# hola = chao"]
     *              ["hola", "chao", "# hola =    chao"]
     *              ["hola", "chao", "# hola :    chao"]
     *              ["hola", "= chao", "# hola :   = chao"]
     *              ["hola", ": chao", "# hola  =   : chao"]
     *              [null, "hola chao", "# hola chao"]
     *              [null, "hola chao", "#### hola chao"]
     *              [null, "hola chao", "#### hola chao    "]
     * @param null|string $expectedKey
     * @param null|string $expectedValue
     * @param string $line
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     */
    public function testKeyValue(?string $expectedKey, ?string $expectedValue, string $line) {
        $header = new HeaderLineaReader($line);
        $this->assertEquals($expectedKey, $header->getKey());
        $this->assertEquals($expectedValue, $header->getValue());
    }

    /**
     * @testWith    ["# name 0 = scan: Scan Count"]
     *              ["# name 1 = prDM: Pressure, Digiquartz [db]"]
     *              ["# name 3 = t190C: Temperature, 2 [ITS-90, deg C]"]
     *              ["# name 9 = par: PAR/Irradiance, Biospherical/Licor"]
     *              ["# name 26 = D2-D1: Density Difference, 2 - 1 [sigma-theta, kg/m^3]"]
     *              ["# name 23 = sbeox1ML/L: Oxygen, SBE 43, 2 [ml/l], WS = 2"]
     * @param string $line
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     */
    public function testMetric(string $line) {
        $header = new HeaderLineaReader($line);
        $this->assertTrue(MetricReader::isMetric($header));

    }
}
