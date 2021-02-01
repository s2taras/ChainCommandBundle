<?php

namespace App\ChainCommandBundle\Exception;

use Exception;

/**
 * Custom exception for commands registered as a member
 *
 * Class MemberException
 * @package App\ChainCommandBundle\Exception
 */
class MemberException extends Exception
{
    /**
     * Exception message
     * @var string
     */
    protected $message = 'Is a member of %s command chain and cannot be executed on its own';

    /**
     * Override message
     *
     * {@inheritdoc}
     */
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct(sprintf($this->message, $message), $code, $previous);
    }
}
