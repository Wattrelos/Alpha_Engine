<?php

namespace Containers;

use Alpha\Controller\Actions\ActionInterface;
use RuntimeException;
use ReflectionClass;
use Exception;

/**
 * AppContainer — Micro-container de injeção de dependências para Actions.
 *
 * Aplica o mesmo padrão do SimpleObjectFactory:
 *   nome da classe → namespace via ACTIONS_PATH → class_exists → is_subclass_of(ActionInterface)
 *
 * A diferença é a etapa de instanciação: em vez de `new $targetClass()` (sem args, como nas Entidades),
 * usa ReflectionClass para inspecionar o construtor da Action e injetar automaticamente
 * as dependências registradas via bind() — eliminando qualquer cadeia de IFs.
 */
class AppContainer implements \Psr\Container\ContainerInterface
{
    /**
     * Verifica se o container possui um registro para o identificador fornecido.
     */
    public function has(string $id): bool
    {
        if (isset($this->bindings[$id])) {
            return true;
        }

        if (str_contains($id, '\\')) {
            return class_exists($id) && is_subclass_of($id, ActionInterface::class);
        }

        if (defined('ACTIONS_PATH')) {
            $baseNamespace = str_replace('.', '\\', ACTIONS_PATH);
            $targetClass = $baseNamespace . '\\' . $id;
            return class_exists($targetClass) && is_subclass_of($targetClass, ActionInterface::class);
        }

        return false;
    }

    /**
     * Registry de serviços disponíveis para injeção automática.
     * A chave é o FQCN da classe ou interface (ex: Twig\Environment::class).
     *
     * @var array<string, object>
     */
    private array $bindings = [];

    /**
     * Registra um serviço disponível para injeção.
     * Interface fluente: $container->bind(...)->bind(...)
     */
    public function bind(string $type, object $instance): self
    {
        $this->bindings[$type] = $instance;

        // Registra também pelas interfaces implementadas pelo serviço,
        // permitindo que actions tipar por interface em vez de classe concreta
        foreach (class_implements($instance) as $interface) {
            if (!isset($this->bindings[$interface])) {
                $this->bindings[$interface] = $instance;
            }
        }

        return $this;
    }

    /**
     * Resolve e instancia uma Action pelo nome simples ou FQCN completo.
     *
     * Mesmo fluxo do SimpleObjectFactory::create():
     *  1. Resolve namespace via ACTIONS_PATH
     *  2. Verifica class_exists
     *  3. Valida is_subclass_of(ActionInterface)
     *  4. Resolve dependências via Reflection (sem IFs)
     *
     * @throws RuntimeException
     */
    public function get(string $className): object
    {
        if (isset($this->bindings[$className])) {
            return $this->bindings[$className];
        }

        try {
            // 1. Resolve o namespace completo — mesmo padrão do SimpleObjectFactory
            // Converte ponto (convenção Java do ACTIONS_PATH) para contra-barra (namespace PHP)
            $baseNamespace = str_replace('.', '\\', ACTIONS_PATH);

            // Se já vier com namespace completo (contém \), usa direto; senão resolve
            $targetClass = str_contains($className, '\\')
                ? $className
                : $baseNamespace . '\\' . $className;

            // 2. Verifica se a classe existe no autoload (igual ao SimpleObjectFactory)
            if (!class_exists($targetClass)) {
                throw new RuntimeException("Action não encontrada: {$targetClass}");
            }

            // 3. Valida que a classe implementa ActionInterface (equivalente ao isAssignableFrom do Java)
            if (!is_subclass_of($targetClass, ActionInterface::class)) {
                throw new RuntimeException(
                    "A classe '{$targetClass}' não implementa ActionInterface. " .
                        "Todas as Actions devem implementar Alpha\\Controller\\Actions\\ActionInterface."
                );
            }

            // 4. Instancia resolvendo dependências via Reflection — sem nenhum IF
            return $this->resolve($targetClass);
        } catch (Exception $e) {
            throw new RuntimeException("Falha ao instanciar Action: '{$className}'", 0, $e);
        }
    }

    /**
     * Inspeciona o construtor da classe via ReflectionClass e resolve cada
     * parâmetro consultando o registry de bindings.
     *
     * Este é o passo que o SimpleObjectFactory não precisa fazer (Entidades não têm dependências).
     * Para Actions, substitui completamente a cadeia de IFs.
     *
     * @throws RuntimeException se uma dependência não estiver registrada
     */
    private function resolve(string $targetClass): ActionInterface
    {
        $reflection  = new ReflectionClass($targetClass);
        $constructor = $reflection->getConstructor();

        // Se a Action não tem construtor ou não tem parâmetros, instancia diretamente
        if (!$constructor || $constructor->getNumberOfParameters() === 0) {
            return new $targetClass();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $type     = $param->getType();
            $typeName = $type?->getName();

            if ($typeName && isset($this->bindings[$typeName])) {
                // Dependência encontrada no registry
                $args[] = $this->bindings[$typeName];
            } elseif ($param->isDefaultValueAvailable()) {
                // Parâmetro opcional: usa o valor padrão declarado na assinatura
                $args[] = $param->getDefaultValue();
            } else {
                throw new RuntimeException(
                    "Dependência '{$typeName}' não registrada no AppContainer. " .
                        "Use \$container->bind({$typeName}::class, \$instancia) antes de get()."
                );
            }
        }

        return $reflection->newInstanceArgs($args);
    }
}
