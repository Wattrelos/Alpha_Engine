<?php
namespace Alpha\Model\DataAccessObject;

use Alpha\Model\Domain\InterfaceEntity;

/**
 * ProxyFactory - Gera entidades "fantasma" para Lazy Loading.
 */
class ProxyFactory
{
    /**
     * Cria uma instância de Proxy para uma entidade.
     * 
     * @param string $entityClass Classe da entidade (ex: Customer::class)
     * @param int $id ID da entidade no banco
     * @param callable $loader Função que sabe como carregar os dados reais
     */
    public static function createProxy(string $entityClass, int $id, callable $loader): InterfaceEntity
    {
        // Criamos uma classe anônima que estende a entidade alvo
        return new class($entityClass, $id, $loader) extends $entityClass {
            private bool $isLoaded = false;
            
            public function __construct(
                private string $targetClass,
                private int $targetId,
                private $loaderFunc
            ) {
                // Definimos apenas o ID no shell do objeto
                parent::setId($targetId);
            }

            /**
             * Interceptamos chamadas para garantir que os dados existam.
             */
            private function triggerLoad(): void
            {
                if (!$this->isLoaded) {
                    $data = ($this->loaderFunc)($this->targetId);
                    if ($data) {
                        // Aqui usamos reflexão ou setters para popular o objeto atual ($this)
                        // com os dados vindos do array $data
                        foreach ($data as $key => $value) {
                            $method = 'set' . ucfirst($key);
                            if (method_exists($this, $method)) {
                                $this->$method($value);
                            }
                        }
                    }
                    $this->isLoaded = true;
                }
            }

            // Sobrescrevemos os métodos de negócio da entidade para disparar o load
            // Nota: Em uma implementação completa, usaríamos __call ou geraríamos
            // métodos que chamam $this->triggerLoad() antes do parent::metodo()
        };
    }
}