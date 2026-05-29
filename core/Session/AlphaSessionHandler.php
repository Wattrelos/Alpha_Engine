<?php

namespace Alpha\Session;

use Alpha\Model\Domain\Repositories\SessionRepository;

/**
 * AlphaSessionHandler - Adaptador de persistência para sessões PHP.
 * 
 * Implementa SessionHandlerInterface para integrar o ciclo de vida da sessão 
 * nativa ao motor de entidades e mappers da Alpha Engine.
 */
class AlphaSessionHandler implements \SessionHandlerInterface
{
    private SessionRepository $repository;
    private int $expire;

    public function __construct(SessionRepository $repository, int $expire = 3600)
    {
        $this->repository = $repository;
        $this->expire = $expire;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        return $this->repository->readRaw($id);
    }

    public function write(string $id, string $data): bool
    {
        $this->repository->writeRaw($id, $data, $this->expire);
        return true;
    }

    public function destroy(string $id): bool
    {
        $this->repository->destroy($id);
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return $this->repository->gc();
    }
}