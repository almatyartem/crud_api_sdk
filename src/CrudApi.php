<?php

namespace ApiSdk;

use ApiSdk\Contracts\RequestProvider;

class CrudApi
{
    /**
     * @var RequestProvider
     */
    public $provider;

    public $api;

    /**
     * CoreApi constructor.
     * @param RequestProvider $provider
     * @param null $api
     */
    public function __construct(RequestProvider $provider, $api = null)
    {
        $this->provider = $provider;
        $this->api = $api ?? 'core';
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return array|null
     * @throws RequestProviderException
     */
    public function unsafeFind(string $entity, array $where = [], array $addParams = []) : ?array
    {
        $params = [];

        if($where)
        {
            $params['filter'] = $where;
        }

        $params = array_merge($params, $addParams);

        return $this->call($entity, 'all', null, $params);
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return array|null
     */
    public function safeFind(string $entity, array $where = [], array $addParams = []) : ?array
    {
        try
        {
            return $this->unsafeFind($entity, $where, $addParams);
        }
        catch(RequestProviderException $exception)
        {
            return null;
        }
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return array|null
     */
    public function find(string $entity, array $where = [], array $addParams = []) : ?array
    {
        return $this->safeFind($entity, $where, $addParams);
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return array|null
     * @throws RequestProviderException
     */
    public function unsafeFindFirst(string $entity, array $where = [], array $addParams = []) : ?array
    {
        $addParams['count'] = 1;

        return $this->unsafeFind($entity, $where, $addParams)[0] ?? null;
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return array|null
     */
    public function safeFindFirst(string $entity, array $where = [], array $addParams = []) : ?array
    {
        try
        {
            return $this->unsafeFindFirst($entity, $where, $addParams);
        }
        catch(RequestProviderException $exception)
        {
            return null;
        }
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return array|null
     */
    public function findFirst(string $entity, array $where = [], array $addParams = []) : ?array
    {
        return $this->safeFindFirst($entity, $where, $addParams);
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
            return $this->unsafeCreate($entity, $data);
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
    public function safeCreate(string $entity, array $data) : ?array
    {
        try
        {
            return $this->unsafeCreate($entity, $data);
        }
        catch(RequestProviderException $exception)
        {
            return null;
        }
    }

    /**
     * @param string $entity
     * @param array $data
     * @return array|null
     * @throws RequestProviderException
     */
    public function unsafeCreate(string $entity, array $data) : ?array
    {
        return $this->call($entity, 'create', null, $data);
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
            return $this->unsafePatch($entity, $id, $data);
        }
        catch(RequestProviderException $exception)
        {
            if($error = $exception->getError() and isset($error['validation_errors']))
            {
                throw new \Exception(json_encode($error['validation_errors']), 666);
            }

            return null;
        }
    }

    /**
     * @param  string $entity
     * @param $id
     * @param string $field
     * @return array|null
     */
    public function increment(string $entity, $id, string $field): ?array
    {
        try
        {
            return $this->call($entity, 'increment', $id.'/'.$field);
        }
        catch(RequestProviderException $exception){}

        return null;
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $data
     * @return array|null
     */
    public function safePatch(string $entity, $id, array $data) : ?array
    {
        try
        {
            return $this->unsafePatch($entity, $id, $data);
        }
        catch(RequestProviderException $exception)
        {
            return null;
        }
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $data
     * @return array|null
     * @throws RequestProviderException
     */
    public function unsafePatch(string $entity, $id, array $data) : ?array
    {
        return $this->call($entity, 'patch', $id, $data);
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $with
     * @return array|null
     */
    public function show(string $entity, $id, $with = []) : ?array
    {
        return $this->safeShow($entity, $id, $with);
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $with
     * @return array|null
     */
    public function safeShow(string $entity, $id, $with = []) : ?array
    {
        try
        {
            return $this->unsafeShow($entity, $id, $with);
        }
        catch(RequestProviderException $exception)
        {
            return null;
        }
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $with
     * @return array|null
     * @throws RequestProviderException
     */
    public function unsafeShow(string $entity, $id, $with = []) : ?array
    {
        return $this->call($entity, 'show', $id, $with ? ['with' => $with] : []);
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
            return $this->unsafeDelete($entity, $id, $with);
        }
        catch(RequestProviderException $exception)
        {
            if($error = $exception->getError() and isset($error['relations_exist']))
            {
                throw new \Exception(json_encode($error['relations_exist']), 666);
            }

            return false;
        }
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $with
     * @return bool
     */
    public function safeDelete(string $entity, $id, $with = []) : bool
    {
        try
        {
            return $this->unsafeDelete($entity, $id, $with);
        }
        catch(RequestProviderException $exception)
        {
            return false;
        }
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $with
     * @return bool
     * @throws RequestProviderException
     */
    public function unsafeDelete(string $entity, $id, $with = []) : bool
    {
        $result = $this->call($entity, 'delete', $id, $with ? ['with' => $with] : []);

        return $result['success'] ?? false;
    }

    /**
     * @param string $entity
     * @param string $method
     * @param null $id
     * @param array $params
     * @return array|null
     * @throws RequestProviderException
     */
    protected function call(string $entity, string $method, $id = null, array $params = []) : ?array
    {
        $getParams = [];

        switch($method)
        {
            case 'create':
                $requestMethod = 'post';
                break;
            case 'delete':
                $requestMethod = 'delete';
                break;
            case 'patch':
            case 'increment':
                $requestMethod = 'patch';
                break;
            default:
                $requestMethod = 'get';
                $getParams = $params;
        }


        $uri = 'crud/'.$entity.($id ? '/'.$id : '').($getParams ? '?'.http_build_query($getParams) : '');

        return $this->provider->request($this->api, $requestMethod, $uri, $params);
    }
}
