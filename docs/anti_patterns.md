# Débitos Técnicos e Anti-Patterns do código legado (Sanados e Consolidados)

Este documento registra as falhas de design arquitetural originais do código legado e documenta como a **Alpha Engine** as resolveu em sua totalidade. O banco de dados foi completamente modernizado, eliminando os antigos anti-patterns e garantindo integridade referencial nativa em nível de banco de dados (DBMS), com a normalização das tabelas e o uso de Foreign Keys reais.

---

## 1. O "Pseudo-Null" em Chaves Estrangeiras (FK = 0)
**Módulo Afetado:** Categorias (`parent_id`), Produtos (`manufacturer_id`), Clientes (`customer_id` em sessões/carrinho), downloads, etc.

### O Problema Original:
O código legado armazena o valor inteiro `0` em vez de `NULL` para representar a ausência de um relacionamento (ex: categoria raiz sem categoria pai, fabricante não selecionado em produtos, ou visitante anônimo no carrinho).
* **Impacto:** Isso impede a criação de chaves estrangeiras restritivas (`FOREIGN KEY`) reais no MySQL (que acusa erro de integridade, já que ID `0` não existe na tabela pai). Em um modelo fortemente tipado (DDD), isso causa quebras de hidratação.

### A Solução Standalone na Alpha Engine:
O `DataAccessObject` (DAO) da Alpha Engine atua como um escudo durante a hidratação recursiva via Reflection (`fillEntityRecursively`). Caso uma propriedade do tipo `InterfaceEntity` receba um valor `0` vindo do banco, o DAO converte-o automaticamente para `null` no objeto PHP de destino. Isso protege a tipagem estrita do PHP 8.4 sem exigir a alteração imediata de todos os registros históricos no MySQL.

### 🧹 Ação de Saneamento:
À medida que as tabelas de banco forem migradas permanentemente para o novo esquema nativo da Alpha Engine, todas as colunas que representam associações opcionais devem sofrer `ALTER TABLE` para permitir `NULL`, acompanhado da conversão dos dados: `UPDATE tabela SET coluna = NULL WHERE coluna = 0`.

* **Saneamentos Executados (2026-06-05):**
  * **Categorias (`parent_id`)**: A coluna `parent_id` de `tbkk_category` foi convertida para permitir `NULL`, com os valores `0` (e um registro órfão) atualizados para `NULL`. Foi criada a FK `fk_category_parent` auto-referenciada.
  * **Produtos (`manufacturer_id`)**: A coluna `manufacturer_id` de `tbkk_product` foi convertida para permitir `NULL`, com os valores `0` (e registros órfãos) atualizados para `NULL`. Foi criada a FK `fk_product_manufacturer` apontando para `tbkk_manufacturer`.
  * **Relações Produto-Categoria (`category_id` em `tbkk_product_to_category`)**: Saneados os registros antigos onde `category_id = 0` (que provocavam falhas de integridade referencial), associando-os a categorias reais ou removendo a ligação inválida, possibilitando o estabelecimento de FKs estritas de integridade referencial.

---

## 2. Memory Leak de Eventos (O Acoplamento Recursivo de JSON)
**Módulo Afetado:** Sistema de Eventos de Inicialização (`event/language.php`).

### O Problema Original:
Para manter estados isolados de tradução entre controladores e componentes parciais (widgets), o código legado serializava arrays inteiros de chaves de tradução em strings JSON e as guardava recursivamente em propriedades estáticas. Em páginas com muitos blocos parciais, isso gerava um crescimento exponencial de consumo de memória RAM, estourando facilmente o limite de execução (`Allowed memory size exhausted`).

### A Solução Standalone na Alpha Engine:
Com a introdução do novo `BaseController` e do `TranslationRepository`, o sistema de internacionalização opera 100% via memória RAM controlada, utilizando pilhas nativas PHP (`$backupStack[] = $data`) que compartilham ponteiros em vez de duplicar strings. O consumo de memória RAM para carregar traduções foi reduzido a valores insignificantes.

---

## 4. A "Loja Fantasma" (store_id = 1 sem registro em `store`)
**Módulo Afetado:** Configurações (`tbkk_setting`), Produtos, Categorias, Pedidos, Clientes, Wishlists, Downloads — qualquer entidade com FK para `tbkk_store`.

