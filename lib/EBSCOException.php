<?php

/**
 * EBSCOException class.
 */
class EBSCOException extends Exception {
    const CRITICAL_ERROR = 1;

   /**
     * EBSCOException constructor.
     * @param string $message
     * @param int $code
     * @param Exception|NULL $previous
     */
    public function __construct($message, $code = self::CRITICAL_ERROR, Exception $previous = NULL) {
        parent::__construct($message, $code, $previous);
    }

}
