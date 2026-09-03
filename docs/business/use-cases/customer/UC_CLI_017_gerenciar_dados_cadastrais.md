# UC_CLI_017 - Gerenciar Dados Cadastrais

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_017` |
| **Nome** | Gerenciar Dados Cadastrais |
| **Módulo** | Loja Virtual - Área "Minha Conta" |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Cadastral |
| **Frequência de Uso** | Baixa a Média |
| **Rastreabilidade** | **RF:** [RF014](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Cadastro e perfil)<br>**RNF:** [RNF003](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Proteção de dados/LGPD e validação de integridade) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente autenticado visualizar e atualizar seus dados pessoais e de contato cadastrados na plataforma (`/account/edit`), tais como Nome Completo, Telefone celular para avisos de entrega via WhatsApp/SMS e Inscrição Estadual (no caso de Pessoa Jurídica).

---

## 2. ⚡ Pré-Condições
- Cliente autenticado na sessão.

---

## 3. ✅ Pós-Condições
- Dados cadastrais atualizados na tabela `tbkk_customer` e confirmação exibida ao usuário.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica em "Editar Dados Cadastrais" ou "Meus Dados" no painel da conta.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa a rota `/account/edit`.
2. **Sistema:** Carrega os dados atuais do cliente (Nome, Sobrenome, E-mail, Telefone, CPF/CNPJ).
3. **Sistema:** Renderiza o formulário com os dados pré-preenchidos (com CPF/CNPJ bloqueado para edição direta por conformidade fiscal).
4. **Ator:** Altera o número de telefone de contato ou o e-mail.
5. **Ator:** Clica em "Salvar Alterações".
6. **Sistema:** Valida o formato dos campos e a unicidade do e-mail no banco.
7. **Sistema:** Atualiza a tabela `tbkk_customer`, renova a sessão em Redis e exibe notificação de sucesso: *"Seus dados foram atualizados com sucesso!"*.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Alteração de E-mail com Revalidação:**
  1. O cliente altera seu e-mail principal.
  2. O sistema envia um e-mail de confirmação para o novo endereço com um link de validação para confirmar a propriedade.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - E-mail Já em Uso por Outro Usuário:**
  1. O cliente tenta trocar seu e-mail para um que já pertence a outra conta.
  2. O sistema impede a alteração e exibe alerta: *"Este e-mail já está sendo utilizado por outra conta."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RNF003 (LGPD):** Bloqueio de edição arbitrária de CPF/CNPJ para preservar a integridade fiscal de pedidos passados e notas emitidas.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `firstname`, `lastname`, `email`, `telephone`, `custom_field`.

### Saídas:
- Mensagem de confirmação de atualização cadastral.
