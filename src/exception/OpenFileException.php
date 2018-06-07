<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 07-06-18
 * Time: 17:26
 */

namespace edwrodrig\cnv_parser\exception;


use Exception;

class OpenFileException extends Exception
{

    /**
     * OpenFileException constructor.
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        parent::__construct($filename);
    }
}