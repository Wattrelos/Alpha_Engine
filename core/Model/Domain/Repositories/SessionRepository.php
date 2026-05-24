<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\Entities\Session as SessionEntity;
use Alpha\Mappers\EntityMappers\SessionMapper;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * SessionRepository - Gerencia o estado e a persistência das sessões de usuário e API.
 * Implementa getMapper() e fornece os métodos específicos para o driver de sessão Alpha.
 */
class SessionRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Define o Mapper principal (System/Session) para operações de banco.
     */
    protected function getMapper()
    {
        return $this->mapperFactory->get(SessionMapper::class);
    }

    /**
     * Lê os dados da sessão, validando expiração e convertendo JSON para array.
     * 
     * @param string $session_token O identificador de transporte da sessão.
     * @return array<mixed>
     */
    public function read(string $session_token): array
    {
        $session = $this->getMapper()->getSession($session_token);

        if (!$session) return [];

        // Alpha Engine: Verificação de validade via timestamp para garantir segurança.
        if (strtotime($session->getExpireAt()) > time()) {
            return json_decode($session->getData(), true) ?: [];
        }

        return [];
    }

    /**
     * Escreve ou atualiza os dados da sessão delegando a lógica atômica ao Mapper.
     * 
     * @param string $session_token
     * @param array<mixed> $data
     * @param int $expire Tempo de vida em segundos (opcional).
     */
    public function write(string $session_token, array $data, int $expire = 0): void
    {
        // Alpha Engine: Prioriza o tempo definido pelo driver, senão usa a configuração da loja, ou o padrão do PHP.
        $expireTime = $expire ?: (int)$this->config->get('config_session_expire') ?: (int)ini_get('session.gc_maxlifetime');
        $expireDate = gmdate('Y-m-d H:i:s', time() + $expireTime);

        $this->getMapper()->saveSession($session_token, json_encode($data), $expireDate);
    }

    /**
     * Remove a sessão do armazenamento.
     */
    public function destroy(string $session_token): void
    {
        $this->getMapper()->deleteByToken($session_token);
    }

    /**
     * Garbage Collector: Remove sessões expiradas do banco de dados.
     */
    public function gc(): void
    {
        $this->getMapper()->deleteExpired();
    }

    /**
     * Atalho para vincular customer_id.
     */
    public function setCustomerId(string $token, int $customerId): void
    {
        $this->getMapper()->updateCustomerId($token, $customerId);
    }

    /**
     * Obtém o customer_id da sessão ativa.
     */
    public function getCustomerId(string $token): int
    {
        $session = $this->getMapper()->getSession($token);
        return $session ? $session->getCustomerId() : 0;
    }

    /**
     * Implementações da BaseRepositoryInterface para normalização de agregados.
     */
    public function find(int $id): ?InterfaceEntity { 
        return $this->getMapper()->findById($id); 
    }

    public function findAll(): array { 
        return $this->getMapper()->findAll(); 
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array {
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?InterfaceEntity {
        return $this->getMapper()->findOneBy($criteria);
    }
}