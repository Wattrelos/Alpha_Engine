<?php

namespace Alpha\Model\DataTransferObject;

use Alpha\Model\DataTransferObject\Attributes\Validation;
use ReflectionClass;
use ReflectionMethod;
use Exception;
use DateTime;

/**
 * Refere-se a EntityMapper.java
 * Adaptado para PHP 8.4.16
 * 
 * Esta classe é responsável por mapear dados de um array associativo (geralmente vindo do request)
 * para os atributos de uma instância de entidade, utilizando métodos setters e conversão automática de tipos.
 */
class EntityMapper
{
    /**
     * Preenche a entidade com base nos parâmetros do request com conversão de tipos.
     * No PHP, o equivalente ao HttpServletRequest costuma ser tratado como um array associativo ($_POST ou $_GET).
     *
     * @param object $entity A instância da classe de domínio a ser preenchida.
     * @param array $request O conjunto de dados chave => valor.
     * @return object A própria entidade com os valores injetados.
     */
    public static function fillEntity(object $entity, array $request): object
    {
        $reflection = new ReflectionClass($entity);
        // Obtém apenas métodos públicos (equivalente ao getMethods do Java)
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodName = $method->getName();

            // Focamos nos setters com exatamente 1 parâmetro
            if (str_starts_with($methodName, 'set') && $method->getNumberOfParameters() === 1) {
                
                $propertyName = substr($methodName, 3);
                // Transforma setNomeCompleto -> nomeCompleto
                $fieldName = lcfirst($propertyName);
                
                // Alpha Engine (Anti-Corruption Layer): Transforma camelCase para snake_case (OpenCart Legacy)
                // ex: customerGroupId -> customer_group_id
                $snakeCaseField = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $fieldName));
                
                // Prioriza o padrão camelCase, com fallback elegante para o padrão do OpenCart
                $requestKey = null;
                if (isset($request[$fieldName])) {
                    $requestKey = $fieldName;
                                } elseif (isset($request[$snakeCaseField])) {
                    $requestKey = $snakeCaseField;
                }

                if ($requestKey !== null) {
                    $paramValue = $request[$requestKey];
                    $isEmpty = is_scalar($paramValue) ? trim((string)$paramValue) === '' : empty($paramValue);

                    if (!$isEmpty) {
                        try {
                            // Obtém o tipo do primeiro parâmetro do setter
                            $params = $method->getParameters();
                            $parameterType = $params[0]->getType()?->getName();
                            
                            $convertedValue = self::convertValue($paramValue, $parameterType);
                            
                            if ($convertedValue !== null) {
                                $method->invoke($entity, $convertedValue);
                            }
                        } catch (Exception $e) {
                            error_log("Erro ao popular campo $fieldName: " . $e->getMessage());
                        }
                    }
                }
            }
        }
        return $entity;
    }

    /**
     * Valida a entidade inspecionando atributos #[Validation] em suas propriedades.
     *
     * @param object $entity
     * @param array $context Dependências externas (Mappers, Services, Config)
     * @return array Mapa de erros campo => código_do_erro
     */
    public static function validate(object $entity, array $context = []): array
    {
        $errors = [];
        $reflection = new ReflectionClass($entity);

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Validation::class);

            foreach ($attributes as $attribute) {
                $rules = $attribute->newInstance();
                $property->setAccessible(true);
                $value = $property->getValue($entity);
                $fieldName = $property->getName();

                if ($rules->required && empty($value)) {
                    $errors[$fieldName] = 'error_required';
                }

                if ($rules->email && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$fieldName] = 'error_email';
                }

                if ($rules->minLength > 0 && !empty($value) && strlen((string)$value) < $rules->minLength) {
                    $errors[$fieldName] = 'error_min_length';
                }

                if ($rules->maxLength > 0 && strlen((string)$value) > $rules->maxLength) {
                    $errors[$fieldName] = 'error_max_length';
                }

                if ($rules->callback !== null && method_exists($entity, $rules->callback)) {
                    $callbackResult = $entity->{$rules->callback}($value, $context);
                    if ($callbackResult !== true) {
                        $errors[$fieldName] = $callbackResult ?: 'error_custom';
                    }
                }
            }
        }

        if ($entity instanceof BaseDTO) {
            $entity->setErrors($errors);
        }

        return $errors;
    }

    /**
     * Converte o valor do request para o tipo específico esperado pelo método.
     * Utiliza o Match Expression do PHP 8, similar ao switch moderno do Java.
     */
    private static function convertValue(mixed $value, ?string $targetType): mixed
    {
        if ($value === null) return null;

        return match ($targetType) {
            'string' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value,
            'int', 'integer' => (int)$value,
            'float', 'double' => (float)$value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'array' => is_array($value) ? $value : [$value],
            'DateTime', 'DateTimeInterface' => new DateTime(is_scalar($value) ? (string)$value : 'now'),
            // BigDecimal em PHP é comumente tratado como string para bibliotecas de precisão (BCMath)
            'BigDecimal' => is_scalar($value) ? (string)$value : '0',
            default => $value,
        };
    }
}