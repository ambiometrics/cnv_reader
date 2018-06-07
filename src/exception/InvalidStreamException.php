<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 04-06-18
 * Time: 16:55
 */

namespace edwrodrig\cnv_parser\exception;


use Exception;

class InvalidStreamException extends Exception
{

    /**
     * InvalidStreamException constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}