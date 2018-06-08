<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 07-06-18
 * Time: 14:29
 */

namespace test\edwrodrig\cnv_reader;

use edwrodrig\cnv_reader\HeaderReader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class HeaderReaderTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    public function setUp() {
        $this->root = vfsStream::setup();
    }

    /**
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     * @throws \edwrodrig\cnv_reader\exception\InvalidStreamException
     */
    public function testEmptyHeader() {

        $filename =  $this->root->url() . '/test';

        file_put_contents($filename, <<<EOF
* 
*END*
EOF
);
        $f = fopen($filename, 'r');

        $parser = new HeaderReader($f);
        $this->assertEquals([], $parser->getData());

        fclose($f);
    }

    /**
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     * @throws \edwrodrig\cnv_reader\exception\InvalidStreamException
     */
    public function testHeaderDataOnly() {

        $filename =  $this->root->url() . '/test';

        file_put_contents($filename, <<<EOF
* Edwin
* Rodríguez
* León
*END*
EOF
        );
        $f = fopen($filename, 'r');

        $parser = new HeaderReader($f);
        $this->assertEquals(['Edwin', 'Rodríguez', 'León'], $parser->getData());

        fclose($f);
    }

    /**
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     * @throws \edwrodrig\cnv_reader\exception\InvalidStreamException
     */
    public function testHeaderIndexedData() {

        $filename =  $this->root->url() . '/test';

        file_put_contents($filename, <<<EOF
* name : Edwin
* surname = Rodríguez
* surname2 = León
*END*
EOF
        );
        $f = fopen($filename, 'r');

        $parser = new HeaderReader($f);
        $this->assertEquals(['name' => 'Edwin', 'surname' => 'Rodríguez', 'surname2' => 'León'], $parser->getIndexedData());

        fclose($f);
    }

    /**
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     * @throws \edwrodrig\cnv_reader\exception\InvalidStreamException
     */
    public function testHeaderMetricData() {
        $filename =  $this->root->url() . '/test';

        file_put_contents($filename, <<<EOF
* name 1 : Edwin
* surname = Rodríguez
* surname2 = León
*END*
EOF
        );
        $f = fopen($filename, 'r');

        $parser = new HeaderReader($f);
        $this->assertEquals(['surname' => 'Rodríguez', 'surname2' => 'León'], $parser->getIndexedData());
        $this->assertEquals('Edwin', $parser->getMetricByColumn(1)->getName());

        fclose($f);
    }

    /**
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     * @throws \edwrodrig\cnv_reader\exception\InvalidStreamException
     */
    public function testHeaderCoordinate() {
        $filename =  $this->root->url() . '/test';

        file_put_contents($filename, <<<EOF
* NMEA Longitude = 70.10
* NMEA Latitude = 20.06
*END*
EOF
        );
        $f = fopen($filename, 'r');

        $parser = new HeaderReader($f);
        $this->assertEquals([], $parser->getIndexedData());
        $this->assertEquals([], $parser->getData());
        $this->assertEquals(20.06, $parser->getCoordinate()->getLat());
        $this->assertEquals(70.10, $parser->getCoordinate()->getLng());

        fclose($f);
    }

    /**
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     * @throws \edwrodrig\cnv_reader\exception\InvalidStreamException
     */
    public function testHeaderCoordinateNullLat() {
        $filename =  $this->root->url() . '/test';

        file_put_contents($filename, <<<EOF
* NMEA Longitude = 70.10
*END*
EOF
        );
        $f = fopen($filename, 'r');

        $parser = new HeaderReader($f);
        $this->assertEquals([], $parser->getIndexedData());
        $this->assertEquals([], $parser->getData());
        $this->assertNull($parser->getCoordinate());

        fclose($f);
    }

    /**
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     * @throws \edwrodrig\cnv_reader\exception\InvalidStreamException
     */
    public function testHeaderCoordinateNullLng() {
        $filename =  $this->root->url() . '/test';

        file_put_contents($filename, <<<EOF
* NMEA Latitude = 70.10
*END*
EOF
        );
        $f = fopen($filename, 'r');

        $parser = new HeaderReader($f);
        $this->assertEquals([], $parser->getIndexedData());
        $this->assertEquals([], $parser->getData());
        $this->assertNull($parser->getCoordinate());

        fclose($f);
    }

    /**
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     * @throws \edwrodrig\cnv_reader\exception\InvalidStreamException
     */
    public function testHeaderDateTime() {
        $filename =  $this->root->url() . '/test';

        file_put_contents($filename, <<<EOF
* NMEA UTC (Time) = Nov 27 2015 17:55:23        
*END*
EOF
        );
        $f = fopen($filename, 'r');

        $parser = new HeaderReader($f);
        $this->assertEquals([], $parser->getIndexedData());
        $this->assertEquals([], $parser->getData());
        $this->assertEquals('2015-11-27 17:55:23', $parser->getDateTime()->format('Y-m-d H:i:s'));

        fclose($f);
    }

    /**
     * @throws \edwrodrig\cnv_reader\exception\InvalidHeaderLineFormatException
     * @throws \edwrodrig\cnv_reader\exception\InvalidStreamException
     */
    public function testHeaderDateTimeNull() {
        $filename =  $this->root->url() . '/test';

        file_put_contents($filename, <<<EOF
*END*
EOF
        );
        $f = fopen($filename, 'r');

        $parser = new HeaderReader($f);
        $this->assertEquals([], $parser->getIndexedData());
        $this->assertEquals([], $parser->getData());
        $this->assertNull($parser->getDateTime());

        fclose($f);
    }


}
