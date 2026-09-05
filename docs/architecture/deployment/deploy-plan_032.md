# DP-32: Implementação do Módulo do Caixa (PDV / POS Cashier)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-06-28 00:53:02
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/32

## Descrição

# Implementação do Módulo do Caixa (PDV / POS Cashier)

Este plano descreve a criação da interface do Caixa (Cashier) e das APIs necessárias para consulta de pré-vendas pendentes e processamento definitivo de pagamentos, com baixa de estoque final e transição de status no banco de dados.

## User Review Required

> [!IMPORTANT]
> - A tela do caixa será implementada diretamente em `resources/views/ pos/cashier/layout.twig`.
> - As ações no banco de dados (baixa final e mudança de status para completo/pago) serão executadas transacionalmente pelo `UnitOfWork` para garantir atomicidade.
> - As rotas serão integradas ao grupo administrativo existente e protegidas pelo `AdminSessionMiddleware`.

## Proposed Changes

### Roteamento

#### [MODIFY] [Routes.php](file:///var/www/html/agsonhos/Config/Routes.php)
- Adicionar no grupo de rotas administrativas do PDV as seguintes rotas:
  - `GET /pos/caixa` -> `ShowCashierDashboardAction` (nome: `admin.pos.cashier`)
  - `GET /pos/pedidos/{id:[0-9]+}` -> `GetPreOrderAction` (nome: `admin.pos.orders.get`)
  - `POST /pos/pedidos/{id:[0-9]+}/pagar` -> `PayOrderAction` (nome: `admin.pos.orders.pay`)

### Actions (Controllers)

#### [NEW] [ShowCashierDashboardAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/POS/ShowCashierDashboardAction.php)
- Action administrativa invocável que estende `BaseController`.
- Carrega e renderiza o template ` pos/cashier/layout.twig` com as informações básicas do caixa logado.

#### [NEW] [GetPreOrderAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/POS/GetPreOrderAction.php)
- Action administrativa invocável que recebe um ID de pedido e verifica se ele está ativo e com status pendente (ID de status = 1).
- Retorna um JSON detalhado com os produtos, quantidades, preços e totais.

#### [NEW] [PayOrderAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/POS/PayOrderAction.php)
- Action administrativa invocável que recebe o pagamento (Pix, Cartão, Dinheiro) e altera o status do pedido para "Completo" (ou correspondente pago).
- Usa `UnitOfWork` e chama `OrderRepository->confirm()` para registrar o pagamento e registrar o histórico.

### Camada de Apresentação (Twig & CSS)

#### [MODIFY] [layout.twig](file:///var/www/html/agsonhos/resources/views/%20pos/cashier/layout.twig)
- Desenhar a interface do caixa de alta fidelidade:
  - Painel de consulta de Ticket (digitação de ID e busca dinâmica).
  - Listagem dos itens da pré-venda com totais destacados.
  - Seleção do método de pagamento (Pix com simulador de QR Code, cartão de débito/crédito, dinheiro físico com calculadora de troco).
  - Impressão de cupom fiscal/recibo final.
  - Integração JS assíncrona com os novos endpoints de consulta e pagamento.

## Verification Plan

### Automated Tests
- Criar o script de teste `tests/TestPOSCashier.php` para validar todo o fluxo de consulta e confirmação de pagamento do caixa no banco de dados.

### Manual Verification
- Acessar `/LPDHED2dC7Gjrg2b/pos/caixa`, carregar o ID de uma pré-venda gerada e efetivar o pagamento.

# Tarefas - Implementação do Caixa (PDV)

- `[x]` Configurar as novas rotas no arquivo `Config/Routes.php`
- `[x]` Criar a Action `ShowCashierDashboardAction`
- `[x]` Criar a Action `GetPreOrderAction`
- `[x]` Criar a Action `PayOrderAction`
- `[x]` Criar o template do caixa em `resources/views/ pos/cashier/layout.twig`
- `[x]` Escrever testes de integração em `tests/TestPOSCashier.php`
- `[x]` Executar e validar os testes

# Walkthrough - Implementação do Caixa (PDV / POS Cashier)

Foi implementada com sucesso a tela do Caixa (Cashier) para o PDV da loja, contendo a interface em Twig e CSS, roteamento protegido e todos os endpoints assíncronos no back-end.

## O que foi Feito

