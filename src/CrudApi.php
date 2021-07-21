<?php

namespace ApiSdk;

use RpContracts\RequestProvider;

class CrudApi extends Api
{
    /**
     * CrudApi constructor.
     * @param RequestProvider $provider
     * @param string $api
     * @param bool $throwExceptions
     */
    public function __construct(RequestProvider $provider, string $api, bool $throwExceptions = true)
    {
        parent::__construct($provider, $api, $throwExceptions);
    }
}
