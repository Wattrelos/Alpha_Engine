# UC_CLI_016 - Acessar Painel do Cliente

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_016` |
| **Nome** | Acessar Painel do Cliente |
| **Módulo** | Loja Virtual - Área "Minha Conta" |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Painel |
| **Frequência de Uso** | Alta |
| **Rastreabilidade** | **RF:** [RF014](/docs/requirements/functional/functional_requirements.yaml) (Auth), [RF016](/docs/requirements/functional/functional_requirements.yaml) (Histórico pedidos)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Ergonomia visual e navegação) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente autenticado acessar a visão geral de sua conta (`/account/dashboard` ou `/account`), exibindo um resumo de suas informações cadastrais, status dos pedidos mais recentes em andamento, atalhos rápidos para endereços de entrega de obras, extrato de transações e saldo de créditos ou orçamentos abertos.

---

## 2. ⚡ Pré-Condições
- Cliente autenticado na sessão (`$_SESSION['customer_id']`).

---

## 3. ✅ Pós-Condições
- Exibição do painel com menu lateral navegável e cards de resumo com dados em tempo real.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica em "Minha Conta" ou "Painel do Cliente" no cabeçalho após o login.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Clica no menu "Minha Conta".
2. **Sistema:** Valida a sessão ativa do usuário.
3. **Sistema:** Carrega as estatísticas do cliente:
   - Últimos pedidos e seus status (ex: "Em Separação", "Em Trânsito");
   - Endereço principal de entrega cadastrado;
   - Grupo do cliente (Varejo ou Atacado/Construtora);
   - Notificações de chamados de devolução (RMA) ou cotações (RFQ).
4. **Sistema:** Renderiza a página `/account` contendo o menu lateral com links para:
   - Meus Dados Cadastrais (`UC_CLI_017`);
   - Alterar Senha (`UC_CLI_018`);
   - Livro de Endereços (`UC_CLI_019`);
   - Lista de Desejos (`UC_CLI_020`);
   - Histórico de Pedidos (`UC_CLI_021`);
   - Extrato e Transações (`UC_CLI_022`);
   - Devoluções / RMA (`UC_CLI_023`);
   - Inscrição Newsletter (`UC_CLI_024`);
   - Meus Projetos e Cotações (`UC_CLI_026`).
5. **Ator:** Seleciona a opção desejada para gerenciar sua conta.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Acesso com Pedido em Rota de Entrega:**
  1. O cliente possui uma compra despachada nas últimas 24h.
  2. O painel exibe um card de destaque no topo com o rastreamento em tempo real (*last-mile*) e botão para ver detalhes.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Sessão Expirada:**
  1. A sessão do usuário expirou por inatividade.
  2. O sistema redireciona automaticamente para `/account/login` com mensagem: *"Sua sessão expirou. Por favor, faça login novamente."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RNF003:** Isolamento de dados entre clientes; nenhum usuário pode visualizar dados de outra conta.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Clique no menu de navegação do painel.

### Saídas:
- Dashboard do cliente com cards de resumo, timeline do último pedido e menu de gerenciamento.