### 1. Roteamento do Slim
No arquivo [Routes.php](file:///var/www/html/agsonhos/Config/Routes.php), adicionamos o seguinte conjunto de rotas sob o grupo administrativo protegido por [AdminSessionMiddleware](file:///var/www/html/agsonhos/core/Auth/Middleware/AdminSessionMiddleware.php):
- `GET /pos/caixa`: Renderiza o painel do caixa.
- `GET /pos/pedidos/{id:[0-9]+}`: Endpoint da API para busca de detalhes da pré-venda (itens, cliente e totais) por ID de ticket.
- `POST /pos/pedidos/{id:[0-9]+}/pagar`: Endpoint da API para processar o pagamento, mudando o status para completo (pago) de forma definitiva.

### 2. Single Action Controllers (Actions)
Criamos as seguintes Actions invocáveis no namespace `Alpha\Admin\Controllers\Actions\POS`:
* [ShowCashierDashboardAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/POS/ShowCashierDashboardAction.php): Renderiza a interface do caixa (` pos/cashier/layout.twig`).
* [GetPreOrderAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/POS/GetPreOrderAction.php): Consulta o banco e retorna detalhes estruturados da pré-venda em JSON.
* [PayOrderAction.php](file:///var/www/html/agsonhos/core/Admin/Controllers/Actions/POS/PayOrderAction.php): Finaliza a venda transicionando o status do pedido para "Completo" (status ID 5) de forma atômica utilizando o `UnitOfWork`.

### 3. Camada de Apresentação (Twig & CSS)
Desenvolvemos uma interface escura (Dark Mode) premium sob a pasta de recursos `resources/views/ pos/`:
* [layout.twig](file:///var/www/html/agsonhos/resources/views/%20pos/cashier/layout.twig): Implementação completa do terminal do caixa com pesquisa dinâmica por ID de ticket, detalhamento de itens, aba de pagamentos (Pix com simulação de QR Code, cartão e dinheiro físico com cálculo de troco) e impressão automática do comprovante.

---

## Validação e Testes

Criamos um script de testes de integração em [TestPOSCashier.php](file:///var/www/html/agsonhos/tests/TestPOSCashier.php) que valida todo o fluxo do caixa:
1. **Compilação:** Confirma que todos os novos controladores do caixa foram carregados no autoloader.
2. **Criação de Teste:** Salva um pedido com status pendente (1) e deduz o estoque em 1 unidade (simulando a pré-venda).
3. **Consulta de Ticket:** Valida a recuperação das informações da pré-venda.
4. **Finalização de Pagamento:** Executa o pagamento no Caixa (ID 5 - Completo) e confirma se a transição ocorreu perfeitamente e o estoque permaneceu reservado (sem dupla dedução).
5. **Limpeza do Banco:** Remove o pedido de teste e restaurar o estoque original usando `UnitOfWork`.

### Execução dos Testes
A execução do teste retornou sucesso absoluto e limpo:
```bash
php tests/TestPOSCashier.php
```

```
==================================================
INICIANDO TESTES DO CAIXA PDV (POS CASHIER)
==================================================

1. Verificando as novas classes de controle (Actions):
  [OK] Class compiled and loaded: Alpha\Admin\Controllers\Actions\POS\ShowCashierDashboardAction
  [OK] Class compiled and loaded: Alpha\Admin\Controllers\Actions\POS\ShowCashierCheckoutAction
  [OK] Class compiled and loaded: Alpha\Admin\Controllers\Actions\POS\GetPreOrderAction
  [OK] Class compiled and loaded: Alpha\Admin\Controllers\Actions\POS\PayOrderAction

2. Criando pedido de teste (Pendente - status 1):
  [OK] Pedido de teste criado com sucesso! Order ID: 42

3. Simulando consulta de pré-venda (GetPreOrderAction):
  [OK] Pedido localizado no banco! Status ID: 1
  [OK] Status está correto (1 - Pendente).

4. Simulando confirmação de pagamento e transição de status (PayOrderAction):
  [OK] Transição de status efetuada com sucesso: 1 (Pendente) -> 5 (Completo)
  [OK] Integridade do estoque mantida (sem dupla dedução): 0 unidades

5. Limpando dados de teste do banco de dados:
  [OK] Banco de dados limpo e estoque restaurado com sucesso!

==================================================
TODOS OS TESTES DO CAIXA PASSARAM COM SUCESSO!
==================================================
```

