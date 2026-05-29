# [Padronização do Fluxo de Dados e Alimentação de Views na Alpha Engine]

**Implementação**
* Introdução do padrão **Data Transfer Object (DTO)** / **Read Model** para otimizar o fluxo de exibição e evitar que o Twig acesse propriedades de persistência diretamente.
* Organização do trânsito de dados pelas seguintes camadas:
  1. **DAO/QueryBuilder**: Retorna dados brutos da query SQL (`PDO::FETCH_ASSOC` ou `stdClass`).
  2. **Data Mapper**: Hidrata os dados brutos em **Entidades de Domínio** (para operações de escrita/negócio) ou mapeia diretamente em **DTOs de Leitura** (para operações somente leitura).
  3. **Repository**: Expõe métodos para a Action/Controller retornando Entidades ou DTOs específicos da View.
  4. **Presenter / View Model (Opcional)**: Trata detalhes de formatação (ex: concatenação de caminhos de imagem, formatação monetária e de datas) para que o Controller não contenha lógica de formatação.
  5. **Action/Controller**: Atua como orquestrador fino, delegando a busca ao Repository, a formatação ao Presenter e passando o DTO higienizado para o Twig.

**Motivo**
* A Action `HomeAction` atual mistura o retorno de arrays associativos (`$configSettings`) e objetos de entidade (`$language`), além de conter lógica de formatação de exibição (como a concatenação da URL da logo `HTTP_SERVER . 'image/' . ...`), o que viola o princípio de responsabilidade única (SRP) e aumenta o acoplamento entre banco de dados e camada visual.

**Benefício**
* **Desacoplamento Visual**: Mudanças no esquema do banco não quebram o template Twig, pois a estrutura do DTO serve como contrato estável.
* **Performance**: Evita a hidratação desnecessária de entidades ricas completas (com proxies e identity maps) em listagens simples (read-only), reduzindo a alocação de memória RAM.
* **Legibilidade e Autocompletar**: Uso de propriedades tipadas de DTOs nativas do PHP 8.4 facilita a manutenção do código e análise estática (PHPStan/Psalm).

## Questão
Analise o aquivo core/Controller/Actions/Main/HomeAction.php. Qual seria a forma mais modern, visando as boas práticas e os melhores padrões de projeto, para buscar objetos hidtratos no banco de dados e alimentar as páginas Twig? Por exemplo, devo utilizar POO para transitar pelas camadas model (Repository, Mapper, Uow, DAO, QueryBuilder), devo usar arrays ou outra alternativa?

## Resposta Completa do Assistente

