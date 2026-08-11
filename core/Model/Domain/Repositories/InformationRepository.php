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
     * Retorna os dados consolidados da página institucional para exibição na View.
     */
    public function getInformationDisplayData(int $informationId): ?\Alpha\Model\DataTransferObject\ViewResponse
    {
        $collection = $this->getInformationData($informationId);
        if (!$collection) {
            return null;
        }

        $response = new \Alpha\Model\DataTransferObject\ViewResponse();
        foreach ($collection->toArray() as $key => $value) {
            $response->set($key, $value);
        }

        return $response;
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

    /**
     * Valida os dados do formulário de contato.
     */
    public function validateContactForm(array $postData): array
    {
        $errors = [];
        if (empty($postData['name']) || mb_strlen($postData['name']) < 3 || mb_strlen($postData['name']) > 32) {
            $errors['name'] = 'O nome deve ter entre 3 e 32 caracteres!';
        }
        if (empty($postData['email']) || !filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'O endereço de e-mail não parece ser válido!';
        }
        if (empty($postData['enquiry']) || mb_strlen($postData['enquiry']) < 10 || mb_strlen($postData['enquiry']) > 3000) {
            $errors['enquiry'] = 'A mensagem deve ter entre 10 e 3000 caracteres!';
        }
        return $errors;
    }

    /**
     * Envia o contato/mensagem (Enquiry).
     */
    public function sendEnquiry(array $postData): void
    {
        // Envio real ou persistência de mensagens pode ser feito aqui futuramente.
    }

    /**
     * Retorna todas as páginas institucionais (ativas e inativas) para gestão no painel de controle.
     */
    public function getAllInformationsAdmin(): array
    {
        /** @var InformationMapper $mapper */
        $mapper = $this->getMapper();
        return $mapper->getAllInformationsAdmin($this->language_id, $this->store_id);
    }

    /**
     * Busca os dados de uma página específica no painel admin (independente do status).
     */
    public function getInformationForAdmin(int $informationId): array
    {
        /** @var InformationMapper $mapper */
        $mapper = $this->getMapper();
        return $mapper->getInformationForAdmin($informationId, $this->language_id, $this->store_id);
    }

    /**
     * Atualiza uma página institucional no banco de dados.
     */
    public function saveInformationPage(int $informationId, array $data): bool
    {
        /** @var InformationMapper $mapper */
        $mapper = $this->getMapper();
        return $mapper->updateInformation($informationId, $data, $this->language_id, $this->store_id);
    }

    /**
     * Cria uma nova página institucional no banco de dados.
     */
    public function createInformationPage(array $data): int
    {
        /** @var InformationMapper $mapper */
        $mapper = $this->getMapper();
        return $mapper->createInformation($data, $this->language_id, $this->store_id);
    }

    /**
     * Exclui uma página institucional personalizada.
     */
    public function deleteInformationPage(int $informationId): bool
    {
        /** @var InformationMapper $mapper */
        $mapper = $this->getMapper();
        return $mapper->deleteInformation($informationId);
    }

    /**
     * Retorna as configurações de contato formatadas e cacheadas no formato camelCase (i18next).
     */
    public function getContactSettings(): array
    {
        if ($this->container && $this->container->has(SettingRepository::class)) {
            $settingRepository = $this->container->get(SettingRepository::class);
        } else {
            $settingRepository = new SettingRepository($this->mapperFactory, $this->container);
        }

        $rawSettings = $settingRepository->getSetting('config', $this->store_id);
        $storeSettings = new \Alpha\Support\StoreSettings($rawSettings, $this->language_id);
        return $storeSettings->getFormattedSettings();
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
