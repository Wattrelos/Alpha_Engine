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
        $now = gmdate('Y-m-d H:i:s');
        $data = $this->getMapper()->getActiveSessionData($session_token, $now);

        if ($data) {
            return json_decode($data, true) ?: [];
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
        $configExpire = 0;
        if (is_object($this->config) && method_exists($this->config, 'get')) {
            $configExpire = (int)$this->config->get('config_session_expire');
        }
        $expireTime = $expire ?: $configExpire ?: (int)ini_get('session.gc_maxlifetime');
        $expireDate = gmdate('Y-m-d H:i:s', time() + $expireTime);

        $this->getMapper()->saveSession($session_token, json_encode($data), $expireDate);
    }

    /**
     * Lê os dados brutos da sessão em string (sem decodificar JSON).
     * 
     * @param string $session_token
     * @return string
     */
    public function readRaw(string $session_token): string
    {
        $now = gmdate('Y-m-d H:i:s');
        return $this->getMapper()->getActiveSessionData($session_token, $now) ?: '';
    }

    /**
     * Escreve dados brutos da sessão na tabela.
     * 
     * @param string $session_token
     * @param string $data
     * @param int $expire
     */
    public function writeRaw(string $session_token, string $data, int $expire = 0): void
    {
        $configExpire = 0;
        if (is_object($this->config) && method_exists($this->config, 'get')) {
            $configExpire = (int)$this->config->get('config_session_expire');
        }
        $expireTime = $expire ?: $configExpire ?: (int)ini_get('session.gc_maxlifetime');
        $expireDate = gmdate('Y-m-d H:i:s', time() + $expireTime);

        $this->getMapper()->saveSession($session_token, $data, $expireDate);
    }

    /**
     * Remove a sessão do armazenamento.
     */
    public function destroy(string $session_token): void
    {
        $this->getMapper()->deleteByToken($session_token);
    }

    /**
     * Garbage Collector: Remove sessões expiradas do banco de dados e retorna o total removido.
     */
    public function gc(): int
    {
        return $this->getMapper()->deleteExpired();
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
