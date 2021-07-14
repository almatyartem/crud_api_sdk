<?php

namespace ApiSdk;

use Illuminate\Support\Facades\Log;
use Throwable;

class SafeLogErrorsCrudApi extends CrudApi
{
    /**
     * @param string $entity
     * @param $id
     * @param array $data
     * @return array|null
     */
    public function safePatch(string $entity, int $id, array $data) : ?array
    {
        try
        {
            return $this->patch($entity, $id, $data);
        }
        catch (\Throwable $e)
        {
            $this->errorHandle($e, ['action' => 'patch', 'entity' => $entity, 'id' => $id, 'data' => $data]);

            return null;
        }
    }

    /**
     * @param string $entity
     * @param int $id
     * @param array $with
     * @return bool
     */
    public function safeDelete(string $entity, int $id, array $with = []) : bool
    {
        try
        {
            return $this->delete($entity, $id, $with);
        }
        catch (\Throwable $e)
        {
            if(!strpos($e->getMessage(),'404 Not Found'))
            {
                $this->errorHandle($e, ['action' => 'delete', 'entity' => $entity, 'id' => $id]);
            }

            return false;
        }
    }

    /**
     * @param string $entity
     * @param array $data
     * @return array|null
     */
    public function safeCreate(string $entity, array $data) : ?array
    {
        try
        {
            return $this->create($entity, $data);
        }
        catch (\Throwable $e)
        {
            $this->errorHandle($e, ['action' => 'create', 'entity' => $entity, 'data' => $data]);

            return null;
        }
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
            return $this->findFirst($entity, $where, $addParams);
        }
        catch (\Throwable $e)
        {
            $this->errorHandle($e, ['action' => 'find first', 'entity' => $entity, 'where' => $where, 'addParams' => $addParams]);

            return null;
        }
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
            return $this->find($entity, $where, $addParams);
        }
        catch (\Throwable $e)
        {
            $this->errorHandle($e, ['action' => 'find', 'entity' => $entity, 'where' => $where, 'addParams' => $addParams]);

            return null;
        }
    }

    /**
     * @param Throwable $e
     * @param array $context
     */
    protected function errorHandle(Throwable $e, array $context = [])
    {
        Log::error('['.$e->getCode().'] "'.$e->getMessage().$e->getTrace()[0]['file'].':'.$e->getTrace()[0]['line']."\r\n", $context);
    }
}
