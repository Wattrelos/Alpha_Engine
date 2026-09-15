# DP-18: Implementar contatos no painel de administração de fornecedores

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-22 21:36:45
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/18

## Descrição

# Implementar Contatos no Painel de Administração de Fornecedores

Este plano descreve como integrar o atributo `Contato` ao domínio `Fornecedor`, armazenando-o em uma tabela composta de tripla relação `agsc_supplier_contact_manufacturer` e adicionando uma interface de usuário rica e dinâmica para gerenciamento de contatos às telas CRUD de fornecedores no painel de administração.

## Revisão do Usuário Necessária

> [!IMPORTANTE]
> A tabela do banco de dados é `agsc_supplier_contact_manufacturer` (vinculando `supplier_id`, `contact_id` e `manufacturer_id`). Para manter o mapeamento do modelo limpo e robusto, lidaremos com os contatos manualmente em `SupplierRepository`, em vez de depender de anotações automáticas, que são projetadas para tabelas pivô binárias simples.

## Alterações Propostas

### Entidades e Repositórios de Domínio

#### [MODIFICAR] [Supplier.php](/core/Model/Domain/Entities/Supplier/Supplier.php)
- Adicionar a propriedade `private array $contacts = [];` com getters e setters.

#### [MODIFICAR] [SupplierRepository.php](/core/Model/Domain/Repositories/SupplierRepository.php)
- **`find(int $id)`**: Carregar todos os contatos associados ao fornecedor de `agsc_supplier_contact_manufacturer` e defini-los na entidade Fornecedor.

- **`save(Supplier $supplier)`**: Inserir ou atualizar contatos, inserir relações na tabela pivô e excluir quaisquer contatos órfãos.

- **`delete(int $id)`**: Recupera os contatos associados e os exclui primeiro da tabela `agsc_contact` para evitar linhas órfãs e, em seguida, exclui o próprio fornecedor.

---

### Controladores do Painel (Ações)

#### [MODIFY] [CreateSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/CreateSupplierAction.php)
- Busca todos os fabricantes e os passa para o modelo na requisição GET.

#### [MODIFY] [EditSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/EditSupplierAction.php)
- Busca todos os fabricantes e os passa para o modelo na requisição GET.

#### [MODIFICAR] [StoreSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/StoreSupplierAction.php)
- Analisar o array `contacts` do corpo da requisição POST e defini-lo na entidade Fornecedor.

- Buscar todos os fabricantes e passá-los para o template em caso de erros de validação.

#### [MODIFICAR] [UpdateSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/UpdateSupplierAction.php)
- Analisar o array `contacts` do corpo da requisição POST e defini-lo na entidade Fornecedor.

- Buscar todos os fabricantes e passá-los para o template em caso de erros de validação.

---

### Modelos de IU

#### [MODIFICAR] [create.html.twig](/resources/views/admin/catalog/supplier/create.html.twig)
- Adicionar uma nova seção "Contatos do Fornecedor" com uma tabela dinâmica que permite adicionar/remover várias linhas de contato (Nome, E-mail, Telefone, Cargo, lista suspensa do Fabricante).

#### [MODIFICAR] [edit.html.twig](/resources/views/admin/catalog/supplier/edit.html.twig)
- Adicionar a mesma tabela dinâmica à tela de edição, pré-preenchida com os contatos atuais do fornecedor.

---

### Conjunto de Testes

#### [NOVO] [TestSupplierContacts.php](/tests/TestSupplierContacts.php)
- Verificar a criação, atualização e exclusão de um fornecedor com contatos e verificar a integridade do banco de dados.

## Plano de Verificação

### Testes Automatizados
- Executar `php tests/TestSupplierContacts.php`.

### Verificação Manual
- Acessar a página Fornecedores no painel de administração.

- Criar um novo fornecedor, adicionar alguns contatos, selecionando seus cargos e fabricantes. Salvar e verificar.

- Editar o fornecedor, modificar um contato, excluir um contato e adicionar um novo. Salvar e verificar.

- [x] Carregar a lista de categorias em `CreateProductAction.php` e renderizá-la
- [x] Salvar as categorias de produto na criação em `CreateProductAction.php`
- [x] Carregar a lista de categorias e as categorias de produto atuais em `EditProductAction.php`
- [x] Salvar as categorias de produto na atualização em `UpdateProductAction.php`
- [x] Adicionar o campo de seleção de categoria na interface do usuário em `create.html.twig`
- [x] Adicionar o campo de seleção de categoria na interface do usuário em `edit.html.twig`
- [x] Atualizar o script de teste `tests/TestCreateProduct.php`
- [x] Executar o conjunto de testes para verificar
- [x] Atualizar as consultas em `ProductMapper.php` (getProduct, getProducts, getProductsByIds, getTotalProducts, getRelated e subconsultas)
- [x] Atualizar a consulta em `ManufacturerMapper.php` (getManufacturersByCategory)
- [x] Atualizar `ShowProductAction.php` (Loop de renderização de variantes)
- [x] Escrever teste de integração `tests/TestStockStatusHiding.php`
- [x] Executar o conjunto de testes para verificar tudo
- [x] Adicionar a propriedade `contacts`, getter e setter em `Supplier.php`
- [x] Implementar o carregamento, salvamento e exclusão de contatos em `SupplierRepository.php`
- [x] Buscar e passar fabricantes em `CreateSupplierAction.php` e `EditSupplierAction.php`
- [x] Analisar e passar contatos e fabricantes em `StoreSupplierAction.php` e `UpdateSupplierAction.php`
- [x] Adicionar gerenciamento dinâmico de interface de contatos em `create.html.twig` e `edit.html.twig`
- [x] Criar script de teste `tests/TestSupplierContacts.php` e verificar

