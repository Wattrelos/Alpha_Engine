# UC_CLI_012 - Cadastrar Nova Conta (Sign-up PF/PJ)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_012` |
| **Nome** | Cadastrar Nova Conta |
| **Módulo** | Loja Virtual - Autenticação & Sessão |
| **Atores Primários** | Visitante (*Guest*) |
| **Atores Secundários** | API ViaCEP / Consulta Receita Federal, Sistema Alpha Engine |
| **Tipo** | Condução / Cadastral |
| **Frequência de Uso** | Alta |
| **Rastreabilidade** | **RF:** [RF014](/docs/requirements/functional/functional_requirements.yaml) (Autenticação e cadastro PF/PJ), [RF017](/docs/requirements/functional/functional_requirements.yaml) (Múltiplos Shiptos)<br>**RN:** [RN017](/docs/requirements/business_rules/business_rules.yaml) (Preço diferenciado PJ / Varejo e Atacado)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Proteção de dados/LGPD e Criptografia Argon2id) |

---

## 1. 🎯 Descrição Sumária
Permite ao visitante registrar uma conta de usuário na plataforma nas modalidades **Pessoa Física** (CPF) ou **Pessoa Jurídica / Construtora** (CNPJ com Inscrição Estadual e Razão Social), cadastrando credenciais criptografadas, endereço principal com preenchimento automático por CEP e consentimento de termos em conformidade com a LGPD.

---

## 2. ⚡ Pré-Condições
- Visitante acessando a página `/account/register`.

---

## 3. ✅ Pós-Condições
- Registro de cliente criado na tabela `tbkk_customer` com grupo de cliente correspondente (Varejo padrão ou Atacado B2B).
- Endereço gravado na tabela `tbkk_address`.
- Sessão autenticada iniciada automaticamente no Redis.

---

## 4. 🚀 Gatilho (Trigger)
O usuário clica no link "Cadastre-se" ou "Criar Conta" no cabeçalho ou durante o checkout.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa o formulário de cadastro e seleciona a opção "Pessoa Física (CPF)" ou "Pessoa Jurídica (CNPJ - Construtora / Empresa)".
2. **Ator:** Preenche os dados cadastrais (Nome/Razão Social, E-mail, Telefone celular, Senha segura e Confirmação).
3. **Ator:** Insere o CEP de residência/obra; o sistema consulta a API do ViaCEP e preenche automaticamente Logradouro, Bairro, Cidade e Estado.
4. **Ator:** Insere o número do imóvel e complemento.
5. **Ator:** Marca a caixa de consentimento dos Termos de Uso e Política de Privacidade (LGPD).
6. **Ator:** Clica em "Finalizar Cadastro".
7. **Sistema:** Valida o formato e integridade dos dados (dígitos verificadores do CPF/CNPJ, e-mail único na base, força da senha).
8. **Sistema:** Gera hash da senha utilizando algoritmo seguro `Argon2id` e insere o registro na tabela `tbkk_customer`.
9. **Sistema:** Se cadastrado como PJ com Inscrição Estadual, vincula automaticamente ao grupo `Atacado / Construtoras` (RN017).
10. **Sistema:** Inicia a sessão de login (`<<include>> UC_CLI_015`), funde eventual carrinho anônimo pré-existente e redireciona para o Painel do Cliente (`/account/dashboard`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Cadastro Rápido via Login Social (OAuth2):**
  1. O ator clica em "Cadastre-se com o Google".
  2. O provedor OAuth2 retorna nome e e-mail verificados. O sistema pré-popula o cadastro, solicitando apenas CPF/CNPJ e CEP para completar a ficha fiscal.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - E-mail ou CPF/CNPJ Já Cadastrado:**
  1. O usuário informa dados que já existem na base.
  2. O sistema sinaliza o campo e exibe: *"Este e-mail/CPF já possui uma conta ativa. Clique aqui para recuperar sua senha."*
- **FE02 - Senha Fraca:**
  1. O usuário digita uma senha com menos de 8 caracteres ou sem números/símbolos.
  2. O sistema bloqueia a submissão e exibe o medidor de força de senha orientando os requisitos mínimos.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN017 (Segmentação Varejo x Atacado):** Contas corporativas (PJ) com CNPJ válido são qualificadas para a tabela de preços de atacado e faturamento por boleto a prazo.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `customer_group_id`, `firstname`, `lastname`, `email`, `telephone`, `cpf_cnpj`, `ie`, `password`, `postcode`, `address_1`, `address_2`, `city`, `zone_id`, `agree`.

### Saídas:
- Mensagem de boas-vindas e redirecionamento para a área logada.
