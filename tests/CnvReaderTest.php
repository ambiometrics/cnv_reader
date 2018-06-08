<?php
declare(strict_types=1);

namespace test\edwrodrig\cnv_reader;
use edwrodrig\cnv_reader\CnvReader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * Class ParserTest
 */
class CnvReaderTest  extends TestCase
{

    /**
     * @var vfsStreamDirectory
     */
    private $root;

    public function setUp() {
        $this->root = vfsStream::setup();
    }

    /**
     * @testWith [["9401", "48.000", "11.7267", "11.7241", "3.868346"],"       9401     48.000    11.7267    11.7241   3.868346  "]
     * [["7753", "2.000", "13.9517", "13.9508", "4.245499", "4.029069", "5.1882", "5.1698", "1.7389", "1.3470e+02", "35.4565", "33.5105", "26.5455", "25.0464", "0.0000e+00"],"       7753      2.000    13.9517    13.9508   4.245499   4.029069     5.1882     5.1698     1.7389 1.3470e+02    35.4565    33.5105    26.5455    25.0464 0.0000e+00"]
     * @param array $expected
     * @param string $line
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     * @throws \edwrodrig\cnv_reader\exception\InvalidStreamException
     * @throws \edwrodrig\cnv_reader\exception\OpenFileException
     */
    function testParseDataLine(array $expected, string $line)
    {
        $filename =  $this->root->url() . '/test';

        file_put_contents($filename, <<<EOF
% binavg_in = X:\MAREA_ROJA_201605\E001\E01_MR_20160526_CNV_FIL_ALG_CTM_LOOP_DER.cnv
% binavg_bintype = decibars
% binavg_binsize = 1
% binavg_excl_bad_scans = no
% binavg_skipover = 0
% binavg_surface_bin = yes, min = 2.000, max = 2.000, value = 2.000
% file_type = ascii
%END%

EOF
        . $line
        );
        $p = new CnvReader($filename);
        $data = iterator_to_array($p, false);
        $this->assertEquals([$expected], $data);
    }


    public function testHappy() {
        $filename =  $this->root->url() . '/test';

        file_put_contents($filename, <<<EOF
% binavg_in = X:\MAREA_ROJA_201605\E001\E01_MR_20160526_CNV_FIL_ALG_CTM_LOOP_DER.cnv
% binavg_bintype = decibars
% binavg_binsize = 1
% binavg_excl_bad_scans = no
% binavg_skipover = 0
% binavg_surface_bin = yes, min = 2.000, max = 2.000, value = 2.000
% file_type = ascii
%END%
       7753      2.000    13.9517    13.9508   4.245499   4.029069     5.1882     5.1698     1.7389 1.3470e+02    35.4565    33.5105    26.5455    25.0464 0.0000e+00
       7821      3.000    13.9554    13.9553   3.306785   4.029673     5.2539     5.3932     1.7691 9.9861e+01    27.0078    33.5118    20.0355    25.0465 0.0000e+00
       7919      4.000    13.9603    13.9596   3.038374   4.029993     4.5721     5.7173     1.8292 7.8707e+01    24.5850    33.5106    18.1677    25.0447 0.0000e+00
       7939      5.000    13.9584    13.9583   4.037179   4.030063     4.5486     5.1847     1.7862 6.6558e+01    33.5757    33.5120    25.0952    25.0461 0.0000e+00
       7958      6.000    13.9579    13.9587   3.901999   4.030753     4.7872     5.5840     1.7402 6.3956e+01    32.3447    33.5176    24.1460    25.0504 0.0000e+00
       7984      7.000    13.9601    13.9618   3.736277   3.973204     5.3225     5.1658     1.7472 5.9525e+01    30.8341    32.9904    22.9807    24.6432 0.0000e+00
       8040      8.000    13.9506    13.9473   4.070614   3.215217     4.9592     4.6683     1.8711 5.0820e+01    33.8869    26.1938    25.3369    19.4105 0.0000e+00
EOF
        );


        $p = new CnvReader($filename);
        $data = iterator_to_array($p, false);
        $this->assertEquals(7753, $data[0][0]);
        $this->assertEquals(33.8869, $data[6][10]);
        $this->assertEquals(15, count($data[0]));
    }


}
