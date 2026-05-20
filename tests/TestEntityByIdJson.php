<?php

namespace Alpha\Tests;

/**
 * TestEntityByIdJson - Teste dinâmico para busca de entidades pelo ID (Alpha Engine).
 * 
 * Este arquivo automatiza o teste dos métodos de busca por PK dos Mappers,
 * recebendo a entidade e o ID via parâmetros GET.
 * 
 * Uso: /tests/TestEntityByIdJson.php?entity=Address&id=32
 */

// 1. Carregamento do ambiente OpenCart e Alpha Engine
require_once(dirname(__DIR__) . '/config.php');
require_once(DIR_SYSTEM . 'engine/autoloader.php');

$autoloader = new \Opencart\System\Engine\Autoloader();
$autoloader->register('Opencart\Catalog', DIR_APPLICATION);
$autoloader->register('Opencart\System', DIR_SYSTEM);

use Alpha\Mappers\MapperInterface;

// Alpha Engine: Validação e Registro do namespace base para localizar Mappers
$alphaCorePath = dirname(DIR_SYSTEM) . '/core/';
$alphaRealPath = realpath($alphaCorePath);

if ($alphaRealPath === false || !is_dir($alphaRealPath)) {
    // Gera um log detalhado para o arquivo de erros do OpenCart
    $logEntry = sprintf("[%s] Alpha Engine Error: Falha ao resolver realpath para: %s (DIR_SYSTEM: %s)\n", 
        date('Y-m-d H:i:s'), $alphaCorePath, DIR_SYSTEM);
    
    file_put_contents(DIR_LOGS . 'error.log', $logEntry, FILE_APPEND);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Erro de infraestrutura: O diretório Alpha Core não foi encontrado. Detalhes no log do sistema.']);
    exit;
}

$autoloader->register('Alpha', $alphaRealPath . '/');

/**
 * Converte recursivamente instâncias de Entidades em arrays associativos.
 * Essencial para serializar o grafo de objetos da Alpha Engine tratando referências circulares.
 */
function entityToArray($data, &$visited = []) {
    if (is_array($data)) {
        return array_map(fn($item) => entityToArray($item, $visited), $data);
    }

    if (is_object($data)) {
        $oid = spl_object_hash($data);
        if (isset($visited[$oid])) return '*** CIRCULAR_REFERENCE (' . get_class($data) . ') ***';
        
        $visited[$oid] = true;
        $result = ['_class' => get_class($data)];

        foreach (get_class_methods($data) as $method) {
            if (str_starts_with($method, 'get') && $method !== 'getters') {
                $key = lcfirst(substr($method, 3));
                try {
                    $value = $data->$method();
                    $result[$key] = entityToArray($value, $visited);
                } catch (\Throwable) {
                    $result[$key] = '[Getter Error]';
                }
            }
        }
        unset($visited[$oid]);
        return $result;
    }
    return $data;
}

header('Content-Type: application/json; charset=utf-8');

try {
    // 2. Captura de parâmetros via GET
    $entityName = $_GET['entity'] ?? '';
    $id = (int)($_GET['id'] ?? 0);

    if (!$entityName || $id <= 0) {
        throw new \Exception("Parâmetros 'entity' e 'id' são obrigatórios. Ex: ?entity=Product&id=1");
    }

    // 3. Resolução dinâmica do Mapper
    $mapperClass = "Alpha\\Mappers\\EntityMappers\\" . $entityName . "Mapper";

    if (!class_exists($mapperClass)) {
        throw new \Exception("Mapper não encontrado: {$mapperClass}");
    }

    $mapper = new $mapperClass();
    $result = null;

    // 4. Resolução de execução (Prioriza métodos específicos para evitar colisões com propriedades não inicializadas)
    
    // Verificamos via Reflection se a propriedade entityClass está inicializada
    $reflection = new \ReflectionClass($mapper);
    $isEntityReady = false;
    if ($reflection->hasProperty('entityClass')) {
        $prop = $reflection->getProperty('entityClass');
        $prop->setAccessible(true);
        $isEntityReady = $prop->isInitialized($mapper);
    }

    if (method_exists($mapper, "get{$entityName}Entity")) {
        // Padrão Alpha: Retorno de Objeto hidratado
        $method = "get{$entityName}Entity";
        $result = $mapper->$method($id);
    } elseif ($isEntityReady && $mapper instanceof MapperInterface) {
        // Padrão Moderno: Interface genérica
        $result = $mapper->findById($id);
    } elseif (method_exists($mapper, "get{$entityName}")) {
        // Padrão Legado/Transição: Retorno de Array
        $method = "get{$entityName}";
        try {
            $result = $mapper->$method($id);
        } catch (\ArgumentCountError) {
            // Injeta defaults conhecidos (Language ID 2 é o padrão Alpha)
            $result = $mapper->$method($id, 2, 0); 
        }
    }

    // 5. Retorno do JSON formatado
    echo json_encode(entityToArray($result), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (\Throwable $t) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $t->getMessage(), 'line' => $t->getLine()]);
}