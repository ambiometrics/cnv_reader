<?php

namespace edwrodrig\cnv_reader;
use IteratorAggregate;

/**
 * Class FileParser
 * 
 * This class parse a converted data file format from some STD devices.
 * CNV stand for converted
 * The format may vary between vendors.
 * 
 * Converted Data File (.cnv) Format
 * 
 * Converted files consist of a descriptive header followed by converted data in
 * engineering units. The header contains:
 * 1. Header information from the raw input data file (these lines begin with *).
 * 2. Header information describing the converted data file (these lines begin with #)
 *    The descriptions include:
 *     * number of rows and columns of data
 *     * variable for each column (for example, pressure, temperature, etc.)
 *     * interval between each row (scan rate or bin size)
 *     * historical record of processing steps used to create or modify file
 * 3. ASCII string *END* to flag the end of the header information.
 *
 * Converted data is stored in rows and columns of ASCII numbers
 * (11 characters per value) or as a binary data stream (4 byte binary floating
 * point number for each value). The last column is a flag field used to mark
 * scans as bad in Loop Edit
 *
 * @see http://www.odb.ntu.edu.tw/Thermosalinograph/instrument/SBEDataProcessing.pdf
 * @package edwrodrig\cnv_reader
 */
class CnvReader implements IteratorAggregate
{

    /**
     * @var bool|null|resource
     */
    public $stream = null;

    /**
     * @var HeaderReader
     */
    public $header;


    /**
     * FileParser constructor.
     * @param string $filename
     * @throws exception\InvalidHeaderLineFormatException
     * @throws exception\InvalidStreamException
     * @throws exception\OpenFileException
     */
    public function __construct(string $filename) {
        $this->stream = fopen($filename, 'r');
        if ( $this->stream === FALSE ) {
            throw new exception\OpenFileException($filename);
        }
        $this->header = new HeaderReader($this->stream);
    }

    public function __destruct()
    {
        if (is_null($this->stream)) return;
        fclose($this->stream);
    }

    /**
     * Get the headers of the line
     * @return HeaderReader
     */
    public function getHeaders() : HeaderReader {
        return $this->header;
    }

    /**
     * Convert a raw line in a array of columns.
     *
     * You can get the column metadata with {@see HeaderParser::getMetricByColummn()}
     * @see CnvReader::getHeaders() to get the header class
     * @param string $line
     * @return array
     */
    private function parseDataLine(string $line) : array {
        $line = mb_convert_encoding($line, 'UTF-8');
        $line = trim($line);
        $tokens = preg_split('/\s+/', $line);
        return $tokens;
    }

    public function getIterator() {
        while ($line = fgets($this->stream)) {
            $data = $this->parseDataLine($line);
            yield $data;
        }
    }


}
