<?php
declare(strict_types=1);

namespace edwrodrig\cnv_reader;

/**
 * Class HeaderLineParser
 *
 * Parses a line in the header
 * @package edwrodrig\cnv_reader
 */
class HeaderLineReader
{
    private string $line;

    /**
     * @var string
     */
    private string $init_char;

    private string $expectedInitChar;

    private string $key;

    private string $value;

    /**
     * HeaderLineParser constructor.
     * @param string $line
     * @param string|null $expectedInitChar
     * @throws exception\InvalidHeaderLineFormatException
     */
    public function __construct(string $line, ?string $expectedInitChar = null) {
        $this->line = $line;
        $this->validate();
        $this->init_char = $this->retrieveInitChar();
        if ( !is_null($expectedInitChar) )
            $this->expectedInitChar = $expectedInitChar;

        $this->retrieveKeyAndValue();
    }

    public function getExpectedInitChar() : string {
        return $this->expectedInitChar ?? $this->init_char;
    }

    /**
     * Get the first character of a header line
     *
     * Just get the first character of the line which in a header match the header
     * @return string
     */
    private function retrieveInitChar() : string {
        return $this->line[0];
    }

    /**
     * Get the first character of the header line
     * @return string
     */
    public function getInitChar() : string {
        return $this->init_char;
    }

    /**
     * Validate the header line
     *
     * When is not valid this function throws
     * @throws exception\InvalidHeaderLineFormatException
     */
    private function validate() {
        if ( strlen($this->line) <= 2 )
            throw new exception\InvalidHeaderLineFormatException($this->line);
    }

    public function isDataLine() : bool {
        if ( isset($this->expectedInitChar) ) {
            if ( $this->expectedInitChar != $this->init_char )
                return true;
        }
        return false;
    }

    /**
     * Check if a line is the end of a header.
     *
     * The pattern is something like *END* or %END%
     * @uses CnvReader::init_char
     * @return bool
     */
    public function isEnd() : bool {
        $pattern = sprintf('%sEND%s', $this->init_char, $this->init_char);
        return strpos($this->line, $pattern) === 0;
    }

    /**
     * Clear the line from header init char and {@see trim surrounding whitespaces}
     * @return string
     */
    private function getCleanLine() : string {
        $regex = sprintf('/^%s*/', preg_quote($this->init_char, '/'));
        $line = preg_replace($regex, '', $this->line);
        return trim($line);
    }

    private function retrieveKeyAndValue() {
        $line = $this->getCleanLine();
        $pos_colon = strpos($line, ':');
        $pos_equals = strpos($line, '=');


        if ( $pos_colon === FALSE && $pos_equals === FALSE ) {
            $this->value = $line;
        } else {
            $separator = ':';
            if ( $pos_colon === FALSE ) {
                $separator = '=';
            } else if ( $pos_equals === FALSE ) {
                $separator = ':';
            } else if ( $pos_equals < $pos_colon ) {
                $separator = '=';
            }

            $tokens = explode($separator, $line, 2);
            $this->key = trim($tokens[0]);
            $this->value = trim($tokens[1]);
        }

        if ( empty($this->key)) unset($this->key);
        if ( empty($this->value) ) unset($this->value);
    }

    /**
     * Get the line key
     *
     * If the line is a key value type. null otherwise
     * @return null|string
     */
    public function getKey() : ?string {
        return $this->key ?? null;
    }

    /**
     * Get the line value
     * @return null|string
     */
    public function getValue() : ?string {
        return $this->value ?? null;
    }

    /**
     * If this header line is a index entry.
     *
     * When contains the information of a key and a value for example
     *
     * position = 123.234234
     * date : 2018-02-03
     *
     * @return bool
     */
    public function isIndexed() : bool {
        return isset($this->key);
    }

    /**
     * If this line has not key or value
     * @return bool
     */
    public function isEmpty() : bool {
        return !isset($this->key) && !isset($this->value);
    }
}