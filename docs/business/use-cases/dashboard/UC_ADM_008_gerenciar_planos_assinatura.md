# UC_ADM_008 - Gerenciar Planos de Assinatura

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_ADM_008` |
| **Nome** | Gerenciar Planos de Assinatura & Cobranças Recorrentes |
| **Módulo** | Painel Administrativo - Operações do Sistema |
| **Atores Primários** | Administrador Geral (*Admin*) |
| **Atores Secundários** | Gateway de Recorrência, Sistema Alpha Engine |
| **Tipo** | Condução / Gestão de Cobrança Recorrente |
| **Frequência de Uso** | Baixa |
| **Rastreabilidade** | **RF:** [RF018](/docs/requirements/functional/functional_requirements.yaml) (Checkout/Pagamentos), [RF023](/docs/requirements/functional/functional_requirements.yaml) (Gestão administrativa)<br>**RN:** [RN017](/docs/requirements/business_rules/business_rules.yaml) (Preços e assinaturas corporativas)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Segurança e conciliação financeira de assinaturas) |

---

## 1. 🎯 Descrição Sumária
Permite ao Administrador Geral configurar e gerenciar planos de assinatura recorrente para clientes corporativos (ex: Clubes de Vantagens da Construção, Planos de Desconto Recorrente para Empreiteiros e Assinaturas de Entrega Expressa Ilimitada), definindo periodicidade de cobrança (mensal, trimestral, anual), taxas de adesão, ciclos de faturamento e status das assinaturas ativas.

---

## 2. ⚡ Pré-Condições
- Administrador Geral autenticado.

---

## 3. ✅ Pós-Condições
- Planos de assinatura cadastrados na tabela `tbkk_subscription_plan` e vínculos com clientes gerenciados em `tbkk_customer_subscription`.

---

## 4. 🚀 Gatilho (Trigger)
O Administrador acessa "Vendas > Planos de Assinatura" ou "Sistema > Recorrência".

---

## 5. 🔄 Fluxo Principal (Cadastrar Novo Plano de Assinatura)

1. **Ator:** Acessa a tela de gestão de planos de assinatura e clica em "Novo Plano".
2. **Sistema:** Exibe o formulário de parametrização:
   - Nome do Plano (ex: *"Clube Construtor VIP"*);
   - Descrição dos Benefícios (ex: *"Frete Grátis Ilimitado + 8% de Desconto em Toda a Loja"*);
   - Valor da Mensalidade (R$);
   - Frequência de Cobrança: Mensal, Semestral ou Anual;
   - Período de Teste Gratuito (*Trial*) em dias;
   - Grupo de Clientes Associado (promove o assinante automaticamente para a tabela VIP).
3. **Ator:** Preenche as configurações e clica em "Salvar".
4. **Sistema:** Valida e grava o plano no banco de dados.
5. **Sistema:** Disponibiliza o plano na vitrine para contratação pelos clientes.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Cancelamento ou Pausa de Assinatura:**
  1. O administrador visualiza um cliente assinante que solicitou cancelamento.
  2. O administrador suspende a renovação automática no gateway; o cliente mantém os benefícios até o encerramento do ciclo vigente.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Falha na Cobrança da Renovação Automática:**
  1. O gateway reporta falha de cobrança recorrente no cartão do assinante.
  2. O sistema altera o status para `Inadimplente / Período de Graça` e dispara notificação automática para atualização do cartão.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN017:** Atribuição dinâmica de vantagens e tabelas comerciais diferenciadas para membros assinantes.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `plan_name`, `price`, `frequency`, `trial_days`, `status`.

### Saídas:
- Plano de assinatura ativo e relatório de assinantes consolidado.
