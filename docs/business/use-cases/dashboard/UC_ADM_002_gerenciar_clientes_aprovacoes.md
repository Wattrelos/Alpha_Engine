# UC_ADM_002 - Gerenciar Clientes & Grupos de Clientes

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_ADM_002` |
| **Nome** | Gerenciar Clientes e Grupos de Clientes B2B |
| **Módulo** | Painel Administrativo - Operações de Negócio |
| **Atores Primários** | Operador do Painel (*Operator*), Administrador Geral (*Admin*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Gestão de Clientes |
| **Frequência de Uso** | Média |
| **Rastreabilidade** | **RF:** [RF014](/docs/requirements/functional/functional_requirements.yaml) (Cadastro de clientes), [RF017](/docs/requirements/functional/functional_requirements.yaml) (Múltiplos Shiptos), [RF023](/docs/requirements/functional/functional_requirements.yaml) (Precificação segmentada)<br>**RN:** [RN017](/docs/requirements/business_rules/business_rules.yaml) (Diferenciação de preço atacado B2B vs. varejo)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Conformidade LGPD e anonimização de dados) |

---

## 1. 🎯 Descrição Sumária
Permite aos operadores gerenciar a base cadastral de clientes (Pessoa Física e Pessoa Jurídica), analisar solicitações de enquadramento em grupos corporativos B2B (Construtoras, Engenheiros, Empreiteiros), liberar limites de crédito para faturamento a prazo, bloquear contas inadimplentes e auditar múltiplos endereços de entrega de obras.

---

## 2. ⚡ Pré-Condições
- Operador autenticado no painel com permissão no módulo `customer/customer`.

---

## 3. ✅ Pós-Condições
- Conta de cliente aprovada, promovida para o grupo B2B ou bloqueada na tabela `tbkk_customer`.

---

## 4. 🚀 Gatilho (Trigger)
O operador acessa "Clientes > Clientes" ou "Aprovações Pendentes" no painel.

---

## 5. 🔄 Fluxo Principal (Aprovar Cadastro B2B de Construtora)

1. **Ator:** Acessa a fila de cadastros PJ pendentes de validação de Inscrição Estadual.
2. **Sistema:** Exibe a ficha da empresa: Razão Social, CNPJ, Inscrição Estadual (IE), CNAE de Construção Civil e dados do responsável.
3. **Ator:** Consulta a regularidade cadastral no Sintegra/Receita Federal e clica em "Aprovar como Construtora B2B".
4. **Sistema:** Altera o `customer_group_id` do cliente para o grupo `Atacado / Construtoras` (RN017).
5. **Sistema:** Dispara e-mail automático ao cliente: *"Seu cadastro corporativo foi aprovado! Aproveite os preços exclusivos de atacado."*
6. **Sistema:** Registra a ação no log de auditoria administrativa.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Atribuição de Limite de Crédito para Boleto Faturado:**
  1. O operador analisa o score financeiro da construtora e define um limite de R$ 50.000,00 para compras a prazo.
  2. O sistema habilita a opção de boleto a prazo no checkout exclusivo daquele cliente.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Bloqueio de Cliente Inadimplente ou Suspeito:**
  1. O operador altera o status do cliente para `Bloqueado / Inativo`.
  2. O sistema encerra imediatamente qualquer sessão ativa do usuário no Redis e bloqueia novas tentativas de login.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN017:** Regras de transição de tabelas tarifárias de varejo para atacado corporativo.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Ações de aprovação, alteração de grupo, atribuição de limite de crédito.

### Saídas:
- Painel de clientes com filtros por grupo, status e volume de compras.
