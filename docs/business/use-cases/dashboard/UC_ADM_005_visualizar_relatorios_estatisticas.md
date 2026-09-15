# UC_ADM_005 - Visualizar Relatórios & Estatísticas

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_ADM_005` |
| **Nome** | Visualizar Relatórios & Estatísticas (Analytics) |
| **Módulo** | Painel Administrativo - Operações de Negócio |
| **Atores Primários** | Operador do Painel (*Operator*), Administrador Geral (*Admin*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Relatórios & Business Intelligence |
| **Frequência de Uso** | Alta |
| **Rastreabilidade** | **RF:** [RF024](/docs/requirements/functional/functional_requirements.yaml) (Alerta de ruptura de estoque), [RF025](/docs/requirements/functional/functional_requirements.yaml) (Analytics e relatórios gerenciais)<br>**RN:** [RN006](/docs/requirements/business_rules/business_rules.yaml) (Alerta proativo de estoque mínimo), [RN015](/docs/requirements/business_rules/business_rules.yaml) (Desempenho de vendas por categoria)<br>**RNF:** [RNF002](/docs/requirements/non_functional/non_functional_requirements.yaml) (Geração rápida com caching e exportação CSV/PDF) |

---

## 1. 🎯 Descrição Sumária
Permite aos gestores e administradores extrair relatórios analíticos e indicadores de desempenho (KPIs) em tempo real, abrangendo faturamento consolidado por canal (E-commerce vs. PDV Balcão/Caixa), curva ABC de produtos de maior giro, monitoramento de ruptura de estoque (*Stockout Warning*), taxa de conversão e relatórios fiscais para a contabilidade.

---

## 2. ⚡ Pré-Condições
- Usuário autenticado com permissão no módulo `report/*`.

---

## 3. ✅ Pós-Condições
- Gráficos e tabelas estatísticas geradas em tela com suporte a exportação em arquivos CSV, Excel ou PDF.

---

## 4. 🚀 Gatilho (Trigger)
O operador acessa a seção "Relatórios" ou visualiza o Dashboard principal do painel.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa "Relatórios > Relatório de Vendas".
2. **Ator:** Define os parâmetros de filtragem: período (ex: *Últimos 30 dias*), canal de venda (*Todos*, *E-commerce* ou *PDV Loja Física*) e grupo de clientes.
3. **Ator:** Clica em "Filtrar / Gerar Relatório".
4. **Sistema:** Processa a agregação dos dados de vendas, faturamento líquido, descontos concedidos e impostos apurados.
5. **Sistema:** Renderiza os gráficos de linha/barra e a tabela analítica detalhada com totais consolidados.
6. **Ator:** Clica no botão "Exportar para Excel (XLSX)".
7. **Sistema:** Gera o arquivo estruturado e inicia o download no navegador.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Relatório de Ruptura de Estoque (Curva de Reposição):**
  1. O gestor acessa "Relatórios > Produtos com Baixo Estoque".
  2. O sistema lista todos os SKUs cujo saldo atual atingiu o limite mínimo de segurança (`quantity <= min_stock_threshold` - RN006) com sugestão de lote de compra.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Intervalo de Datas Excessivamente Longo:**
  1. O usuário solicita relatório de um período superior a 3 anos sem filtros.
  2. O sistema avisa que o processamento será assíncrono e agenda a exportação em background, enviando link de download por e-mail.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN006 (Alerta de Baixo Estoque):** Exibição em destaque de todos os produtos que necessitam de reposição imediata.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `date_start`, `date_end`, `order_status_id`, `group_by` (dia/mês/ano).

### Saídas:
- Painel visual de gráficos, KPIs de ticket médio e relatórios exportáveis.
