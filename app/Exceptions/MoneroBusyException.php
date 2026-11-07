<?php

namespace App\Exceptions;

class MoneroBusyException extends MoneroRpcException
{
    public function __construct(int $waitedSeconds, array $rpcDetails = [])
    {
        parent::__construct(
            'The Monero wallet service is busy right now. Please try again in a minute.',
            0,
            $rpcDetails + ['waited_seconds' => $waitedSeconds]
        );
    }
}
