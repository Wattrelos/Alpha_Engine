# UC_CORE_010 - Monitoramento de Ruptura

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CORE_010` |
| **Nome** | Monitoramento de Ruptura de Estoque & Curva ABC |
| **Módulo** | Núcleo Central (Core) - Inteligência de Negócio & Suprimentos |
| **Atores Primários** | Administrador Geral (*Admin*), Gerente de Compras |
| **Atores Secundários** | Sistema Alpha Engine (Motor Cron / Analytics), Servidor SMTP / Mensageria |
| **Tipo** | Condução / Relatórios & Alerta Proativo |
| **Frequência de Uso** | Contínua (Daemon de Auditoria) / Diária (Gestão) |
| **Rastreabilidade** | **RF:** [RF006](/docs/requirements/functional/functional_requirements.yaml) (Gestão automática de inventário), [RF024](/docs/requirements/functional/functional_requirements.yaml) (Alerta de Stockout), [RF025](/docs/requirements/functional/functional_requirements.yaml) (Relatórios e Analytics)<br>**RN:** [RN005](/docs/requirements/business_rules/business_rules.yaml) (Monitoramento rigoroso de itens de alta demanda), [RN006](/docs/requirements/business_rules/business_rules.yaml) (Alerta proativo de baixo estoque), [RN015](/docs/requirements/business_rules/business_rules.yaml) (Curva de giro por categoria)<br>**RNF:** [RNF002](/docs/requirements/non_functional/non_functional_requirements.yaml) (Processamento assíncrono em background), [RNF006](/docs/requirements/non_functional/non_functional_requirements.yaml) (Integração com ERP) |

---

## 1. 🎯 Descrição Sumária
Permite aos administradores e à equipe de suprimentos monitorar preventivamente os níveis de estoque físico de toda a loja, identificando SKUs em risco iminente de ruptura (*stockout warning*) e analisando a curva ABC de faturamento e giro de mercadorias. O sistema executa rotinas periódicas em segundo plano que comparam o saldo livre em tempo real contra o Ponto de Pedido Mínimo (`min_stock_alert`), emitindo alertas prioritários no painel e despachando sugestões automatizadas de ordens de compra aos fornecedores cadastrados.

---

## 2. ⚡ Pré-Condições
1. Usuário com permissão de administrador ou gerente de compras autenticado no painel.
2. Limiares de estoque mínimo parametrizados nos cadastros dos produtos (`UC_CORE_002`).

---

## 3. ✅ Pós-Condições
- Painel analítico atualizado com os índices de ruptura e previsão de esgotamento (*dias de cobertura*).
- Notificações de alerta enviadas por e-mail e webhook para os compradores responsáveis.
- Relatório de sugestão de compras gerado e exportável em planilha (XLSX/CSV).

---

## 4. 🚀 Gatilho (Trigger)
Ocorre periodicamente via agendador de tarefas do sistema (*Cron Job*) a cada 1 hora ou manualmente quando o gestor clica em "Relatórios > Monitor de Ruptura & Estoque Crítico".

---

## 5. 🔄 Fluxo Principal (Auditoria de Ruptura e Sugestão de Reposição)

1. **Sistema (Daemon):** Dispara a rotina periódica de auditoria de inventário em segundo plano.
2. **Sistema:** Executa a consulta agregada comparando o saldo atual disponível contra o limiar mínimo definido no cadastro do produto:
   ```sql
   SELECT product_id, sku, name, quantity, min_stock_alert, lead_time_days 
   FROM tbkk_product 
   WHERE quantity <= min_stock_alert AND status = 1;
   ```
3. **Sistema:** Detecta que o SKU `CIM-CPII-50KG` (Cimento CP-II 50kg) possui 14 sacos disponíveis, enquanto seu ponto de pedido mínimo de segurança é de 100 sacos (RN006).
4. **Sistema:** Calcula o consumo médio diário (ex: 35 sacos/dia nos últimos 15 dias) e projeta que o estoque físico zerará em menos de 10 horas operacionais.
5. **Sistema:** Gera um alerta visual de alta prioridade (badge vermelho pulsante) na barra superior do Painel Administrativo e despacha notificação de urgência para o e-mail do setor de suprimentos.
6. **Ator (Gestor):** Acessa o Painel de Monitoramento de Ruptura.
7. **Sistema:** Apresenta a matriz da Curva ABC categorizada:
   - **Classe A (Alto Impacto / Giro Intenso):** Cimentos, ferros, fios e argamassas;
   - **Classe B (Médio Impacto):** Tintas, tubos e conexões;
   - **Classe C (Giro Lento / Alto Valor Unitário):** Louças especiais e porcelanatos importados.
8. **Ator:** Visualiza a linha do cimento e clica em "Gerar Ordem de Compra Sugerida".
9. **Sistema:** Calcula automaticamente o Lote Econômico de Compra (LEC) considerando o tempo de entrega do fabricante (*lead time* de 2 dias) e gera o rascunho de pedido de compra para 500 sacos.
10. **Ator:** Valida as condições comerciais e exporta o espelho do pedido em PDF/Excel para despacho à fábrica de cimento.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Disparo de Webhook para ERP Externo:**
  1. O sistema está integrado ao ERP corporativo da loja física via API (RNF006).
  2. Ao atingir o ponto de ruptura, o Alpha Engine dispara um webhook assíncrono `POST /api/v1/stock/threshold-reached`.
  3. O ERP externo recebe o payload e emite a cotação de compra automaticamente no sistema central da empresa.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Ruptura Crítica no Balcão com Pré-Vendas Pendentes:**
  1. O sistema identifica que a quantidade física livre é menor que o volume retido em tickets de pré-venda ainda não pagos no caixa (`free_stock < 0`).
  2. O sistema exibe um alerta de emergência na tela dos caixas e do balcão: *"Alerta de Ruptura em Caixa: Existem mais tickets abertos do que mercadorias físicas no depósito para o item Cimento CP-II. Suspenda novas pré-vendas deste SKU imediatamente."* (RN005).

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 (Controle Rigoroso de Estoque e Monitoramento Contínuo):** Vigilância constante sobre insumos pesados de alta rotatividade.
- **RN006 (Alerta de Baixo Estoque):** Gatilho automático parametrizável por produto e categoria.
- **RN015 (Desempenho e Curva ABC):** Cálculo da velocidade de escoamento para sugerir reposição antes do esgotamento total.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Filtros do relatório: Categoria, Fabricante, Nível de Criticidade (`Crítico`, `Atenção`, `Normal`), Curva (`A`, `B`, `C`).

### Saídas:
- Painel analítico de giro e dias de cobertura, exportação em lote de planilhas de compra (CSV/XLSX), alertas sonoros e visuais na interface do administrador.
