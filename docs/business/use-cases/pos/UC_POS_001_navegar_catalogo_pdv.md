# UC_POS_001 - Navegar no Catálogo de Produtos (POS)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_001` |
| **Nome** | Navegar no Catálogo de Produtos (POS Balcão) |
| **Módulo** | Ponto de Venda (POS) - Módulo Vendedor |
| **Atores Primários** | Vendedor de Balcão (*Sales Representative*) |
| **Atores Secundários** | Sistema Alpha Engine POS |
| **Tipo** | Condução / Operacional de Loja |
| **Frequência de Uso** | Contínua / Muito Alta |
| **Rastreabilidade** | **RF:** [RF001](/docs/requirements/functional/functional_requirements.yaml) (Catálogo), [RF002](/docs/requirements/functional/functional_requirements.yaml) (Especificações), [RF006](/docs/requirements/functional/functional_requirements.yaml) (Consulta de saldo de estoque)<br>**RN:** [RN003](/docs/requirements/business_rules/business_rules.yaml) (Informações técnicas), [RN005](/docs/requirements/business_rules/business_rules.yaml) (Controle de estoque em tempo real)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Interface de alta densidade otimizada para teclado e leitor de código de barras) |

---

## 1. 🎯 Descrição Sumária
Permite ao vendedor presencial no balcão da loja de materiais de construção pesquisar rapidamente mercadorias digitando o nome comercial, código interno ou passando o leitor óptico de código de barras (EAN), consultando a ficha técnica resumida, preço de balcão e invocando a verificação de saldo físico em estoque (`<<include>> UC_POS_003`).

---

## 2. ⚡ Pré-Condições
- Vendedor autenticado no terminal de balcão do PDV (`/pos/balcao`).

---

## 3. ✅ Pós-Condições
- Lista de produtos correspondentes exibida em grade ou tabela rápida de alta densidade no terminal.

---

## 4. 🚀 Gatilho (Trigger)
O vendedor digita uma palavra-chave no campo de busca rápida ou passa o leitor de código de barras no produto físico.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** No terminal POS Balcão, digita o termo desejado (ex: *"Argamassa AC-III 20kg"*) ou bipa o código de barras do saco.
2. **Sistema:** Executa a consulta local indexada em milissegundos e invoca `<<include>> UC_POS_003 (Verificar Disponibilidade de Estoque)`.
3. **Sistema:** Exibe a linha do produto na tela contendo:
   - Código SKU / EAN;
   - Nome e Marca do produto;
   - Saldo em estoque físico na loja (ex: *"Prateleira B-12: 142 sacos"*);
   - Preço de tabela e preço à vista no PIX/Dinheiro (RN016);
   - Seletor de variações (`<<extend>> UC_POS_002`).
4. **Ator:** Informa a disponibilidade ao cliente presencial e adiciona o item ao pedido (`UC_POS_006`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Busca por Leitor de Código de Barras (Modo Bipagem Rápida):**
  1. O vendedor bipa o produto diretamente com o leitor.
  2. O sistema reconhece o EAN exato, adiciona 1 unidade diretamente à comanda de pré-venda e emite sinal sonoro de confirmação (*beep*).

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Produto Não Cadastrado ou Inativo:**
  1. O código de barras não existe na base de dados.
  2. O sistema emite alerta sonoro de erro e exibe: *"Produto não encontrado no cadastro."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN003 & RN005:** Exibição clara de atributos técnicos e estoque real do depósito da loja física.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Código de barras (leitor), SKU ou termo de busca textual.

### Saídas:
- Tabela rápida com saldo de estoque, localização física em loja e preço unitário.
