# UC_CLI_018 - Alterar Senha Logado

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_018` |
| **Nome** | Alterar Senha Logado |
| **Módulo** | Loja Virtual - Área "Minha Conta" |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Segurança |
| **Frequência de Uso** | Baixa |
| **Rastreabilidade** | **RF:** [RF014](/docs/requirements/functional/functional_requirements.yaml) (Auth), [RF015](/docs/requirements/functional/functional_requirements.yaml) (Redefinição de senha)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Hash seguro Argon2id) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente que já se encontra autenticado alterar sua credencial de acesso (`/account/password` ou `/account/resetar-senha`), exigindo a confirmação da senha atual antes de cadastrar e persistir o novo hash de segurança.

---

## 2. ⚡ Pré-Condições
- Cliente autenticado na sessão.

---

## 3. ✅ Pós-Condições
- Novo hash criptografado gerado e gravado no banco de dados.
- E-mail de alerta de segurança despachado informando a alteração da senha.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica em "Alterar Senha" no menu da área do cliente.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa a tela de alteração de senha.
2. **Sistema:** Exibe os campos: "Senha Atual", "Nova Senha" e "Confirmar Nova Senha".
3. **Ator:** Preenche sua senha atual e digita a nova senha desejada duas vezes.
4. **Ator:** Clica em "Salvar Senha".
5. **Sistema:** Valida que a senha atual coincide com o hash do banco.
6. **Sistema:** Valida a força da nova senha e que ambos os campos de nova senha são idênticos.
7. **Sistema:** Gera o novo hash seguro com `Argon2id` e atualiza a coluna `password` na tabela `tbkk_customer`.
8. **Sistema:** Despacha e-mail informativo: *"Aviso de Segurança: Sua senha foi alterada com sucesso."*
9. **Sistema:** Exibe mensagem de sucesso em tela.

---

## 6. 🔀 Fluxos Alternativos

- N/A.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Senha Atual Incorreta:**
  1. O cliente erra a digitação de sua senha antiga.
  2. O sistema bloqueia a alteração e sinaliza: *"A senha atual informada está incorreta."*
- **FE02 - Confirmação Divergente:**
  1. O campo "Nova Senha" e "Confirmar Nova Senha" não coincidem.
  2. O sistema exibe: *"As senhas digitadas não são iguais."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RNF003:** Aplicação das políticas de complexidade mínima de senha (mínimo 8 caracteres).

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `old_password`, `password`, `confirm`.

### Saídas:
- Mensagem de sucesso e renovação da credencial.
