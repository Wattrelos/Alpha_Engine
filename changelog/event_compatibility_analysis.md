# ⚡ Análise de Compatibilidade do Sistema de Eventos (Alpha Engine vs OpenCart 4)

Este documento analisa a viabilidade técnica, estratégias de implementação e contorno de débitos técnicos para permitir que os novos Repositórios da **Alpha Engine** disparem eventos do OpenCart (`event->trigger()`), mantendo total compatibilidade com extensões de terceiros.

---

## 🏛️ O Funcionamento Nativo dos Eventos no OpenCart 4

No OpenCart 4, os eventos de modelo (`model/*`) são disparados automaticamente pelo **Loader** (`system/engine/loader.php`) quando uma chamada de método é realizada através do objeto proxy do modelo. 

O fluxo de execução simplificado de `$this->model_catalog_product->getProduct($product_id)` é:
1. A chamada é interceptada pelo método mágico `__call` do `Proxy`.
2. O `Proxy` invoca a callback gerada em `Loader::callback()`.
3. O `Loader` dispara o evento `before`:
   ```php
   $this->event->trigger('model/catalog/product/getProduct/before', [&$route, &$args]);
   ```
4. O `Loader` resolve o objeto do modelo real (interceptado pela `AlphaContainer` para retornar a engine nova) e executa o método real.
5. O `Loader` dispara o evento `after`:
   ```php
   $this->event->trigger('model/catalog/product/getProduct/after', [&$route, &$args, &$output]);
   ```

---

## 🔍 O Desafio com Repositórios Diretos

Quando refatoramos os controladores da Alpha Engine para chamar os repositórios diretamente via injeção de dependência (ex: `$this->productRepository->getProduct($id)`), a chamada **ignora a camada de proxy do Loader**.
Consequentemente:
- Os eventos `before` e `after` **não são disparados**.
- Extensões de terceiros (como sistemas de impostos, campos personalizados de checkout, promoções) que dependem desses ganchos deixam de funcionar.

---

## 🛠️ Opções Estruturais de Solução

Abaixo estão as três principais estratégias para resolver a compatibilidade de eventos de forma gradual ou definitiva:

### Opção 1: Preservar o Carregamento por Proxy nos Controladores (Recomendada para Compatibilidade Imediata)
Em vez de instanciar os repositórios diretamente nos controladores, o controlador continua utilizando o Loader clássico para carregar o modelo:
```php
$this->load->model('catalog/product');
$product = $this->model_catalog_product->getProduct($product_id);
```
Como o `AlphaContainer` intercepta o carregamento de `'catalog/product'` e retorna o `ProductRepository`, o `Loader` automaticamente gerará o `Proxy` e disparará os eventos `before` e `after`.

* **Prós**: 
  - 100% de compatibilidade retroativa com todas as extensões sem alterar nenhuma linha de código da Alpha Engine.
  - Zero risco de duplicidade de eventos.
* **Contras**:
  - Reintroduz o acoplamento do controlador com a nomenclatura do `$this->load->model()`.

---

### Opção 2: Disparo Manual nos Repositórios com Proteção de Duplicidade (AlphaLoader Guard)
Caso queiramos invocar repositórios diretamente, podemos disparar os eventos dentro das funções do Repositório. Para evitar que os eventos rodem duas vezes quando chamados via proxy legados, implementamos um controle de estado.

1. **AlphaLoader**: Substituímos a instância do `Loader` na inicialização do Registry por um loader estendido que monitora o estado de execução:
   ```php
   namespace Alpha\System;

   class AlphaLoader extends \Opencart\System\Engine\Loader {
       public static bool $inProxyCallback = false;

       public function callback($route): callable {
           $parentCallback = parent::callback($route);
           return function(&...$args) use ($parentCallback) {
               self::$inProxyCallback = true;
               try {
                   return $parentCallback(...$args);
               } finally {
                   self::$inProxyCallback = false;
               }
           };
       }
   }
   ```

2. **Trigger no Repositório**:
   ```php
   public function getProduct(int $product_id): array {
       $args = [$product_id];
       if (!AlphaLoader::$inProxyCallback) {
           $this->event->trigger('model/catalog/product/getProduct/before', [&$product_id]);
       }

       // Lógica de banco/Mapper...
       $output = $this->getMapper()->getProduct($product_id);

       if (!AlphaLoader::$inProxyCallback) {
           $this->event->trigger('model/catalog/product/getProduct/after', ['catalog/product.getProduct', &$args, &$output]);
       }

       return $output;
   }
   ```

* **Prós**: 
  - Permite chamadas limpas aos repositórios nos novos controladores.
  - Evita loops e duplicações de chamadas de eventos de extensões.
* **Contras**:
  - Exige codificação manual de `$this->event->trigger` nas funções dos Repositórios (código boilerplate).

---

### Opção 3: Proxy de Aspecto Automatizado (AOP) no RepositoryFactory
Criar um decorador dinâmico (`__call`) que envolve os repositórios retornados pelo `RepositoryFactory` e dispara os eventos de forma transparente com base em mapeamento de rotas.

* **Prós**:
  - Centralizado, sem boilerplate nos repositórios.
* **Contras (Bomba de Débito Técnico)**:
  - **Passagem por Referência**: O método mágico `__call($name, $args)` do PHP recebe argumentos por valor. Extensões do OpenCart frequentemente modificam os argumentos originais por referência (ex: `[&$product_id]`). Implementar passagem por referência robusta em `__call` dinâmico do PHP é extremamente complexo, lento e instável, violando a simplicidade e a performance da Alpha Engine.

---

## 📈 Recomendação de Transição Gradual

1. **Para Módulos Críticos e Estáveis** (ex: `setting/store`, `setting/extension`, `setting/api`): 
   - A **Opção 1** (manter compatibilidade através do carregador e proxy legados) é mais do que suficiente e segura, pois o core e as extensões continuam chamando `$this->load->model()`.
2. **Para Módulos de Alto Impacto Visual** (ex: `catalog/product`, `checkout/cart`):
   - Adotar a **Opção 2** (Disparo Manual com AlphaLoader Guard) de forma cirúrgica apenas nas funções-chave consumidas por extensões (como `getProduct`, `getCategories`, `addOrder`).
