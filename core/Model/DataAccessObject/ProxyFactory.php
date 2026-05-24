<?php
namespace Alpha\Model\DataAccessObject;

use Alpha\Model\Domain\InterfaceEntity;
use ReflectionClass;
use ReflectionMethod;

/**
 * ProxyFactory - Gera entidades "fantasma" para Lazy Loading.
 * 
 * Alpha Engine Upgrade:
 * Geração dinâmica de classes via eval() para interceptar todos os métodos
 * da entidade alvo. Permite hidratar arrays e relacionamentos profundos.
 */
class ProxyFactory
{
    private static array $generatedClasses = [];

    /**
     * Cria uma instância de Proxy para uma entidade.
     * 
     * @param string $entityClass Classe da entidade (ex: Customer::class)
     * @param int $id ID da entidade no banco
     * @param callable $loader Função que será disparada ao acessar qualquer método.
     */
    public static function createProxy(string $entityClass, int $id, callable $loader): InterfaceEntity
    {
        $proxyFQCN = $entityClass . 'AlphaProxy';

        if (!isset(self::$generatedClasses[$proxyFQCN])) {
            $reflection = new ReflectionClass($entityClass);
            $namespace = $reflection->getNamespaceName();
            $shortName = $reflection->getShortName();
            $proxyShortName = $shortName . 'AlphaProxy';
            
            $methodsCode = '';

            $formatType = function(?\ReflectionType $type) {
                if (!$type) return '';
                if ($type instanceof \ReflectionNamedType) {
                    $name = $type->getName();
                    return ($type->isBuiltin() || $name === 'self' || $name === 'static' ? '' : '\\') . $name;
                }
                if ($type instanceof \ReflectionUnionType) {
                    return implode('|', array_map(function($t) {
                        $name = $t->getName();
                        return ($t->isBuiltin() || $name === 'self' || $name === 'static' ? '' : '\\') . $name;
                    }, $type->getTypes()));
                }
                return (string)$type;
            };

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->isConstructor() || $method->isDestructor() || $method->getName() === 'setId' || $method->getName() === 'getId') {
                    continue;
                }

                $methodName = $method->getName();
                
                $returnType = '';
                if ($method->hasReturnType()) {
                    $type = $method->getReturnType();
                    $returnType = ': ' . ($type->allowsNull() ? '?' : '') . $formatType($type);
                }

                $params = [];
                $paramNames = [];
                foreach ($method->getParameters() as $param) {
                    $paramStr = '';
                    if ($param->hasType()) {
                        $type = $param->getType();
                        $paramStr .= ($type->allowsNull() ? '?' : '') . $formatType($type) . ' ';
                    }
                    
                    if ($param->isVariadic()) {
                        $paramStr .= '...$' . $param->getName();
                        $paramNames[] = '...$' . $param->getName();
                    } else {
                        $paramStr .= '$' . $param->getName();
                        $paramNames[] = '$' . $param->getName();
                    }
                    
                    if ($param->isDefaultValueAvailable()) {
                        $paramStr .= ' = ' . var_export($param->getDefaultValue(), true);
                    }
                    $params[] = $paramStr;
                }
                
                $paramsStr = implode(', ', $params);
                $paramNamesStr = implode(', ', $paramNames);

                $methodsCode .= "
                public function {$methodName}({$paramsStr}){$returnType}
                {
                    \$this->_alphaTriggerLoad();
                    return parent::{$methodName}({$paramNamesStr});
                }
                ";
            }

            $classCode = "
                namespace {$namespace};

                class {$proxyShortName} extends {$shortName} {
                    private bool \$_alphaIsLoaded = false;
                    private \$_alphaLoaderFunc;
                    private int \$_alphaTargetId;

                    public function _alphaInitProxy(int \$id, callable \$loader): void
                    {
                        \$this->_alphaTargetId = \$id;
                        \$this->_alphaLoaderFunc = \$loader;
                        parent::setId(\$id);
                    }

                    private function _alphaTriggerLoad(): void
                    {
                        if (!\$this->_alphaIsLoaded) {
                            (\$this->_alphaLoaderFunc)(\$this, \$this->_alphaTargetId);
                            \$this->_alphaIsLoaded = true;
                        }
                    }

                    {$methodsCode}
                }
            ";

            eval($classCode);
            self::$generatedClasses[$proxyFQCN] = true;
        }

        $proxy = new $proxyFQCN();
        $proxy->_alphaInitProxy($id, $loader);
        return $proxy;
    }
}