<?php

namespace CrudApiSdk;

use RpContracts\Cache;
use RpContracts\RequestProvider;
use RpContracts\Response;

class Api
{
    /**
     * @var RequestProvider
     */
    protected RequestProvider $provider;

    /**
     * @var bool
     */
    protected bool $throwExceptions;

    /**
     * Api constructor.
     * @param RequestProvider $provider
     * @param bool $throwExceptions
     */
    public function __construct(RequestProvider $provider, bool $throwExceptions = false)
    {
        $this->provider = $provider;
        $this->throwExceptions = $throwExceptions;
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @param int|null $cacheTtl
     * @return array|null
     */
    public function find(string $entity, array $where = [], array $addParams = [], int $cacheTtl = null) : ?array
    {
        $params = [];

        if($where)
        {
            $params['filter'] = $where;
        }

        $params = array_merge($params, $addParams);

        $response = $this->call($entity, 'get', $params, [], false, $cacheTtl);

        if($response->isSuccess())
        {
            return $response->getContents();
        }

        return null;
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @param int $chunkSize
     * @return array
     */
    public function findByChunks(string $entity, array $where = [], array $addParams = [], int $chunkSize = 10000) : array
    {
        $result = [];

        $addParams['paginate'] = 1;
        $addParams['page'] = 1;
        $addParams['count'] = $chunkSize;

        do
        {
            $chunk = $this->find($entity, $where, $addParams);
            $addParams['page'] = ($addParams['page'] + 1);
            $result = array_merge($result, $chunk['data'] ?? []);
        }
        while($chunk['next_page_url'] ?? null);

        return $result;
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @param int|null $cacheTtl
     * @return array|null
     */
    public function findFirst(string $entity, array $where = [], array $addParams = [], int $cacheTtl = null) : ?array
    {
        $addParams['count'] = 1;

        return $this->find($entity, $where, $addParams, $cacheTtl)[0] ?? null;
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
     * @param array $unique
     * @param array $data
     * @return array|null
     * @throws \Throwable
     */
    public function createOrUpdate(string $entity, array $unique, array $data) : ?array
    {
        return $this->call($entity, 'post', ['unique' => $unique,'data' => $data])->getContents();
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
     * @param string $field
     * @param string $value
     * @return array|null
     * @throws \Throwable
     */
    public function massDelete(string $entity, string $field, string $value) : ?array
    {
        return $this->call($entity, 'delete', ['field' => $field, 'value' => $value])->getContents();
    }

    /**
     * @param string $entity
     * @param array $conditions
     * @return array|null
     * @throws \Throwable
     */
    public function massDeleteByConditions(string $entity, array $conditions) : ?array
    {
        return $this->call($entity, 'delete', $conditions)->getContents();
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
     * @param string $uri
     * @param string $requestMethod
     * @param array $params
     * @param array $addHeaders
     * @param bool $postAsForm
     * @param int|null $cacheTtl
     * @return Response
     * @throws \Throwable
     */
    protected function call(string $uri,
                            string $requestMethod,
                            array $params = [],
                            array $addHeaders = [],
                            bool $postAsForm = false,
                            int $cacheTtl = null) : Response
    {
        $uri = 'crud/'.$uri.($requestMethod == 'get' ? '?'.http_build_query($params) : '');

        $response = $this->provider->request($uri, $requestMethod, $params, $addHeaders, $postAsForm, $cacheTtl);

        if($this->throwExceptions and ($exception = $response->getLastException()))
        {
            throw $exception;
        }

        return $response;
    }
}
