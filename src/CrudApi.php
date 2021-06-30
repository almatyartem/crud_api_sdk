<?php

namespace ApiSdk;

use ApiSdk\Contracts\RequestProvider;
use Illuminate\Support\Facades\Log;

class CrudApi
{
    /**
     * @var RequestProvider
     */
    public $provider;

    public $api;

    public $logAll;

    /**
     * CoreApi constructor.
     * @param RequestProvider $provider
     * @param string $api
     * @param bool $logAll
     */
    public function __construct(RequestProvider $provider, string $api, bool $logAll = false)
    {
        $this->provider = $provider;
        $this->api = $api;
        $this->logAll = $logAll;
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return array|null
     * @throws RequestProviderException
     */
    public function find(string $entity, array $where = [], array $addParams = []) : ?array
    {
        $params = [];

        if($where)
        {
            $params['filter'] = $where;
        }

        $params = array_merge($params, $addParams);

        return $this->call($entity, 'get', $params);
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return array|null
     * @throws RequestProviderException
     */
    public function findFirst(string $entity, array $where = [], array $addParams = []) : ?array
    {
        $addParams['count'] = 1;

        return $this->find($entity, $where, $addParams)[0] ?? null;
    }

    /**
     * @param string $entity
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function create(string $entity, array $data) : ?array
    {
        try
        {
            return $this->call($entity, 'post', $data);
        }
        catch(RequestProviderException $exception)
        {
            if($error = $exception->getError() and isset($error['validation_errors']))
            {
                throw new \Exception(json_encode($error['validation_errors']), 666);
            }

            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param string $entity
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function massCreate(string $entity, array $data)
    {
        return $this->call('mass/'.$entity, 'post', ['data' => $data]);
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function patch(string $entity, $id, array $data) : ?array
    {
        try
        {
            return $this->call($entity.'/'.$id, 'patch', $data);
        }
        catch(RequestProviderException $exception)
        {
            if($error = $exception->getError() and isset($error['validation_errors']))
            {
                throw new \Exception(json_encode($error['validation_errors']), 666);
            }

            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $with
     * @return bool
     * @throws \Exception
     */
    public function delete(string $entity, $id, $with = []) : bool
    {
        try
        {
            $result = $this->call($entity.'/'.$id, 'delete', $with ? ['with' => $with] : []);

            return $result['success'] ?? false;
        }
        catch(RequestProviderException $exception)
        {
            if($error = $exception->getError() and isset($error['relations_exist']))
            {
                throw new \Exception(json_encode($error['relations_exist']), 666);
            }

            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param string $entity
     * @param array $fields
     * @param array $like
     * @return array|null
     */
    public function search(string $entity, array $fields, array $like) : ?array
    {
        return $this->call('search/'.$entity, 'get', ['fields' => $fields, 'like' => $like]);
    }

    /**
     * @param string $uri
     * @param string $requestMethod
     * @param array $params
     * @return array|null
     * @throws RequestProviderException
     */
    protected function call(string $uri, string $requestMethod, array $params = []) : ?array
    {
        $uri = 'crud/'.$uri.($requestMethod == 'get' ? '?'.http_build_query($params) : '');

        $result = $this->provider->request($this->api, $requestMethod, $uri, $params);

        if($this->logAll)
        {
            Log::info('Uri: '. $uri.', params: '.json_encode($params).', result: '.json_encode($result));
        }

        return $result;
    }
}
