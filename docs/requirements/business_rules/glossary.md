# 📖 Glossário de Regras de Negócio (E-commerce de Materiais de Construção)

Este documento define e descreve os principais termos e regras de negócio que governam a **Alpha Engine**, focados nas especificidades do domínio de comércio eletrônico de materiais de construção e no funcionamento operacional do sistema.

---

## 1. Módulo de Produtos & Catálogo

### Venda Fracionada (Unidade de Medida Dinâmica)
*   **Definição:** Capacidade de vender materiais em diferentes formatos de embalagem ou cubagem (ex: peças avulsas, metros quadrados `m²`, caixas, quilogramas `kg`, litros `L`).
*   **Lógica de Negócio (RN001):** O sistema deve calcular automaticamente o preço final com base na unidade selecionada e quantidade inserida, aplicando as devidas tabelas de conversão (ex: uma caixa de pisos contém X peças e cobre Y m²).

### Especificações Técnicas por Categoria (RN003)
Diferentes categorias exigem o preenchimento de campos obrigatórios para evitar erros de compra e devoluções desnecessárias:
*   **Ferramentas:** Obrigatoriedade de informar Voltagem (110V, 220V, Bivolt).
*   **Tintas e Acabamentos:** Instruções de diluição, aplicação e rendimento.
*   **Cimento e Argamassas:** Resistência (ex: CP-II, CP-III) e tempo de secagem.

### Kits e Combos de Produtos (RN004)
*   **Definição:** Agrupamento promocional de produtos que frequentemente são comprados juntos (ex: Kit Pintura: tintas, trinchas, lixas, selador e bandeja).
*   **Vantagem Comercial:** Concessão de descontos percentuais ou frete reduzido quando o cliente adquire o kit completo.

---

## 2. Inventário & Logística

### Controle de Estoque Rigoroso (RN005)
*   **Definição:** Sistema que impede a venda de mercadorias sem saldo disponível em estoque (anti-venda às cegas).
*   **Lógica:** O estoque deve ser descontado em tempo real (`REALTIME`) assim que a transação de pagamento do pedido for confirmada e a transação síncrona de escrita for finalizada no banco.

### Alerta de Estoque Mínimo / Stockout (RN006)
*   **Definição:** Disparo de alerta proativo para a equipe de compras quando o estoque de um SKU cai abaixo do limite de segurança.

### Modalidades de Envio por Cubagem (RN007)
*   **Correios:** Restrito a pacotes pequenos e leves (limites de peso e dimensões oficiais dos Correios).
*   **Transportadora Especializada:** Utilizada obrigatoriamente para itens pesados ou volumosos (ex: cimento, telhas, vergalhões de ferro).

---

## 3. Compras, Devoluções & CDC (Código de Defesa do Consumidor)

### Retirada na Loja Física / BOPIS (Buy Online, Pick Up In Store)
*   **Definição:** Permite ao cliente efetuar a compra online e efetuar a retirada presencial física em uma das lojas da rede.
*   **Implicação Legal (RN011):** O cliente que opta por retirar o produto fisicamente na loja **não possui o Direito de Arrependimento de 7 dias** previsto no Artigo 49 do CDC, pois tem a oportunidade de inspecionar a integridade e especificações do produto pessoalmente no momento da retirada.

### Direito de Arrependimento (CDC Art. 49)
*   **Definição:** Direito de desistência de compras feitas fora do estabelecimento comercial (online) em até 7 dias corridos após o recebimento.
*   **Condições de Aceite (RN011):** O produto deve retornar na embalagem original fechada e inviolada. Produtos com embalagem danificada ou indícios de uso serão recusados (exceto em casos de defeito de fabricação comprovado).

---

## 4. Precificação e Condições Financeiras

### Descontos por Volume / Venda Progressiva (RN015)
*   **Definição:** Concessão de desconto no preço unitário do item à medida que a quantidade comprada aumenta. Aplicável principalmente a materiais de fundação (cimento, areia, tijolos).

### Preço Varejo vs. Atacado (RN017)
*   **Varejo:** Preço padrão praticado para CPF (pessoa física).
*   **Atacado:** Preço diferenciado com margem reduzida focado em empresas (PJ/CNPJ), condicionado a volume mínimo de compra ou cadastro aprovado de construtoras.
