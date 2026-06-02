<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\UploadMapper;
use Alpha\Model\Domain\Entities\Upload;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * UploadRepository
 * Gerencia os arquivos enviados pelos clientes para a loja.
 */
class UploadRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): UploadMapper
    {
        return $this->mapperFactory->get(UploadMapper::class);
    }

    /**
     * Adiciona um novo registro de upload no banco de dados e retorna o token de acesso.
     *
     * @param string $name
     * @param string $filename
     * @return string
     */
    public function addUpload(string $name, string $filename): string
    {
        $code = oc_token(32);
        
        $upload = new Upload();
        $upload->setName($name);
        $upload->setFilename($filename);
        $upload->setCode($code);
        $upload->setDateAdded(date('Y-m-d H:i:s'));

        $this->getMapper()->save($upload);

        return $code;
    }

    /**
     * Busca um upload pelo seu código hash.
     *
     * @param string $code
     * @return Upload|null
     */
    public function findByCode(string $code): ?Upload
    {
        return $this->getMapper()->findByCode($code);
    }

    /**
     * Legacy Bridge: Retorna o array de dados bruto compatível com o legado.
     *
     * @param string $code
     * @return array
     */
    public function getUploadByCode(string $code): array
    {
        $upload = $this->findByCode($code);
        if (!$upload) {
            return [];
        }

        return [
            'upload_id'  => $upload->getId(),
            'name'       => $upload->getName(),
            'filename'   => $upload->getFilename(),
            'code'       => $upload->getCode(),
            'date_added' => $upload->getDateAdded()
        ];
    }

    // --- BaseRepositoryInterface bindings ---

    public function find(int $id): ?InterfaceEntity
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

    public function findOneBy(array $criteria): ?InterfaceEntity
    {
        $results = $this->getMapper()->search($criteria);
        return $results[0] ?? null;
    }
}
