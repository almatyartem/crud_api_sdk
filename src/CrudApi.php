<?php

namespace CrudApiSdk;

use RpContracts\RequestProvider;

class CrudApi extends Api
{
    /**
     * CrudApi constructor.
     * @param RequestProvider $provider
     * @param string $api
     * @param bool $throwExceptions
     */
    public function __construct(RequestProvider $provider, bool $throwExceptions = true)
    {
        parent::__construct($provider, $throwExceptions);
    }
}
