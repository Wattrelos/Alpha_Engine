# UC_CLI_024 - Gerenciar Inscrição na Newsletter

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_024` |
| **Nome** | Gerenciar Inscrição na Newsletter |
| **Módulo** | Loja Virtual - Área "Minha Conta" |
| **Atores Primários** | Cliente Logado (*Customer*), Visitante (*Guest*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Preferências |
| **Frequência de Uso** | Baixa |
| **Rastreabilidade** | **RF:** [RF014](/docs/requirements/functional/functional_requirements.yaml) (Perfil do usuário)<br>**RN:** [RN018](/docs/requirements/business_rules/business_rules.yaml) (Campanhas por categoria e ofertas exclusivas)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Consentimento explícito e opt-out LGPD) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente gerenciar sua preferência de recebimento de e-mails de marketing, promoções sazonais (ex: "Semana do Piso", "Mês das Ferramentas") e comunicados de lançamentos (`/account/newsletter`), podendo ativar (*Opt-in*) ou desativar (*Opt-out*) a assinatura a qualquer momento com 1 clique, em total conformidade com as diretrizes da LGPD.

---

## 2. ⚡ Pré-Condições
- Cliente acessando a página de newsletter ou rodapé da loja.

---

## 3. ✅ Pós-Condições
- Flag `newsletter` atualizada na tabela `tbkk_customer` (para cliente logado) ou e-mail registrado na tabela de inscritos.

---

## 4. 🚀 Gatilho (Trigger)
O usuário altera a opção no formulário de newsletter no painel ou digita seu e-mail na caixa de newsletter do rodapé.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa `/account/newsletter`.
2. **Sistema:** Exibe a opção atual de inscrição com botões de rádio `( ) Sim, quero receber ofertas e cupons por e-mail` e `( ) Não desejo receber e-mails promocionais`.
3. **Ator:** Marca a opção desejada (ex: "Não") e clica em "Salvar".
4. **Sistema:** Atualiza a coluna `newsletter = 0` no banco de dados.
5. **Sistema:** Exibe notificação de sucesso: *"Suas preferências de comunicação foram atualizadas com sucesso."*

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Inscrição Rápida pelo Rodapé da Loja (Visitante):**
  1. Um visitante anônimo digita seu e-mail no formulário do rodapé e clica em "Inscrever-se".
  2. O sistema valida o e-mail, grava na base de leads e exibe mensagem de boas-vindas com cupom de primeira compra.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Formato de E-mail Inválido:**
  1. O usuário digita um e-mail com sintaxe incorreta no rodapé.
  2. O sistema exibe mensagem de validação: *"Por favor, insira um endereço de e-mail válido."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RNF003 (Conformidade LGPD):** Garantia de cancelamento imediato e incondicional de comunicações de marketing (*One-Click Unsubscribe*).

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `newsletter` (boolean `0` ou `1`) ou `email`.

### Saídas:
- Mensagem de confirmação de preferência de envio.
