# UC_CLI_014 - Recuperar Senha por E-mail

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_014` |
| **Nome** | Recuperar Senha por E-mail |
| **Módulo** | Loja Virtual - Autenticação & Sessão |
| **Atores Primários** | Visitante (*Guest*), Cliente Logado (*Customer*) |
| **Atores Secundários** | Servidor SMTP / Fila de E-mails RabbitMQ, Sistema Alpha Engine |
| **Tipo** | Condução / Recuperação |
| **Frequência de Uso** | Média |
| **Rastreabilidade** | **RF:** [RF015](/docs/requirements/functional/functional_requirements.yaml) (Recuperação de credenciais)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Tokens temporários com hash criptográfico e expiração em 30 min) |

---

## 1. 🎯 Descrição Sumária
Permite ao usuário que esqueceu sua senha de acesso solicitar um link de redefinição seguro enviado por e-mail transacional, gerando um token criptográfico único e com validade de 30 minutos, permitindo cadastrar uma nova senha e restabelecer o acesso à sua conta.

---

## 2. ⚡ Pré-Condições
- O e-mail informado deve existir na base de dados de clientes.

---

## 3. ✅ Pós-Condições
- Nova senha gravada com hash Argon2id e token de recuperação invalidado imediatamente.
- Sessões anteriores do usuário revogadas no Redis por segurança.

---

## 4. 🚀 Gatilho (Trigger)
O usuário clica no link "Esqueci minha senha" na tela de login (`/account/forgotten`).

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa `/account/forgotten`, insere seu e-mail cadastrado e clica em "Continuar".
2. **Sistema:** Valida o formato do e-mail e consulta a base de dados.
3. **Sistema:** Gera um token seguro de 64 caracteres hexadecimais aleatórios (`random_bytes(32)`) e grava o hash do token com timestamp de expiração (30 minutos) na tabela `tbkk_customer_reset_password`.
4. **Sistema:** Despacha uma mensagem para a fila do RabbitMQ contendo os dados do e-mail transacional com o link único: `https://loja.com/account/reset?code={token}`.
5. **Sistema:** Exibe tela informando: *"Se o e-mail informado estiver cadastrado em nossa base, você receberá um link de redefinição em instantes."* (Resposta cega para evitar enumeração de usuários).
6. **Ator:** Abre o e-mail recebido e clica no link de redefinição.
7. **Sistema:** Valida que o token é autêntico e não expirou, exibindo o formulário de "Nova Senha" e "Confirmação de Senha".
8. **Ator:** Digita a nova senha desejada e clica em "Salvar Nova Senha".
9. **Sistema:** Gera o novo hash da senha com Argon2id, atualiza o registro na tabela `tbkk_customer`, remove o token utilizado e exibe mensagem de sucesso com link para login.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Cliente Desiste ou Lembra a Senha:**
  1. O cliente ignora o e-mail recebido.
  2. O token expira após 30 minutos sem causar qualquer alteração na conta.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Token Expirado ou Inválido:**
  1. O usuário clica no link após mais de 30 minutos da solicitação.
  2. O sistema detecta a expiração e exibe alerta: *"Este link de redefinição expirou ou já foi utilizado. Por favor, faça uma nova solicitação."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RNF003 (Segurança):** Não expor mensagens informando se o e-mail existe ou não na tela inicial para prevenir ataques de enumeração (*User Enumeration*).

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `email` (na solicitação).
- `password`, `confirm_password` (na redefinição).

### Saídas:
- Mensagem de envio de instruções e tela de redefinição bem-sucedida.
