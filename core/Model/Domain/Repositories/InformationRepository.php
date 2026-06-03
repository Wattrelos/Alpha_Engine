<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\InformationMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Support\Collection;

/**
 * InformationRepository - Gerencia a lógica de páginas institucionais e formulários de contato.
 * 
 * Melhoras Alpha Engine:
 * - Encapsulamento de Validação: Isola as regras de negócio do formulário de contato.
 * - Centralização de Domínio: Gerencia a exibição e integridade das páginas informativas.
 */
class InformationRepository extends AbstractRepository implements BaseRepositoryInterface
{
    /**
     * Define o Mapper principal (Catalog/Information)
     */
    protected function getMapper()
    {
        return $this->mapperFactory->get(InformationMapper::class);
    }

    /**
     * Recupera os dados hidratados de uma página de informação específica.
     * 
     * @param int $informationId
     * @return Collection|null
     */
    public function getInformationData(int $informationId): ?Collection
    {
        /** @var InformationMapper $informationMapper */
        $informationMapper = $this->mapperFactory->get(InformationMapper::class);
        
        $information = $informationMapper->getInformation($informationId, $this->language_id, $this->store_id);

        if ($information) {
            return new Collection($information);
        }

        return null;
    }

    /**
     * Legacy Bridge: Compatibilidade com Controladores Legados.
     * Retorna a página de informação formatada como Array bruto.
     */
    public function getInformation(int $informationId): array
    {
        $information = $this->getMapper()->getInformation($informationId, $this->language_id, $this->store_id);
        return $information ?: [];
    }

    // BaseRepositoryInterface bindings

    /**
     * Busca uma entidade pelo seu ID principal.
     */
    public function find(int $id): ?InterfaceEntity {
        return $this->getMapper()->findById($id);
    }

    /**
     * Retorna todas as entidades deste domínio.
     */
    public function findAll(): array {
        return $this->getMapper()->findAll();
    }

    /**
     * Busca entidades através de critérios específicos.
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array {
        return $this->getMapper()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Retorna a primeira entidade que satisfaça o critério informado.
     */
    public function findOneBy(array $criteria): ?InterfaceEntity {
        return $this->getMapper()->findOneBy($criteria);
    }
}
