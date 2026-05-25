
### Correção de Bug: Fatal Error no Carrinho de Compras (Cart)

 **Implementação:** Correção de chamadas de método de carregamento de linguagem incorretas (`$this->loadLanguage` para `$this->load->language`) no controlador `catalog/controller/checkout/cart.php` nos métodos `list()`, `add()`, `edit()` e `remove()`.
 **Motivo:** O método `loadLanguage` não existia no escopo do controlador, originando o erro `Call to undefined method` e resultando na interrupção (Crash) durante a adição de produtos ao carrinho. O carregamento de traduções precisa ser intermediado pelo objeto Loader da arquitetura OpenCart.
 **Benefício:** Restaura o funcionamento assíncrono do carrinho de compras, impedindo que o fluxo de checkout e adição ao carrinho resulte em uma tela de erro ou pare de processar as chamadas AJAX vindas do front-end.

---

### Correção de Bug: Fatal Error - Undefined Method `getTemplate`

- **Implementação:** Substituição da chamada `$this->getTemplate(...)` por `$this->load->view(...)` no método `getList()` do `Cart` e nos métodos `index()` dos controladores do checkout (`payment_address`, `shipping_address`, `shipping_method`, `payment_method`, `confirm`).
- **Motivo:** O método `getTemplate` não está presente em `BaseController` nem no núcleo do OpenCart, causando um Fatal Error (`Call to undefined method`) quando esses controladores precisavam retornar o HTML em requisições AJAX. O método nativo e correto para renderizar e retornar uma view em formato de string no OpenCart é através de `$this->load->view()`.
- **Benefício:** Restaura o carregamento correto da listagem do carrinho e de todos os passos assíncronos do checkout, prevenindo falhas em cascata que impediriam a finalização do pedido.

---

### Correção de Bug: Variáveis de Tradução Faltando no Template do Carrinho

- **Implementação:** Invocação do método `$this->loadLanguageData('checkout/cart', $data);` nos métodos `index()` e `getList()` do controlador `catalog/controller/checkout/cart.php`.
- **Motivo:** O HTML da view do carrinho (`cart_list.twig`) estava apresentando partes incompletas e campos vazios (por exemplo, etiquetas e atributos `title` dos botões) porque as chaves de idioma não estavam sendo enviadas para a interface. Diferente do OpenCart nativo que às vezes polui o escopo, a Alpha Engine requer a injeção explícita de traduções no array de retorno usando `loadLanguageData`.
- **Benefício:** Restaura os textos dos rótulos, legendas, títulos de tabela e botões de tooltip do carrinho de compras, retornando a semântica visual e a acessibilidade da página para o usuário final.

---

### Correção de Bug: Fatal Error - Abstract Method em AddressRepository

- **Implementação:** Adicionado o método `findAll()` na classe `Alpha\Model\Domain\Repositories\AddressRepository`.
- **Motivo:** A classe implementa a interface `BaseRepositoryInterface`, que exige o contrato obrigatório do método `findAll()`. A ausência da declaração desse método resultou no erro fatal reportado pelo PHP (`Class contains 1 abstract method and must therefore be declared abstract or implement the remaining methods`).
- **Benefício:** Restaura a estabilidade da classe e corrige o erro fatal, garantindo o cumprimento integral do contrato estipulado pela interface do repositório base.

---

### Melhoria de Documentação: Herança no AddressMapper

- **Implementação:** Adição de bloco explicativo (PHPDoc) na classe `Alpha\Mappers\EntityMappers\AddressMapper`.
- **Motivo:** Como os métodos básicos de CRUD (`findAll`, `findById`, etc.) são abstraídos na classe pai `BaseMapper`, a ausência explícita desses métodos nas classes filhas pode gerar dúvidas estruturais. A documentação previne investigações desnecessárias por parte de outros desenvolvedores.
- **Benefício:** Melhora a clareza e a legibilidade do código, reforçando o conhecimento arquitetural sobre como o ORM/Mapeador da Alpha Engine funciona através de herança estrita.

---

### Correção de Domínio: Entidades Geográficas (Country e Zone)

- **Implementação:** Adição da propriedade `$name` (com os respectivos getters e setters) nas entidades `Country` e `Zone`. Alteração da propriedade `$addressFormatId` para `$addressFormat` (string) em `Country`.
- **Motivo:** Ao revisar os mapeamentos da entidade `Address`, verificou-se que as entidades de destino das relações (`Country` e `Zone`) não possuíam as propriedades de nome e template de formatação requeridas para a serialização no `AddressRepository`. Isso causaria o erro fatal `Call to undefined method` durante a finalização de compras (Checkout) ao exibir os endereços do cliente.
- **Benefício:** Restaura a integridade estrutural das entidades geográficas, garantindo o funcionamento perfeito da relação `ManyToOne` na arquitetura Alpha Engine e a exibição correta dos endereços na frente da loja.

---

### Integração de Domínio: Lógica de Imposto e Zona (`CartRepository`)

- **Implementação:** Criação do método `resolveTaxAndShippingZone()` no `CartRepository` (invocado durante o ciclo de vida inicial de `getProducts()`) e implementação do cálculo estrito de impostos visuais (`$this->tax->calculate()`) nos DTOs de formatação de preço do carrinho.
- **Motivo:** O carrinho estava omitindo a exibição de impostos no subtotal textual dos produtos e perdendo as coordenadas de Zona (Estado/País) de clientes recém-logados, pois não sincronizava com a nova estrutura de abstração da entidade `Address`.
- **Benefício:** Agora, ao carregar os itens, a Alpha Engine automaticamente extrai o endereço principal da Entidade de Endereços (`AddressRepository`), injeta as coordenadas na sessão e na classe de imposto, e exibe os valores exatos de tributos e estimativas de frete baseados na localidade real do cliente.

---

### Nova Implementação: Ferramentas de Auditoria ORM, Automação e PSR-16 Cache

- **Implementação:** Desenvolvimento e consolidação de scripts utilitários para análise estática e refatoração em massa (`DetectarZumbis.php`, `InjetarInterface.php`, `GerarGettersSetters.php`, `RenomeadorDeNomesDeArquivosEmReferenciasInternas.php`), implementação do contrato abstrato `CacheStrategyInterface` (inspirado na PSR-16) e refinamento das entidades de domínio (ex: `Layout`) aproveitando recursos do PHP 8.4+.
- **Motivo:** A transição do legado para a arquitetura Alpha Engine (orientada a DDD, Mappers e Repository Pattern) exigiu ferramentas robustas para garantir que as estruturas de banco de dados e objetos PHP estivessem em perfeita sincronia, prevenindo anomalias silenciosas (atributos órfãos) e facilitando a transição de arquivos sem quebrar referências internas.
- **Benefício:** Redução drástica da possibilidade de erro humano, tanto nas refatorações profundas quanto no mapeamento do banco de dados (zero Entidades Zumbis). O sistema ganha um nível enterprise de escalabilidade, testabilidade e manutenibilidade, coroando este ciclo de modernização com uma base sólida pronta para integrações avançadas (como novos drivers de Cache).