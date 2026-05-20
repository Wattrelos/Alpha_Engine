<?php
namespace Opencart\testes;


/**
 * Converte recursivamente objetos (Entidades) em arrays para saída JSON.
 * Implementa proteção contra referências circulares para auxiliar nos testes do DAO.
 * 
 * @param mixed $data Dados a serem convertidos.
 * @param array $visited Rastreio de objetos visitados no caminho atual da recursão.
 * @return mixed
 */
function entityToArrayRecursive($data, &$visited = []) {
    if (is_array($data)) {
        $array = [];
        foreach ($data as $key => $value) {
            $array[$key] = entityToArrayRecursive($value, $visited);
        }
        return $array;
    }

    if (is_object($data)) {
        $oid = spl_object_hash($data);
        if (isset($visited[$oid])) {
            return '*** REFERÊNCIA CIRCULAR DETECTADA (' . get_class($data) . ') ***';
        }
        
        $visited[$oid] = true;
        $result = ['_class' => get_class($data)];
        $methods = get_class_methods($data);

        foreach ($methods as $method) {
            // Identifica métodos getter (get...) excluindo o método genérico 'getters'
            if (substr($method, 0, 3) === 'get' && $method !== 'getters') {
                $key = lcfirst(substr($method, 3));
                try {
                    $value = $data->$method();
                    $result[$key] = entityToArrayRecursive($value, $visited);
                } catch (\Throwable $e) {
                    $result[$key] = 'ERRO AO CHAMAR GETTER: ' . $e->getMessage();
                }
            }
        }

        unset($visited[$oid]); // Permite que o mesmo objeto apareça em ramos diferentes, mas não no mesmo caminho
        return $result;
    }

    return $data;
}

// 3. Autoloader do OpenCart (apenas para classes nativas do sistema se necessário)
require_once(DIR_SYSTEM . 'engine/autoloader.php');
$autoloader = new \Opencart\System\Engine\Autoloader();
$autoloader->register('Opencart\Catalog', DIR_APPLICATION);
$autoloader->register('Opencart\System', DIR_SYSTEM);

// Alpha Engine: Registra o namespace base
$autoloader->register('Alpha', DIR_SYSTEM . '../core');

header('Content-Type: application/json; charset=utf-8');

try {
    $entityName = $_GET['entity'] ?? 'Product';
    $id = $_GET['id'] ?? null;

    // Alpha Engine: Agora os mappers de entidade residem no sub-namespace EntityMappers
    $mapperClass = "\\Alpha\\Mappers\\EntityMappers\\" . $entityName . "Mapper";

    if (!class_exists($mapperClass)) {
        throw new \Exception("Erro: O Mapper '{$mapperClass}' não foi encontrado. Certifique-se de que a classe existe e o Autoloader está configurado.");
    }

    $mapper = new $mapperClass();

    if ($id) {
        // Prioriza findById (Interface) ou tenta o método dinâmico
        if ($mapper instanceof MapperInterface) {
            $result = $mapper->findById((int)$id);
        } else {
            $methodName = "get" . $entityName . "Entity";
            if (!method_exists($mapper, $methodName)) {
                throw new \Exception("Erro: O Mapper '{$mapperClass}' não é compatível.");
            }
            $result = $mapper->$methodName((int)$id);
        }

        $objectsList = $result ? [$result] : [];
    } else {
        $objectsList = $mapper->findAll();
    }

    echo json_encode(entityToArrayRecursive($objectsList), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (\Throwable $t) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $t->getMessage(),
        'file' => $t->getFile(),
        'line' => $t->getLine()
    ]);
}
