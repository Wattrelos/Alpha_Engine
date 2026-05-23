<?php
namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\HomeMapper;
use Alpha\Mappers\EntityMappers\ProductMapper;
use Alpha\Support\Collection;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * Repositório especializado em vitrines e lógica de Home Page
 */
class HomeRepository extends AbstractRepository implements BaseRepositoryInterface {
    
    protected function getMapper() {
        return $this->mapperFactory->get(ProductMapper::class);
    }

    /**
     * Alpha Engine: Consolida os dados fundamentais para a Home Page.
     * Isola a lógica de SEO e metadados que antes ficava no controlador legado.
     */
    public function getHomeData(): Collection
    {
        /** @var HomeMapper $homeMapper */
        $homeMapper = $this->mapperFactory->get(HomeMapper::class);
        $metadata = $homeMapper->getHomeMetadata($this->store_id);

        // --- TESTE REAL-TIME DO LOADCONFIG DA ALPHA ENGINE ---
        // Carrega o arquivo system/config/alpha.php
        $this->loadConfig('alpha');
        // Grava no log de erros do OpenCart (system/storage/logs/error.log) para comprovar a injeção na $this->config global
        $this->registry->get('log')->write('[TESTE ALPHA] ' . $this->config->get('alpha_engine_status'));
        // ------------------------------------------------------

        // Configuração de metadados via Document (Domain Logic)
        $this->document->setTitle($metadata['config_meta_title'] ?? $this->config->get('config_name'));
        $this->document->setDescription($metadata['config_meta_description'] ?? '');
        $this->document->setKeywords($metadata['config_meta_keyword'] ?? '');

        return new Collection([
            'title'       => $this->document->getTitle(),
            'description' => $this->document->getDescription(),
            'keywords'    => $this->document->getKeywords()
        ]);
    }

    // Implementações obrigatórias da BaseRepositoryInterface
    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}