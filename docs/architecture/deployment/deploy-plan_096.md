# DP-96: Implementação de Configuração para Permitir/Bloquear Compras de Visitantes (Guest Checkout)

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-28 16:07:02
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/96

## Descrição

# Plano de Implementação: Configuração para Permitir/Bloquear Compras de Visitantes (Guest Checkout)

Este plano descreve as alterações necessárias para permitir que o lojista configure no Painel Administrativo se a loja aceita ou não compras de usuários anônimos (visitantes / *guest checkout*), aplicando a restrição em toda a jornada de compra e no processamento do pedido.

---

## 1. Visão Geral das Mudanças

Atualmente, a Alpha Engine possui o parâmetro `config_checkout_guest` previsto no banco de dados (`agsc_setting`), mas ele ainda não está exposto na interface visual de Configurações da Loja (`/admin/configuracoes`) nem aplicado de forma restritiva no formulário de Checkout e na ação de fechamento do pedido (`SubmitCheckoutAction`).

### Objetivos:
1. **Painel Administrativo**:
   - Adicionar uma aba/seção dedicada de **"Opções de Compra" (Checkout)** nas Configurações da Loja.
   - Permitir ao administrador alternar o recurso **"Permitir Compras como Visitante (Guest Checkout)"** entre Habilitado (Sim) e Desabilitado (Não).
   - Persistir e validar a chave `config_checkout_guest` no banco via `UpdateStoreSettingAction`.

2. **Frontend & Experiência do Usuário (Checkout)**:
   - No controlador `Checkout.php`, injetar a flag `allow_guest_checkout` no template Twig.
   - No template `checkout.twig`:
     - Quando desabilitado (`config_checkout_guest = 0`), ocultar o botão *"Comprar como visitante"*.
     - Exibir mensagem/aviso orientando o cliente a entrar na conta ou se cadastrar para concluir a compra.
     - Selecionar por padrão a aba de Login ou Cadastro.
   - No script `checkout.js`:
     - Impedir avanço na Etapa 1 sem autenticação prévia caso o modo visitante esteja desabilitado.

3. **Backend & Segurança da API (`SubmitCheckoutAction`)**:
   - Validar no servidor se o usuário é anônimo (`customer_id == 0`) e a loja está configurada com `config_checkout_guest == 0`.
   - Rejeitar a criação do pedido caso um visitante tente burlar a interface via POST/API direta, retornando erro descritivo e seguro.

---

## 2. Mudanças Propostas

### Backend & Configuração da Loja (Admin)

#### [MODIFY] [UpdateStoreSettingAction.php](/backend/core/Admin/Controllers/Actions/Setting/StoreSetting/UpdateStoreSettingAction.php)
- Capturar `config_checkout_guest` do formulário (valor `1` ou `0`).
- Salvar o valor em `$newSettings['config_checkout_guest']`.

#### [MODIFY] [edit.html.twig](/backend/resources/views/admin/setting/store_setting/edit.html.twig)
- Adicionar botão de aba `Opções de Compra` (`tab-btn-options`) no cabeçalho de abas.
- Adicionar o painel da aba `tab-content-options` com layout moderno e elegante:
  - Card estilizado para opções de checkout.
  - Seletor / Switch para "Permitir compras de visitantes (Guest Checkout)".
  - Texto explicativo de ajuda (tooltip / hint) detalhando o impacto para o consumidor.

---

### Backend & Frontend de Checkout (Loja Virtual)

#### [MODIFY] [StoreSettings.php](/backend/core/Support/StoreSettings.php)
- Adicionar mapeamento da chave `checkoutGuest` no array transformado para consumo em templates e helpers.

#### [MODIFY] [Checkout.php](/backend/core/Controller/Actions/Cart/Checkout.php)
- Obter `$allowGuestCheckout = (bool)($configSettings['config_checkout_guest'] ?? 1);`.
- Passar a variável `allow_guest_checkout` para a visualização Twig.

#### [MODIFY] [checkout.twig](/backend/resources/views/pages/cart/checkout.twig)
- Condicionar a exibição do botão `Comprar como visitante` à variável `allow_guest_checkout`.
- Caso `allow_guest_checkout` seja falso e o usuário não esteja logado, exibir alerta explicativo e abrir automaticamente a opção de login ou cadastro.

#### [MODIFY] [checkout.js](/public_html/js/cart/checkout.js)
- Ajustar comportamento da Etapa 1 para respeitar a ausência do botão visitante e selecionar a primeira opção disponível caso não esteja logado.

#### [MODIFY] [SubmitCheckoutAction.php](/backend/core/Controller/Actions/Cart/SubmitCheckoutAction.php)
- Adicionar trava de segurança no backend: se `customer_id <= 0` e `config_checkout_guest == '0'`, bloquear a finalização do pedido e retornar mensagem de erro para o cliente.

---

### Idiomas & Mensagens

#### [MODIFY] [pt-br.checkout.checkout.json](/backend/Locales/pt-br/pt-br.checkout.checkout.json)
- Adicionar mensagens de feedback para compra de visitante desabilitada.

---

## 3. Plano de Verificação

