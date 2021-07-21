<?php

namespace ApiSdk;

use RpContracts\RequestProvider;
use RpContracts\Response;

class Api
{
    /**
     * @var RequestProvider
     */
    protected RequestProvider $provider;

    /**
     * @var string
     */
    protected string $api;

    /**
     * @var bool
     */
    protected bool $throwExceptions;

    /**
     * Api constructor.
     * @param RequestProvider $provider
     * @param string $api
     * @param bool $throwExceptions
     */
    public function __construct(RequestProvider $provider, string $api, bool $throwExceptions = false)
    {
        $this->provider = $provider;
        $this->api = $api;
        $this->throwExceptions = $throwExceptions;
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return array|null
     */
    public function find(string $entity, array $where = [], array $addParams = []) : ?array
    {
        $params = [];

        if($where)
        {
            $params['filter'] = $where;
        }

        $params = array_merge($params, $addParams);

        return $this->call($entity, 'get', $params)->getContents();
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return array|null
     */
    public function findFirst(string $entity, array $where = [], array $addParams = []) : ?array
    {
        $addParams['count'] = 1;

        return $this->find($entity, $where, $addParams)[0] ?? null;
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return int|null
     */
    public function count(string $entity, array $where = [], array $addParams = []) : ?int
    {
        return ($this->find($entity, $where, array_merge($addParams, ['paginate' => 1, 'count' => 1]))['total'] ?? null);
    }

    /**
     * @param string $entity
     * @param array $data
     * @return array|null
     */
    public function create(string $entity, array $data) : ?array
    {
        return $this->call($entity, 'post', $data)->getContents();
    }

    /**
     * @param string $entity
     * @param array $data
     * @return array|null
     */
    public function massCreate(string $entity, array $data) : ?array
    {
        return $this->call('mass/'.$entity, 'post', ['data' => $data])->getContents();
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $data
     * @return array|null
     */
    public function patch(string $entity, $id, array $data) : ?array
    {
        return $this->call($entity.'/'.$id, 'patch', $data)->getContents();
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $with
     * @return bool
     */
    public function delete(string $entity, $id, $with = []) : bool
    {
        return $this->call($entity.'/'.$id, 'delete', $with ? ['with' => $with] : [])->isSuccess();
    }

    /**
     * @param string $entity
     * @param array $fields
     * @param array $like
     * @return array|null
     */
    public function search(string $entity, array $fields, array $like) : ?array
    {
        return $this->call('search/'.$entity, 'get', ['fields' => $fields, 'like' => $like])->getContents();
    }

    /**
     * @param string $uri
     * @param string $requestMethod
     * @param array $params
     * @return Response
     */
    protected function call(string $uri, string $requestMethod, array $params = []) : Response
    {
        $uri = 'crud/'.$uri.($requestMethod == 'get' ? '?'.http_build_query($params) : '');

        $response = $this->provider->request($this->api.'/'.$uri, $requestMethod, $params);

        if($this->throwExceptions and ($exception = $response->getLastException()))
        {
            throw new $exception;
        }

        return $response;
    }
}
