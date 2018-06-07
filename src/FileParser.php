<?php

namespace edwrodrig\cnv_parser;
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
 * @package edwrodrig\cnv_parser
 */
class FileParser
{

    public $stream = null;
    public $sensors = [];
    public $properties = [];
    public $info = [];




    function __destruct()
    {
        if (is_null($this->stream)) return;
        fclose($this->stream);
    }

    function find_column_by_name($name)
    {
        foreach ($this->sensors as $index => $var) {
            if ($var['name'] == $name)
                return $index;
        }
    }

    function set_stream($stream)
    {
        $this->stream = $stream;
        $this->sensors = [];
        $this->properties = [];
        $this->info = [];

        $this->read_headers();
    }

    function traverse()
    {
        while ($line = fgets($this->stream)) {
            $line = mb_convert_encoding($line, 'UTF-8');
            $data = self::parse_data_line($line);
            yield $data;
        }
    }

    static function parse_data_line($line)
    {
        return preg_split('/\s+/', trim($line));
    }

    static function parse_unit_section($unit)
    {
        $tokens = explode(',', $unit);
        $tokens = array_reverse($tokens);

        $r = [];
        $r['unit'] = trim($tokens[0]);
        if (!empty($tokens[1]))
            $r['detail'] = trim($tokens[1]);

        return $r;
    }

    static function parse_metric_section($metric)
    {
        $tokens = preg_split('/\s*,\s*/', $metric);

        $type = array_shift($tokens);
        $index = 1;
        if (!empty($tokens)) {
            $last = $tokens[count($tokens) - 1];
            if (is_numeric($last)) {
                $index = $last;
                array_pop($tokens);
            }
        }
        return [
            'type' => $type,
            'index' => $index,
            'other' => $tokens
        ];
    }

    fun

    function retrieve_info_from_parsed_header($parsed_header)
    {
        if (!isset($parsed_header['key'])) {
            $this->info[] = $parsed_header['value'];
            return;
        }

        $key = $parsed_header['key'];
        $value = $parsed_header['value'];
        if (preg_match('/^name (\d+)$/', $key, $matches)) {
            $index = $matches[1];
            if (preg_match('/^([^\:]*):([^\[]*)\[([^\[\]]*)\](.*)$/', $value, $matched_value)) {
                $this->sensors[$index]['name'] = trim($matched_value[1]);

                $this->sensors[$index]['metric'] = self::parse_metric_section(trim($matched_value[2]));

                if (!empty($matched_value[3]))
                    $this->sensors[$index]['unit'] = self::parse_unit_section(trim($matched_value[3]));
                if (!empty($matched_value[4]))
                    $this->sensors[$index]['metric']['other'][] = $matched_value[4];
            } else if (preg_match('/^([^\:]*):([^\[]*)$/', $value, $matched_value)) {
                $this->sensors[$index]['name'] = trim($matched_value[1]);

                $this->sensors[$index]['metric'] = self::parse_metric_section(trim($matched_value[2]));
            }
        } else if (preg_match('/^span (\d+)$/', $key, $matches)) {
            $this->sensors[$matches[1]]['span'] = $value;

        } else {
            $this->properties[$key] = $value;
        }
    }

    function is_header_line($line)
    {
        if (strlen($line) <= 0) return false;
        return $this->is_header_char($line[0]);
    }

    function is_header_char($char)
    {
        return in_array($char, $this->header_chars);
    }

    function parse_header_line($line)
    {
        $line = substr($line, 1); //clean initial %
        $line = trim($line);
        $tokens = [];
        if ($this->is_header_char($line[0])) {
            $line = substr($line, 1);
            $line = trim($line);
as
        if (count($tokens) >= 2) {
            return ['key' => trim($tokens[0]), 'value' => trim($tokens[1])];
        } else {
            return ['value' => trim($tokens[0])];
        }
    }

    function read_headers()
    {
        while ($line = fgets($this->stream)) {
            $line = mb_convert_encoding($line, 'UTF-8');
            if ( is_null($this->header_char) )
                $this->header_char = $this->retrieveHeaderCharFromLine($line);


            if ( $this->isLineHeaderEnd() )
                break;

            if ($this->is_header_line($line)) {
                $parsed_header = self::parse_header_line($line);
                $this->retrieve_info_from_parsed_header($parsed_header);
            }
        }
    }

    static function normalize_date($date_str)
    {
        $d = \DateTime::createFromFormat('M d Y H:i:s', $date_str);
        return $d->format('Y-m-d\TH:i:s');
    }

    static function str($str, $header_chars = ['%'])
    {
        $r = new self();
        $r->header_chars = $header_chars;
        $stream = fopen("php://temp", 'w+');
        fwrite($stream, $str);
        rewind($stream);
        $r->set_stream($stream);
        return $r;
    }

    static function file($f, $header_chars = ['%'])
    {
        $r = new self();
        $stream = fopen($f, 'r+');
        $r->header_chars = $header_chars;
        $r->set_stream($stream);
        return $r;
    }

}
