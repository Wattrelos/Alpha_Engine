# UC_POS_004 - Identificar / Cadastrar Cliente (POS)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_POS_004` |
| **Nome** | Identificar / Cadastrar Cliente no Balcão |
| **Módulo** | Ponto de Venda (POS) - Módulo Vendedor |
| **Atores Primários** | Vendedor de Balcão (*Sales Representative*), Cliente Presencial (*Customer*) |
| **Atores Secundários** | Sistema Alpha Engine POS |
| **Tipo** | Condução / Cadastral de Loja |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF014](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Cadastro e identificação), [RF020](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Dados para emissão de NFC-e/NF-e)<br>**RN:** [RN017](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Preço de atacado para PJ/Construtoras)<br>**RNF:** [RNF001](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Agilidade no atendimento presencial) |

---

## 1. 🎯 Descrição Sumária
Permite ao vendedor de balcão identificar rapidamente o cliente presencial através do CPF, CNPJ, telefone ou nome, resgatando seu histórico de compras e tabela de preços diferenciada (como construtoras e profissionais da obra), ou realizar um cadastro ágil com poucos campos para vinculação à pré-venda e emissão fiscal.

---

## 2. ⚡ Pré-Condições
- Vendedor operando o terminal POS Balcão.

---

## 3. ✅ Pós-Condições
- Cliente vinculado à sessão da pré-venda com tabela de preços correspondente aplicada.

---

## 4. 🚀 Gatilho (Trigger)
O vendedor pergunta o CPF/CNPJ do cliente e digita no campo `[F2] Identificar Cliente`.

---

## 5. 🔄 Fluxo Principal (Identificação de Cliente Cadastrado)

1. **Ator:** Pressiona `[F2]` no terminal e digita o CPF ou CNPJ informado pelo cliente presencial.
2. **Sistema:** Consulta a base de clientes (`tbkk_customer`) em milissegundos.
3. **Sistema:** Localiza o registro e exibe no cabeçalho da pré-venda:
   - Nome / Razão Social do Cliente;
   - Tipo de Cliente: *Consumidor Final* ou *Construtora / Atacado*;
   - Saldo de créditos em loja (se houver);
   - Endereço principal para eventual entrega de caminhão.
4. **Sistema:** Se for cliente PJ/Construtora, ativa automaticamente a tabela de preços de atacado na comanda (RN017).
5. **Ator:** Prossegue com o atendimento e adição dos itens na pré-venda (`UC_POS_005`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Cadastro Rápido de Novo Cliente no Balcão:**
  1. O CPF digitado não é encontrado na base.
  2. O sistema abre uma janela modal simplificada de cadastro rápido (`[F3] Novo Cadastro`).
  3. O vendedor preenche Nome Completo, CPF/CNPJ, Telefone celular e CEP.
  4. O sistema grava o cliente imediatamente e já o vincula à pré-venda.
- **FA02 - Venda a Consumidor Não Identificado (Consumidor Balcão):**
  1. O cliente prefere não informar o CPF para identificação no balcão.
  2. O sistema vincula a comanda ao cliente genérico *"Consumidor Não Identificado"*.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - CPF ou CNPJ com Dígitos Verificadores Inválidos:**
  1. O vendedor digita um número com erro.
  2. O sistema emite alerta sonoro e impede a confirmação até a correção do documento.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN017 (Preço Varejo vs Atacado):** Aplicação compulsória dos descontos de atacado assim que o CNPJ corporativo é identificado.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `cpf_cnpj`, `telephone` ou `customer_name`.

### Saídas:
- Perfil do cliente carregado no terminal com badge de grupo de preço e histórico recente.
