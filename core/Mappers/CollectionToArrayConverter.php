<?php

namespace Alpha\Mappers;



use Alpha\Model\Domain\InterfaceEntity;
use ReflectionClass;

/**
 * Classe responsável por converter coleções de Entidades ou Entidades únicas
 * em arrays associativos compatíveis com as Views (Twig).
 */
class CollectionToArrayConverter
{
    /**
     * Converte uma coleção (array) de objetos InterfaceEntity em um array de arrays.
     *
     * @param array $collection
     * @param array $visited
     * @return array
     */
    public static function convertCollection(array $collection, array &$visited = []): array
    {
        $result = [];
        foreach ($collection as $item) {
            if ($item instanceof InterfaceEntity) {
                $result[] = self::convertEntity($item, $visited);
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * Converte uma única Entidade em um array associativo.
     *
     * @param InterfaceEntity $entity
     * @param array $visited
     * @return array
     */
    public static function convertEntity(InterfaceEntity $entity, array &$visited = []): array
    {
        $oid = spl_object_hash($entity);
        if (isset($visited[$oid])) {
            return ['id' => $entity->getId(), '_cyclic' => true];
        }
        $visited[$oid] = true;

        $data = [];
        $reflection = new ReflectionClass($entity);

        // Inclui o ID definido na BaseEntity
        $data['id'] = $entity->getId();

        // Itera sobre as propriedades da classe e de suas classes pais
        $properties = $reflection->getProperties();
        
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $name = $property->getName();

            // Segurança PHP 8+: Ignora propriedades não inicializadas para evitar Fatal Errors
            if (!$property->isInitialized($entity)) {
                continue;
            }

            $value = $property->getValue($entity);

            // Converte camelCase (POO) para snake_case
            $key = strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $name));

            $data[$key] = self::convertValue($value, $visited);

            // Se for uma associação (Objeto Entidade), também adicionamos o sufixo _id para compatibilidade legada
            if ($value instanceof InterfaceEntity) {
                $data[$key . '_id'] = $value->getId();
            }
        }

        unset($visited[$oid]);
        return $data;
    }

    /**
     * Trata o valor recursivamente se for outra entidade ou uma lista.
     */
    private static function convertValue(mixed $value, array &$visited = []): mixed
    {
        return match (true) {
            $value instanceof InterfaceEntity => self::convertEntity($value, $visited),
            is_iterable($value) => self::convertCollection(is_array($value) ? $value : iterator_to_array($value), $visited),
            $value instanceof \JsonSerializable => $value->jsonSerialize(),
            default => $value,
        };
    }
}
