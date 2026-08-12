<?php

namespace Alpha\Model\Domain\Factory;

use Alpha\Model\Domain\InterfaceEntity;
use Exception;
use RuntimeException;

/**
 * Refere-se a SimpleObjectFactory.java
 * Adaptado para PHP 8.4.16
 */
abstract class SimpleObjectFactory
{
    /**
     * Instancia qualquer classe pelo nome, desde que implemente InterfaceEntity.
     * 
     * @param string $fullClassName Nome simples da classe (ex: "Cliente")
     * @return InterfaceEntity
     * @throws RuntimeException
     */
    public static function create(string $fullClassName): InterfaceEntity
    {
        try {
            // 1. Resolve o Namespace completo
            // No PHP, convertemos o ponto (padrão Java no AppConfig) para contra-barra (Namespace PHP)
            $baseNamespace = str_replace('.', '\\', ENTITIES_PATH);
            $targetClass = $baseNamespace . '\\' . $fullClassName;

            // 2. Verifica se a classe existe no sistema de Autoload
            if (!class_exists($targetClass)) {
                throw new RuntimeException("Classe não encontrada: " . $targetClass);
            }

            // 3. Valida se a classe é compatível com InterfaceEntity (Equivalente ao isAssignableFrom)
            if (is_subclass_of($targetClass, InterfaceEntity::class)) {
                // 4. Cria a instância dinamicamente
                return new $targetClass();
            } else {
                throw new RuntimeException("A classe " . $fullClassName . " não é uma InterfaceEntity válida.");
            }
        } catch (Exception $e) {
            throw new RuntimeException("Falha ao instanciar objeto: " . $fullClassName, 0, $e);
        }
    }
}
