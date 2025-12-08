<?php

namespace App\Exceptions;

use Exception;

class MoneroRpcException extends Exception
{
    protected array $rpcDetails;

    public function __construct(string $message = "", int $code = 0, array $rpcDetails = [])
    {
        parent::__construct($message, $code);
        $this->rpcDetails = $rpcDetails;
    }

    public function getRpcDetails(): array
    {
        return $this->rpcDetails;
    }

    public function getDetailedMessage(): string
    {
        $details = $this->getRpcDetails();
        
        $message = $this->getMessage();
        
        if (!empty($details)) {
            $message .= " | RPC Details: " . json_encode($details);
        }
        
        return $message;
    }
}
