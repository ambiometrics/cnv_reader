<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 05-06-18
 * Time: 15:51
 */

namespace test\edwrodrig\cnv_reader;

use edwrodrig\cnv_reader\MetricInfoReader;
use PHPUnit\Framework\TestCase;

class MetricInfoReaderTest extends TestCase
{
    /**
     * @testWith    ["scan", null, "scan: Scan Count"]
     *              ["prDM", "db", "prDM: Pressure, Digiquartz [db]"]
     *              ["t190C", "ITS-90, deg C", "t190C: Temperature, 2 [ITS-90, deg C]"]
     *              ["par", null, "par: PAR/Irradiance, Biospherical/Licor"]
     *              ["D2-D1", "sigma-theta, kg/m^3", "D2-D1: Density Difference, 2 - 1 [sigma-theta, kg/m^3]"]
     *              ["sbeox1ML/L", "ml/l", "sbeox1ML/L: Oxygen, SBE 43, 2 [ml/l], WS = 2"]
     * @param string $expectedName
     * @param null|string $expectedUnit
     * @param string $line
     */
    public function testNameUnit(string $expectedName, ?string $expectedUnit, string $line) {
        $metric = new MetricInfoReader($line);
        $this->assertEquals($expectedName, $metric->getName());
        $this->assertEquals($expectedUnit, $metric->getUnit());
    }

    /**
     * @testWith    ["Scan Count", [], "scan: Scan Count"]
     *              ["Pressure", ["Digiquartz"], "prDM: Pressure, Digiquartz [db]"]
     *              ["Temperature", ["2"], "t190C: Temperature, 2 [ITS-90, deg C]"]
     *              ["PAR/Irradiance", ["Biospherical/Licor"], "par: PAR/Irradiance, Biospherical/Licor"]
     *              ["Density Difference", ["2 - 1"], "D2-D1: Density Difference, 2 - 1 [sigma-theta, kg/m^3]"]
     *              ["Oxygen", ["SBE 43", "2", "WS = 2"], "sbeox1ML/L: Oxygen, SBE 43, 2 [ml/l], WS = 2"]
     * @param string $expectedType
     * @param array $expectedOther
     * @param string $line
     */
    public function testTypeOther(string $expectedType, array $expectedOther, string $line) {
        $metric = new MetricInfoReader($line);
        $this->assertEquals($expectedType, $metric->getType());
        $this->assertEquals($expectedOther, $metric->getOther());
    }
}
