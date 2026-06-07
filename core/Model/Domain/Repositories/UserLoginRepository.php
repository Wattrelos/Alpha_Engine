<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\UserLoginMapper;

/**
 * UserLoginRepository
 * Gerencia os históricos e restrições de tentativa de login no Backoffice.
 */
class UserLoginRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected string $mapperClass = UserLoginMapper::class;

    protected function getMapper(): UserLoginMapper
    {
        return $this->mapperFactory->get(UserLoginMapper::class);
    }

    /**
     * Retorna a quantidade total de logins recentes para prevenir ataques de força bruta.
     *
     * @param string $username
     * @return int
     */
    public function countRecentLogins(string $username): int
    {
        $logins = $this->getMapper()->search(['username' => $username]);
        return count($logins);
    }

    /**
     * Limpa o histórico de login após um acesso bem-sucedido ou expiração do limite de tempo.
     *
     * @param string $username
     */
    public function clearLoginAttempts(string $username): void
    {
        // Alpha Engine: Dívida técnica resolvida. O Repositório assume a deleção de histórico via Mapper.
        $logins = $this->getMapper()->search(['username' => $username]);
        foreach ($logins as $login) {
            if ($login instanceof \Alpha\Model\Domain\InterfaceEntity) {
                $this->getMapper()->delete($login->getId());
            }
        }
    }

    // BaseRepositoryInterface bindings
    public function find(int $id): ?\Alpha\Model\Domain\InterfaceEntity
    {
        return $this->getMapper()->findById($id);
    }

    public function findAll(): array
    {
        return $this->getMapper()->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getMapper()->search($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?\Alpha\Model\Domain\InterfaceEntity
    {
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }
}
