# UC_CLI_013 - Autenticar-se (Login / Logout)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_013` |
| **Nome** | Autenticar-se (Login / Logout) |
| **Módulo** | Loja Virtual - Autenticação & Sessão |
| **Atores Primários** | Visitante (*Guest*), Cliente Logado (*Customer*) |
| **Atores Secundários** | Servidor Redis / Provedor OAuth2, Sistema Alpha Engine |
| **Tipo** | Condução / Segurança |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF014](/docs/requirements/functional/functional_requirements.yaml) (Autenticação Email/Senha e Social Auth)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Proteção de sessão, Argon2id, Anti-CSRF e Cookies HttpOnly; Secure) |

---

## 1. 🎯 Descrição Sumária
Permite ao usuário autenticar-se na loja virtual através de suas credenciais de e-mail e senha cadastrada ou via provedores de autenticação social (Google OAuth2), estabelecendo uma sessão segura em Redis com proteção contra CSRF, bem como efetuar o encerramento seguro de sua sessão (*Logout*).

---

## 2. ⚡ Pré-Condições
- Conta de cliente previamente cadastrada e ativa.

---

## 3. ✅ Pós-Condições
- **No Login:** Sessão criada no Redis (`sess:{session_id}`), cookie de sessão criptografado emitido com flags `HttpOnly; SameSite=Lax; Secure` e mesclagem de carrinho executada (`<<include>> UC_CLI_015`).
- **No Logout:** Destruição do token de sessão no Redis e no navegador, limpando dados sensíveis.

---

## 4. 🚀 Gatilho (Trigger)
O usuário clica em "Entrar / Minha Conta" no topo da página ou clica em "Sair" na área autenticada.

---

## 5. 🔄 Fluxo Principal (Login por E-mail e Senha)

1. **Ator:** Acessa a página `/account/login`, insere seu e-mail e senha e clica em "Entrar".
2. **Sistema:** Valida o token Anti-CSRF da requisição HTTP.
3. **Sistema:** Consulta o registro do cliente na tabela `tbkk_customer` pelo e-mail informado.
4. **Sistema:** Compara o hash da senha utilizando a função nativa `password_verify` (Argon2id/Bcrypt).
5. **Sistema:** Confirma a autenticidade e invoca `<<include>> UC_CLI_015 (Sincronizar Sessão Redis & Carrinho)`.
6. **Sistema:** Registra o log de acesso na tabela `tbkk_customer_login` com IP e User-Agent.
7. **Sistema:** Redireciona o usuário para a página em que estava anteriormente ou para o Painel do Cliente (`/account/dashboard`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Login Social com Google (OAuth2):**
  1. O usuário clica em "Entrar com Google".
  2. O sistema redireciona para o consentimento da conta Google, valida o retorno do token JWT, autentica a conta vinculada e inicia a sessão.
- **FA02 - Fluxo de Logout:**
  1. O cliente logado clica no botão "Sair / Encerrar Sessão".
  2. O sistema remove a chave da sessão no Redis, invalida o cookie no navegador e redireciona para `/account/logout` com mensagem de encerramento seguro.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Credenciais Incorretas:**
  1. O usuário erra o e-mail ou a senha.
  2. O sistema incrementa o contador de tentativas com rate limiting no Redis e exibe alerta genérico: *"E-mail ou senha incorretos."*
- **FE02 - Bloqueio por Tentativas Excessivas (Proteção contra Força Bruta):**
  1. Ocorrem mais de 5 tentativas consecutivas de senha inválida em 5 minutos para o mesmo IP.
  2. O sistema bloqueia novas requisições por 15 minutos e exige desafio CAPTCHA para desbloqueio.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RNF003 (Segurança da Informação):** Proteção integral de credenciais e isolamento de sessões de usuários.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `email`, `password`, `remember_me`, `_csrf_token`.

### Saídas:
- Sessão iniciada, cabeçalho de usuário atualizado com "Olá, [Nome]" e redirecionamento.
