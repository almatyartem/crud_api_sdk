<?php

namespace ApiSdk;

use RpContracts\RequestProvider;

class SafeLogErrorsCrudApi extends CrudApi
{
    /**
     * CoreApi constructor.
     * @param RequestProvider $provider
     * @param string $api
     */
    public function __construct(RequestProvider $provider, string $api)
    {
        parent::__construct($provider, $api, false);
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return array|null
     */
    public function safeFind(string $entity, array $where = [], array $addParams = []) : ?array
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
    public function safeFindFirst(string $entity, array $where = [], array $addParams = []) : ?array
    {
        $addParams['count'] = 1;

        return $this->safeFind($entity, $where, $addParams)[0] ?? null;
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $addParams
     * @return int|null
     */
    public function safeCount(string $entity, array $where = [], array $addParams = []) : ?int
    {
        return ($this->safeFind($entity, $where, array_merge($addParams, ['paginate' => 1, 'count' => 1]))['total'] ?? null);
    }

    /**
     * @param string $entity
     * @param array $data
     * @return array|null
     */
    public function safeCreate(string $entity, array $data) : ?array
    {
        return $this->call($entity, 'post', $data)->getContents();
    }

    /**
     * @param string $entity
     * @param array $data
     * @return array|null
     */
    public function safeMassCreate(string $entity, array $data) : ?array
    {
        return $this->call('mass/'.$entity, 'post', ['data' => $data])->getContents();
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $data
     * @return array|null
     */
    public function safePatch(string $entity, $id, array $data) : ?array
    {
        return $this->call($entity.'/'.$id, 'patch', $data)->getContents();
    }

    /**
     * @param string $entity
     * @param $id
     * @param array $with
     * @return bool
     */
    public function safeDelete(string $entity, $id, $with = []) : bool
    {
        return $this->call($entity.'/'.$id, 'delete', $with ? ['with' => $with] : [])->isSuccess();
    }

    /**
     * @param string $entity
     * @param array $fields
     * @param array $like
     * @return array|null
     */
    public function safeSearch(string $entity, array $fields, array $like) : ?array
    {
        return $this->call('search/'.$entity, 'get', ['fields' => $fields, 'like' => $like])->getContents();
    }
}
