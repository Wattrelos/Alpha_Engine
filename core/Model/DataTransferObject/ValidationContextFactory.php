<?php

namespace Alpha\Model\DataTransferObject;

use Alpha\Mappers\MapperFactory;
use Alpha\Mappers\EntityMappers\CustomerMapper;

/**
 * ValidationContextFactory - Centraliza a geração do array de contexto 
 * necessário para validações customizadas em DTOs.
 */
class ValidationContextFactory
{
    public function __construct(
        protected MapperFactory $mapperFactory
    ) {}

    /**
     * Cria o contexto de validação padrão da Alpha Engine.
     * 
     * @param array $extra Permite injetar dependências específicas para uma requisição.
     * @return array
     */
    public function create(array $extra = []): array
    {
        // Definimos aqui o "Core" de dependências que os DTOs costumam precisar
        $defaultContext = [
            'customer_mapper' => $this->mapperFactory->get(CustomerMapper::class),
            'mapper_factory'  => $this->mapperFactory,
            // Você pode expandir para incluir serviços de sistema se necessário:
            // 'config' => $this->config 
        ];

        return array_merge($defaultContext, $extra);
    }
}