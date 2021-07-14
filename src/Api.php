<?php

namespace GamesApiSdk;

use GuzzleWrapper\RequestProvider;
use LoggingRequestProvider\Logger;

class Api
{
    /**
     * @var RequestProvider
     */
    public RequestProvider $provider;

    /**
     * @var bool
     */
    public bool $logAll;

    /**
     * CoreApi constructor.
     * @param RequestProvider $provider
     * @param string $api
     * @param bool $logAll
     */
    public function __construct(
        RequestProvider $provider,
        string $api,
        bool $logAll = false
    )
    {
        $this->provider = new RequestProvider($api, 1, 1,
            new Logger($logAll ? Logger::STRATEGY_DEBUG : Logger::STRATEGY_LOG_EXCEPTIONS));
        $this->logAll = $logAll;
    }

    /**
     * @param string $platform
     * @param string $code
     * @param string|null $language
     * @return array|null
     */
    public function getGameData(string $platform, string $code, string $language = null) : ?array
    {
        return $this->provider->request($platform.'/game/'.$code.($language ? '/'.$language : ''))->getContents();
    }

    /**
     * @param string $platform
     * @param string $search
     * @return array|null
     */
    public function searchByTitle(string $platform, string $search) : ?array
    {
        return $this->provider->request($platform.'/search_by_title/'.urlencode($search))->getContents();
    }
}