### O Problema Original:
No código legado, o `store_id = 1` é usado como identificador da **loja principal** (default), porém **nunca existe um registro correspondente com `id = 0` na tabela `tbkk_store`**. Isso cria um estado inconsistente:
- A tabela `tbkk_setting` usa `store_id = 1` para configurações globais/padrão (confirmado em [`SettingMapper`](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/SettingMapper.php#L24)).
- Toda a cadeia de repositories (`AbstractRepository`, `CartRepository`, `CustomerRepository`, etc.) faz fallback para `?? 0`, assumindo que `0` representa "a loja principal" sem que esse `0` exista como entidade real.
- Não é possível criar uma `FOREIGN KEY` de `tbkk_setting.store_id → tbkk_store.id` sem violar a integridade referencial, já que nenhuma linha com `id = 0` existe (ou pode existir, pois é `AUTO_INCREMENT`).
- O `SettingMapper::findByStoreId()` faz `WHERE store_id = 1 OR store_id = ?`, ou seja, hardcoda a inexistência de `id=0` como loja global — isso é um vazamento do legado para dentro da Alpha Engine.

### A Situação Atual na Alpha Engine:
Com a criação do registro `id = 1` em `tbkk_store`, o sistema passa a ter **uma loja real registrada**. O padrão `config_store_id = 1` (configurado em `tbkk_setting`) torna toda a resolução de `store_id` legítima e rastreável. A resolução de `store_id` em `AbstractRepository::getStoreId()` retorna dinamicamente o ID da loja ativa a partir do container PSR-11 ou headers de requisição (com fallback seguro para `1`), eliminando completamente o anti-pattern do `store_id = 0`.

### ✅ Recomendação: Exigir ao menos 1 registro real em `store`
A abordagem correta — e adotada — é **manter ao menos um registro válido em `tbkk_store`** (ex: `id = 1`, a loja principal). Isso porque:

| Cenário | Sem registro (`id=0` fantasma) | Com registro real (`id=1`) |
|---|---|---|
| FK restritiva no MySQL | ❌ Impossível | ✅ Possível com `id=1` como âncora |
| Integridade de dados | ❌ `0` é um número mágico sem entidade pai | ✅ Toda FK aponta para uma entidade rastreável |
| Multi-loja | ❌ Loja 0 é ficção; lojas adicionais começam em 1 sem clareza | ✅ Lojas adicionais são `id=2, 3...`; a hierarquia é natural |
| Debugging / auditoria | ❌ "Pertence à loja 0" não informa nada | ✅ "Pertence à loja Agsonhos Principal" é auditável |
| Hidratação da entidade `Store` | ❌ DAO retorna `null` (via Pseudo-Null) ou explode | ✅ DAO hidrata um `Store` real com `name` e `url` |

### 🧹 Ação de Saneamento (Concluída):
1. ✅ **Registro `id=1` mantido** em `tbkk_store` como loja principal permanente.
2. ✅ **`tbkk_setting` migrado**: Registros de configurações associados a `store_id = 1`. FK `fk_setting_store` criada: `tbkk_setting.store_id → tbkk_store.id ON UPDATE CASCADE`.
3. ✅ **`SettingMapper::findByStoreId()`** simplificado: `WHERE store_id = ?`.
4. ✅ **Resolução unificada de `store_id`**: Centralizada em `AbstractRepository::getStoreId()` com fallback estrito para a loja principal `1`:
   - [`AbstractRepository.php`](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/AbstractRepository.php)
   - [`WishlistRepository.php`](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/WishlistRepository.php)
   - [`CartRepository.php`](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CartRepository.php)
   - [`SettingRepository.php`](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/SettingRepository.php)
   - [`BaseController.php`](file:///var/www/html/agsonhos/core/Controller/BaseController.php)
   - [`ThemeMapper.php`](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/ThemeMapper.php)
   - [`SubmitCheckoutAction.php`](file:///var/www/html/agsonhos/core/Controller/Actions/Cart/SubmitCheckoutAction.php)
5. ✅ **Saneamento de `store_id = 0` residual**: Corrigida a carga do bootstrap em [`AppBootstrap.php`](file:///var/www/html/agsonhos/Containers/AppBootstrap.php), menu institucional em [`index.php`](file:///var/www/html/agsonhos/public_html/index.php) e resolvedor de SEO em [`ShowInformationAction.php`](file:///var/www/html/agsonhos/core/Controller/Actions/Information/ShowInformationAction.php) para referenciar a loja principal real `store_id = 1`.

---
- Agora, quando o cliente instala o software, terá que cadastrar, ao menos, uma loja (store_id=1 ou outra, mas pelo menos 1).
- Isso também vale para idiomas, formas de pagamento, moedas, etc.

## 3. Travamento de Conexões e Sessões Obesas
**Módulo Afetado:** Driver de Sessão do Banco de Dados.

### O Problema Original:
O código legado inicializava sessões sem controle sobre o tamanho do payload. Quando ocorriam erros ou loops no frontend, strings massivas de erro HTML ou dados brutos de depuração eram salvos na tabela `session`. Na requisição seguinte, a leitura desse registro gordo travava a conexão PDO ou causava estouro de memória no encerramento da execução.

### A Solução Standalone na Alpha Engine:
O runtime de sessão é gerenciado exclusivamente pela Alpha Engine. O `SessionMapper` executa uma auditoria de tamanho ativa antes de carregar o payload (`SELECT id, LENGTH(data)`). Se o tamanho exceder 5MB, a linha é imediatamente eliminada e uma sessão limpa é gerada para o visitante. Além disso, as conexões da Alpha Engine impõem queries bufferizadas (`PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true`), eliminando os travamentos por consultas concorrentes ativas.

---

## 5. Novo Sistema de Localização e Endereçamento (Sanado)
**Módulo Afetado:** Geolocalização (`Country`, `Zone`, `City`) e Cadastro de Endereços de Clientes (`CustomerAddresses`).

### Solução Executada e Integridade de Banco:
O sistema de localização legado (baseado em queries simples e chaves fracas sem restrições) foi totalmente descontinuado. Implementamos o novo modelo de domínio sob o namespace `Alpha\Model\Domain\Entities\Geo` (entidades `Country`, `Zone`, `City`) e `Alpha\Model\Domain\Entities\Customer\CustomerAddresses` para endereços de clientes. 
As novas tabelas geográficas (`tbkk_geo_country`, `tbkk_geo_zone` e `tbkk_geo_city`) foram criadas e populadas no banco de dados com chaves estrangeiras restritivas reais, garantindo a integridade dos dados de endereçamento.

### Classes e Repositórios Mortos (NÃO UTILIZAR):
Os arquivos antigos foram renomeados com o sufixo `Deprecated.txt`. Eles representam **código morto** e não devem ser utilizados em nenhuma hipótese:
* `core/Model/Domain/Entities/CountryDeprecated.txt` (Substituído por [`Country.php`](file:///var/www/html/agsonhos/core/Model/Domain/Entities/Geo/Country.php))
* `core/Model/Domain/Entities/CountryDescriptionDeprecated.txt` (Descontinuado)
* `core/Model/Domain/Repositories/AddressRepositoryDeprecated.txt` (Substituído por [`CustomerAddressesRepository.php`](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/CustomerAddressesRepository.php))
* `core/Model/Domain/Repositories/ZoneRepositoryDeprecated.txt` (Substituído por [`GeoZoneRepository.php`](file:///var/www/html/agsonhos/core/Model/Domain/Repositories/GeoZoneRepository.php))
* `core/Model/Domain/Repositories/CountryRepositoryDeprecated.txt` (Substituído por [`GeoCountryMapper.php`](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/GeoCountryMapper.php))
* `core/Mappers/EntityMappers/CountryMapperDeprecated.txt` (Substituído por [`GeoCountryMapper.php`](file:///var/www/html/agsonhos/core/Mappers/EntityMappers/GeoCountryMapper.php))

---

## 6. Novo Módulo de Gestão de Fornecedores (Sanado)
**Módulo Afetado:** Cadastro de Fornecedores (`Supplier`) e relacionamento com fabricantes e endereços.

### O Problema Original:
No código legado, não existia uma entidade ou estrutura de banco de dados para representar e auditar os fornecedores (`Suppliers`), impossibilitando a gestão de cadeias de suprimentos de forma estruturada.

### Solução Executada:
Criamos e normalizamos o novo esquema relacional de fornecedores em nível de banco de dados por meio das seguintes tabelas:
*   `tbkk_supplier`: Cadastro central de fornecedores (CNPJ, Razão Social, etc.).
*   `tbkk_supplier_address`: Mapeamento de endereços associados a cada fornecedor, integrado às tabelas geográficas (`tbkk_geo_country`, `tbkk_geo_zone`, `tbkk_geo_city`).
*   `tbkk_supplier_contact_manufacturer`: Associação de muitos-para-muitos entre fornecedores, contatos de atendimento e fabricantes representados.
A implementação conta com o novo namespace de domínio em `Alpha\Model\Domain\Entities\Supplier` e controladores administrativos específicos, garantindo conformidade total com o padrão Domain-Driven Design (DDD).

---

## 7. Formulários HTML Sem Injeção de Tokens Anti-CSRF (Sanado)
**Módulo Afetado:** Todos os formulários HTML com envio `POST`, `PUT`, `DELETE` ou `PATCH` (Admin e Front-end).

### O Problema Original:
Criar ou renderizar formulários HTML Twig sem os campos ocultos do token CSRF (`{{ csrf.keys.name }}` e `{{ csrf.keys.value }}`) faz com que a submissão do formulário seja enviada ao servidor sem as credenciais de segurança exigidas pelo `CsrfGuardMiddleware`. Isso resulta na rejeição da requisição com erro `HTTP 400 - Requisição Rejeitada (CSRF)` ("Sua sessão expirou ou a validação de segurança do formulário falhou").

### A Solução Padronizada na Alpha Engine:
É obrigatório que todo e qualquer formulário HTML que envie dados via métodos de alteração inclua o bloco condicional de tokens no template Twig:

```twig
<form method="POST" action="...">
    {% if csrf %}
        <input type="hidden" name="{{ csrf.keys.name }}" value="{{ csrf.name }}">
        <input type="hidden" name="{{ csrf.keys.value }}" value="{{ csrf.value }}">
    {% endif %}
    ...
</form>
```

Para formulários assíncronos submetidos via JavaScript, deve-se extrair as variáveis de segurança das meta-tags `<meta name="csrf-*">` renderizadas no layout base (`base.html.twig` / `admin/layouts/base.html.twig`) ou injetar os campos via `form-validator.js`.