# Implementação de Categorias, Ocultação de Estoque e Contatos de Fornecedores

Integramos com sucesso a atribuição de categorias aos produtos, implementamos a filtragem de status de estoque para ocultar itens fora de estoque, integramos os Contatos de Fornecedores com controles dinâmicos da interface administrativa e resolvemos um problema crítico de transações aninhadas na camada principal do banco de dados.

---

## 1. Integração de Categorias no Painel de Administração de Produtos

### Alterações Implementadas

#### Controladores
- **[CreateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/CreateProductAction.php)**: Adicionada lógica para consultar todas as categorias disponíveis e passá-las para o modelo no método GET. Adicionada análise do corpo da requisição para IDs de categoria e lógica para armazená-los em `product_to_category` dentro da transação no método POST.

- **[EditProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/EditProductAction.php)**: Consulta todas as categorias e as categorias atuais associadas ao produto para passar para a visualização Twig.

- **[UpdateProductAction.php](/core/Admin/Controllers/Actions/Catalog/Product/UpdateProductAction.php)**: Adicionada análise do ID da categoria a partir do POST. Integrada lógica de transação para excluir relações de categoria antigas e inserir as atualizadas.

#### Templates
- **[create.html.twig](/resources/views/admin/pages/products/create.html.twig)**: Adicionada uma interface de usuário premium com grade de seleção rolável para selecionar categorias de produtos.
- **[edit.html.twig](/resources/views/admin/pages/products/edit.html.twig)**: Adicionada a mesma interface de caixa de seleção em grade na aba Geral, com categorias pré-selecionadas para registros de produtos existentes.

#### Testes
- **[TestCreateProduct.php](/tests/TestCreateProduct.php)**: Aprimorado o payload simulado para incluir categorias, assegurada a persistência adequada no banco de dados e a limpeza de relações.

---

## 2. Ocultar Produtos e Variações Fora de Estoque (stock_status_id = 5)

### Alterações Implementadas

#### Mapeadores da Loja Virtual
- **[ProductMapper.php](/core/Mappers/EntityMappers/ProductMapper.php)**:

- Substituídas as verificações fixas `p.quantity > 0` por `NOT (p.quantity <= 0 AND p.stock_status_id = 5)` em todas as consultas da loja virtual (`getProduct`, `getProducts`, `getProductsByIds`, `getTotalProducts`, `getRelated`).

- Adicionada a condição `AND NOT (pv.quantity <= 0 AND pv.stock_status_id = 5)` às subconsultas que calculam o preço mínimo/máximo, o nome e a imagem da variante, para que as variantes fora de estoque com ID de status 5 sejam ignoradas corretamente. **[ManufacturerMapper.php](/core/Mappers/EntityMappers/ManufacturerMapper.php)**: Atualizou `getManufacturersByCategory` para substituir a condição `p.quantity > 0` por `NOT (p.quantity <= 0 AND p.stock_status_id = 5)`.

#### Controladores da Loja Virtual
- **[ShowProductAction.php](/core/Controller/Actions/Product/ShowProductAction.php)**: Filtraram todas as variações cuja quantidade é <= 0 e stock_status_id é 5 no loop de variações para renderização da loja virtual.

#### Testes
- **[TestStockStatusHiding.php](/tests/TestStockStatusHiding.php)**: Criado um novo script de teste de integração para verificar se os produtos são ocultados/visíveis dependendo do seu ID de status de estoque e quantidade.

---

## 3. Integração de Contatos de Fornecedores (CRUD Administrativo e Banco de Dados)

### Alterações Implementadas

#### Entidades e Repositórios de Domínio
- **[Supplier.php](/core/Model/Domain/Entities/Supplier/Supplier.php)**: Adicionada a propriedade de array `$contacts` com getters e setters para agregar detalhes de contato dentro da entidade Fornecedor.

- **[SupplierRepository.php](/core/Model/Domain/Repositories/SupplierRepository.php)**:

- **`find(int $id)`**: Consulta os contatos associados ao fornecedor na tabela `agsc_contact` através da tabela pivô `agsc_supplier_contact_manufacturer` e preenche a entidade.

- **`save(Supplier $supplier)`**: Salva/atualiza contatos manualmente, atualiza registros pivô e realiza a limpeza de contatos órfãos dentro de uma transação de banco de dados.

- **`delete(int $id)`**: Recupera todos os contatos associados e os exclui da tabela de contatos para manter a integridade do banco de dados.

#### Núcleo do Framework (Correção de Bug)
- **[DataAccessObject.php](/core/Model/DataAccessObject/DataAccessObject.php)**: Corrigido um bug crítico no método `delete()` do núcleo, que iniciava e confirmava transações de banco de dados incondicionalmente. Agora, ele verifica corretamente se uma transação já está ativa usando `!$conn->inTransaction()`, evitando colisões de transações aninhadas ao excluir objetos dependentes.

#### Controladores do Painel de Controle
- **[CreateSupplierAction.php](/core/Admin/Controllers/Actions/Procurement/Supplier/CreateSupplierAction.php)**: Busca e vincula `fabricantes` à visualização de criação.

- **[EditSupplierAction.php](/core/Admin/Controllers/Actions/Procure