### Testes Automatizados (PHPUnit)
- Criar/atualizar teste unitário em `tests/Validation/GuestCheckoutSettingTest.php`:
  1. Testar que `SubmitCheckoutAction` permite pedido de visitante quando `config_checkout_guest = 1`.
  2. Testar que `SubmitCheckoutAction` bloqueia pedido de visitante quando `config_checkout_guest = 0`.
  3. Testar atualização da configuração no `SettingRepository` e `UpdateStoreSettingAction`.
- Executar a suíte de testes:
  ```bash
  vendor/bin/phpunit
  ```

### Testes E2E (Playwright)
- Executar fluxo de checkout com Playwright:
  ```bash
  npx playwright test e2e/specs/cart/checkout-flow.spec.ts --project=chromium
  ```

### Verificação Manual
- Acessar o painel administrativo (`/LPDHED2dC7Gjrg2b/configuracoes`), aba **Opções de Compra**, desabilitar a opção "Permitir compras de visitantes" e salvar.
- Acessar a loja como visitante, adicionar um produto e ir para o checkout (`/pt-br/checkout`).
- Verificar que o botão "Comprar como visitante" não aparece e que a mensagem instrutiva para login/cadastro é apresentada.
- Tentar submeter pedido via requisição direta sem login e confirmar o bloqueio de segurança.

# Walkthrough: Configuração de Compras de Usuários Anônimos (Guest Checkout)

Implementamos a funcionalidade completa para gerenciar e restringir compras de usuários anônimos (visitantes) nas configurações da loja virtual.

---

## 🎯 O que foi feito

### 1. Painel Administrativo (`/admin/configuracoes`)
- **Aba "Opções de Compra"**: Criada nova aba dedicada na tela de configurações da loja ([`edit.html.twig`](/backend/resources/views/admin/setting/store_setting/edit.html.twig)).
- **Controle Segmentado (*Segmented Control*)**: Adicionado controle visual moderno para a chave `config_checkout_guest`:
  - **Permitir (Sim)**: `config_checkout_guest = 1`
  - **Exigir Cadastro (Não)**: `config_checkout_guest = 0`
- **Persistência de Dados**: Atualizada a ação de backend [`UpdateStoreSettingAction.php`](/backend/core/Admin/Controllers/Actions/Setting/StoreSetting/UpdateStoreSettingAction.php) para validar e salvar o parâmetro na tabela `setting`.

### 2. Frontend do Checkout (`/checkout`)
- **Controlador de Checkout**: No arquivo [`Checkout.php`](/backend/core/Controller/Actions/Cart/Checkout.php), a flag `allow_guest_checkout` é injetada no template Twig a partir das configurações ativas.
- **Visualização de Identificação**: Em [`checkout.twig`](/backend/resources/views/pages/cart/checkout.twig), o botão *"Comprar como visitante"* é condicionado à flag `allow_guest_checkout`.
  - Quando desabilitado, um aviso amigável orienta o comprador a fazer login ou criar uma conta.
- **Validação de Fluxo no JS**: No script [`checkout.js`](/public_html/js/cart/checkout.js), a transição entre etapas bloqueia tentativas de avançar como visitante quando a funcionalidade estiver desligada.

### 3. Trava de Segurança no Fechamento do Pedido (`POST /checkout`)
- Em [`SubmitCheckoutAction.php`](/backend/core/Controller/Actions/Cart/SubmitCheckoutAction.php), adicionamos validação no backend que rejeita compras anônimas (`customer_id == 0`) caso `config_checkout_guest == 0`, retornando status `403` com erro `GUEST_CHECKOUT_DISABLED` (ou redirecionando com mensagem de erro na sessão).

### 4. Helper e Internacionalização
- Atualizado [`StoreSettings.php`](/backend/core/Support/StoreSettings.php) para mapear `$store['checkoutGuest']`.
- Atualizados os arquivos de tradução em Português ([`pt-br.admin.setting.json`](/backend/Locales/pt-br/pt-br.admin.setting.json), [`pt-br.checkout.json`](/backend/Locales/pt-br/pt-br.checkout.json)) e Inglês ([`en-gb.admin.setting.json`](/backend/Locales/en-gb/en-gb.admin.setting.json), [`en-gb.checkout.json`](/backend/Locales/en-gb/en-gb.checkout.json)).

---

## 🧪 Verificação & Testes

### 1. Testes Unitários e de Integração (PHPUnit)
Criado o arquivo de testes [`GuestCheckoutSettingTest.php`](/tests/Validation/GuestCheckoutSettingTest.php) cobrindo:
- Mapeamento correto da chave no helper `StoreSettings`.
- Bloqueio de pedido anônimo quando `config_checkout_guest = 0`.
- Liberação de checkout para cliente logado quando `config_checkout_guest = 0`.
- Persistência e restauração do parâmetro via `UpdateStoreSettingAction`.

**Resultado da execução**:
```bash
PHPUnit 13.3.1
Tests: 97, Assertions: 346, Failures: 0
OK (100% dos testes passando)
```

### 2. Testes E2E (Playwright)
Executadas as suítes de compras e checkout:
```bash
npx playwright test e2e/specs/cart/checkout-flow.spec.ts --project=chromium
npx playwright test e2e/specs/cart/guest-checkout-toggle.spec.ts --project=chromium
```
**Resultado**: Todos os testes E2E executados com sucesso (100% Passing).