### Análise do Arquivo `HomeAction.php` Atual
No arquivo [HomeAction.php](file:///var/www/html/agsonhos/core/Controller/Actions/Main/HomeAction.php), observamos o seguinte cenário de trânsito de dados:
* `categoryRepository->getFeaturedCategories()` retorna objetos de categorias.
* `settingRepository->getSetting('config', 0)` retorna um `array` associativo bruto direto do banco.
* `languageRepository->getByCode($languageCode)` retorna um objeto de entidade `Language`.
* O controlador contém regras de apresentação, como a resolução do caminho da logo:
  ```php
  $logo = '';
  if (!empty($configSettings['config_logo'])) {
      $logo = HTTP_SERVER . 'image/' . $configSettings['config_logo'];
  }
  ```

---

### Solução Arquitetural Recomendada: Fluxo Standalone Moderno

Para obter a arquitetura de e-commerce mais robusta, performática e limpa na **Alpha Engine**, o fluxo ideal deve seguir os seguintes padrões:

```text
[Banco de Dados]
       │  (PDO::FETCH_ASSOC / raw)
       ▼
[DataAccessObject (DAO) / QueryBuilder]
       │  (Objeto genérico ou Array de dados)
       ▼
[Data Mapper]
       │  (Mapeia/Hidrata colunas do banco)
       ▼
 ┌─────┴──────────────────────────────────┐
 │                                        │
 ▼ (Operações de Escrita)                 ▼ (Operações de Leitura - Read Only)
[Domain Entities (POPOs)]                [Data Transfer Objects (DTOs) / Read Models]
 │                                        │
 └─────┬──────────────────────────────────┘
       ▼
[Repository]
       │  (Retorna Entidade ou DTO)
       ▼
[Presenter / View Model]  <── (Aplica formatação: ex: URL de imagens, datas, preços)
       │  (Objeto de Apresentação Higienizado)
       ▼
[Action / Controller]     <── (Apenas orquestra o fluxo)
       │  (Repassa o DTO formatado)
       ▼
[Twig Template]           <── (Exibe os dados usando sintaxe OO simples)
```

---

### 1. Por que NÃO utilizar Arrays associativos brutos no Controller/Twig?
1. **Falta de Tipagem**: Arrays não garantem a existência de chaves (o que obriga o uso excessivo de `isset()` ou operadores de coalescência nula no Twig, ex: `settings.config_logo ?? ''`).
2. **Refatoração Difícil**: Se uma coluna mudar de nome no banco (ex: de `config_logo` para `logo_path`), você terá que atualizar manualmente todos os templates Twig que acessam essa chave do array.
3. **Sem Autocompletar**: IDEs e ferramentas de análise estática não conseguem validar as chaves de um array associativo.

### 2. Por que NÃO injetar Entidades de Persistência ricas diretamente no Twig?
Injetar entidades ricas de escrita (com proxies e lógica de persistência atômica) diretamente na view expõe detalhes internos de banco e pode causar:
* **Lazy Loading Acidental (N+1 queries)**: O Twig ao percorrer `$categoria.produtos` pode disparar uma nova query SQL silenciosa no banco durante a renderização da tela.
* **Complexidade desnecessária**: A entidade possui métodos de validação de escrita e regras de domínio que a view não precisa conhecer.

---

### 3. A Alternativa Moderna: DTOs e Presenters

#### A. O Data Transfer Object (DTO)
Para alimentar a Home ou qualquer página, o Repository deve expor métodos que retornem DTOs específicos de leitura (Read Models) que representam exatamente o contrato que a view precisa.

```php
namespace Alpha\Model\Domain\DTOs;

/**
 * Representa os dados consolidados de configuração da loja para exibição na View.
 */
readonly class StoreViewDto
{
    public function __construct(
        public string $name,
        public string $title,
        public string $description,
        public string $keywords,
        public string $logoUrl,
        public string $languageCode
    ) {}
}
```

#### B. O Presenter (ou View Model)
A transformação da entidade bruta ou array de configuração para o DTO deve ser feita em um **Presenter** ou diretamente pelo **Mapper** / **Repository**, limpando a Action:

```php
namespace Alpha\Support\Presenters;

use Alpha\Model\Domain\DTOs\StoreViewDto;

class StoreSettingsPresenter
{
    public static function toViewDto(array $settings, string $languageCode): StoreViewDto
    {
        $logo = '';
        if (!empty($settings['config_logo'])) {
            $logo = HTTP_SERVER . 'image/' . $settings['config_logo'];
        }

        return new StoreViewDto(
            name: $settings['config_name'] ?? 'AG Sonhos',
            title: $settings['config_meta_title'] ?? 'Início',
            description: $settings['config_meta_description'] ?? '',
            keywords: $settings['config_meta_keyword'] ?? '',
            logoUrl: $logo,
            languageCode: $languageCode
        );
    }
}
```

#### C. Como a `HomeAction` ficaria refatorada:
O controlador torna-se um orquestrador extremamente fino e fácil de ler:

```php
public function __invoke(Request $request, Response $response, array $args): Response
{
    // 1. Busca dados puros estruturados das camadas de repositório
    $featuredCategories = $this->categoryRepository->getFeaturedCategories();
    $rawSettings        = $this->settingRepository->getSetting('config', 0);
    $language           = $this->languageRepository->getCurrentLanguage();

    // 2. Converte e formata os dados para DTOs de Apresentação
    $storeView = StoreSettingsPresenter::toViewDto($rawSettings, $language->getCode());

    // 3. Renderiza a tela passando os objetos higienizados
    $html = $this->twig->render('home.html.twig', [
        'store'               => $storeView,
        'featured_categories' => $featuredCategories,
        'language'            => $language
    ]);

    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
}
```

#### D. Como o Twig consumiria:
No template Twig, a sintaxe fica limpa, com acesso OO direto, tipado e com autocompletar garantido:
```twig
<title>{{ store.title }}</title>
<meta name="description" content="{{ store.description }}">

<a href="/">
    <img src="{{ store.logoUrl }}" alt="{{ store.name }}">
</a>
```

### Conclusão e Resumo das Camadas
Utilizar **Programação Orientada a Objetos (POO)** por todo o fluxo de transporte é a melhor prática da indústria. 
* O **QueryBuilder** e o **DAO** conversam com tabelas e colunas brutas do MySQL.
* O **Mapper** atua como o tradutor das regras físicas para objetos.
* O **Repository** orquestra e coordena os dados de domínio.
* O **DTO** serve como contrato seguro de dados para a View, e o **Presenter** assume a lógica visual, mantendo as **Actions** e o **Twig** limpos de qualquer regra de infraestrutura.
