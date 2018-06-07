<?php
declare(strict_types=1);

namespace edwrodrig\cnv_parser;


class HeaderLineParser
{
    private $line;

    /**
     * @var string
     */
    private $init_char;

    /**
     * @var null|string
     */
    private $key = null;

    /**
     * @var null|string
     */
    private $value = null;

    /**
     * HeaderLineParser constructor.
     * @param string $line
     * @throws exception\InvalidHeaderLineFormatException
     */
    public function __construct(string $line) {
        $this->line = $line;
        $this->validate($this->line);
        $this->init_char = $this->retrieveInitChar();
        $this->retrieveKeyAndValue();
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
        if ( strlen($this->line) <= 2 ) throw new exception\InvalidHeaderLineFormatException($this->line);
    }

    /**
     * Check if a line is the end of a header.
     *
     * The pattern is something like *END* or %END%
     * @uses FileParser::init_char
     * @return bool
     */
    public function isEnd() : bool {
        $pattern = sprintf('%sEND%s', $this->init_char, $this->init_char);
        return strpos($this->line, $pattern) === 0;
    }

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

        if ( empty($this->key)) $this->key = null;
        if ( empty($this->value) ) $this->value = null;
    }

    public function getKey() : ?string {
        return $this->key;
    }

    public function getValue() : ?string {
        return $this->value;
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
        return !is_null($this->getKey());
    }

    /**
     * If this line has not key or value
     * @return bool
     */
    public function isEmpty() : bool {
        return is_null($this->getKey()) && is_null($this->getValue());
    }
}