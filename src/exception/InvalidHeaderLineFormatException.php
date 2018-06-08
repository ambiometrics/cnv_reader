<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 04-06-18
 * Time: 16:49
 */

namespace edwrodrig\cnv_reader\exception;


use Exception;

class InvalidHeaderLineFormatException extends Exception
{

    /**
     * InvalidHeaderLineFormat constructor.
     * @param string $line
     */
    public function __construct(string $line)
    {
        parent::__construct($line);
    }
}