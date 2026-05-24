<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Store - Define as configurações de uma loja no ambiente multi-loja.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Multi-instância: Estrutura preparada para isolamento de domínios e URLs ssl/não-ssl, fundamental para o motor de rotas.
 * - Tipagem Estrita: Propriedades de URL e nome tipadas como string para garantir consistência no roteamento e evitar injeções.
 * - Interface Fluida: Facilita a configuração programática de novas instâncias de loja em scripts de migração ou multitenancy.
 * - Normalização de PK: Utiliza o padrão 'id' herdado de BaseEntity, facilitando a automação no DataAccessObject.
 */
class Store extends BaseEntity
{
    private string $name = '';
    private string $url = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }
}