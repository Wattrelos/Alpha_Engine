# UC_CORE_003 - Autenticação & Cadastro

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CORE_003` |
| **Nome** | Autenticação & Cadastro Dual PF/PJ |
| **Módulo** | Núcleo Central (Core) - Segurança & Gestão de Identidade |
| **Atores Primários** | Visitante (*Guest*), Cliente Logado (*Customer*) |
| **Atores Secundários** | Serviço de Consulta Sintegra/Receita (Validação IE/CNPJ), Servidor SMTP |
| **Tipo** | Condução / Identidade & Controle de Acesso |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF014](/docs/requirements/functional/functional_requirements.yaml) (Autenticação e cadastro), [RF015](/docs/requirements/functional/functional_requirements.yaml) (Recuperação de credenciais)<br>**RN:** [RN017](/docs/requirements/business_rules/business_rules.yaml) (Segmentação PF vs PJ/Construtora)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Criptografia de credenciais e proteção LGPD) |

---

## 1. 🎯 Descrição Sumária
Gerencia o onboarding e a autenticação segura dos usuários na plataforma, oferecendo fluxo de cadastro dual: **Pessoa Física (Consumidor Final)** com validação de CPF e **Pessoa Jurídica (Construtoras, Empreiteiras e Instaladores)** com validação de CNPJ e Inscrição Estadual (IE) ativa. O caso de uso abrange o login com proteção contra força bruta, emissão de sessão e recuperação de senha por e-mail com token temporário criptografado.

---

## 2. ⚡ Pré-Condições
1. O usuário deve possuir um navegador web com suporte a cookies/sessão e conexão segura HTTPS (TLS 1.3).
2. Para clientes PJ, o CNPJ deve estar ativo e regular junto ao Cadastro Nacional.

---

## 3. ✅ Pós-Condições
- Conta de usuário criada e persistida nas tabelas `tbkk_customer` e `tbkk_address`.
- Atribuição do grupo de cliente (`customer_group_id`): Grupo Padrão (Varejo) para PF ou Grupo Construtora (Atacado) para PJ com IE aprovada.
- Sessão de autenticação emitida e sincronizada no Redis.

---

## 4. 🚀 Gatilho (Trigger)
O usuário clica no botão "Entrar / Cadastrar-se" no topo do portal ou no botão "Esqueci minha senha".

---

## 5. 🔄 Fluxo Principal (Cadastro de Nova Conta PJ - Construtora)

1. **Ator:** Acessa a página de autenticação e clica em "Criar Nova Conta".
2. **Sistema:** Exibe a opção de escolha do perfil de cadastro: **Pessoa Física (CPF)** ou **Pessoa Jurídica (CNPJ / Construtora)**.
3. **Ator:** Seleciona "Pessoa Jurídica (CNPJ)".
4. **Sistema:** Apresenta o formulário específico contendo:
   - Razão Social, Nome Fantasia, CNPJ;
   - Inscrição Estadual (IE) ou indicação de "Isento";
   - E-mail corporativo, Telefone/WhatsApp do setor de compras;
   - Endereço da sede fiscal com CEP (autopreenchimento via API de CEP);
   - Definição de senha de acesso forte (mínimo 8 caracteres, números e símbolos).
5. **Ator:** Preenche os dados corporativos e clica em "Concluir Cadastro".
6. **Sistema:** Executa validação de formato e dígito verificador do CNPJ e consulta o status cadastral da Inscrição Estadual.
7. **Sistema:** Valida que o e-mail e CNPJ ainda não existem na base.
8. **Sistema:** Aplica hash criptográfico seguro (Argon2id / Bcrypt com fator de custo elevado) sobre a senha.
9. **Sistema:** Vincula o usuário ao grupo de cliente **B2B / Construtora** (concedendo acesso à tabela diferenciada de preços - RN017).
10. **Sistema:** Envia e-mail de boas-vindas com orientações comerciais e autentica o usuário automaticamente, redirecionando-o para a vitrine com os preços corporativos ativados.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Cadastro de Pessoa Física (PF):**
  1. No passo 3, o ator escolhe "Pessoa Física".
  2. O sistema solicita Nome Completo, CPF, Data de Nascimento, E-mail e Senha.
  3. O usuário é vinculado ao grupo de clientes padrão (Varejo).
- **FA02 - Recuperação de Senha por Token:**
  1. O usuário clica em "Esqueci minha senha" e informa o e-mail cadastrado.
  2. O sistema gera um token seguro com hash de 64 caracteres e validade estrita de 15 minutos.
  3. Despacha link de redefinição por e-mail: `https://site/index.php?route=account/reset&token=...`.
  4. O usuário clica no link, digita a nova senha e o sistema atualiza a credencial no banco.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Inscrição Estadual Inválida ou Rejeitada na SEFAZ:**
  1. No passo 6, o CNPJ é de contribuinte do ICMS, mas a Inscrição Estadual informada está suspensa, baixada ou incorreta.
  2. O sistema impede a conclusão do cadastro PJ com alerta orientativo: *"A Inscrição Estadual informada não confere com o cadastro da SEFAZ para este CNPJ. Verifique os dados ou cadastre-se como Consumidor Não Contribuinte."*.
- **FE02 - Tentativas Excessivas de Senha Incorreta (Brute Force):**
  1. No fluxo de login, o usuário erra a senha 5 vezes consecutivas.
  2. O sistema bloqueia temporariamente as requisições originadas pelo IP e e-mail por 15 minutos, exigindo resolução de CAPTCHA.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN017 (Preço Varejo e Atacado):** A validação do tipo de cadastro (PJ com IE ativa) é a chave que destrava automaticamente as condições de atacado e faturamento faturado para construtoras.
- **RNF003 (Proteção de Dados e Segurança):** Todos os dados pessoais e corporativos são armazenados sob conformidade com a LGPD e senhas em hashes irreversíveis.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Perfil (`account_type`: `PF` ou `PJ`).
- `fullname` / `company_name`, `cpf_cnpj`, `state_registration` (IE).
- `email`, `telephone`, `password`, `password_confirm`, `address_cep`.

### Saídas:
- Sessão criptografada gerada no Redis (`PHPSESSID`).
- Atribuição do grupo de clientes (`customer_group_id`).
- Notificação de e-mail disparada via SMTP.
