# UC_CLI_007 - Calcular Frete por CEP

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_007` |
| **Nome** | Calcular Frete por CEP |
| **Módulo** | Loja Virtual - Carrinho & Compras |
| **Atores Primários** | Visitante (*Guest*), Cliente Logado (*Customer*) |
| **Atores Secundários** | WebServices dos Correios / Transportadoras de Carga Pesada |
| **Tipo** | Extensão de `UC_CLI_006` (`<<extend>>`) |
| **Frequência de Uso** | Alta |
| **Rastreabilidade** | **RF:** [RF010](/docs/requirements/functional/functional_requirements.yaml) (Cálculo frete dinâmico), [RF021](/docs/requirements/functional/functional_requirements.yaml) (BOPIS/Retirada)<br>**RN:** [RN002](/docs/requirements/business_rules/business_rules.yaml) (Cubagem e peso), [RN007](/docs/requirements/business_rules/business_rules.yaml) (Múltiplas opções de frete), [RN008](/docs/requirements/business_rules/business_rules.yaml) (Frete grátis e retirada em loja) |

---

## 1. 🎯 Descrição Sumária
Estende o fluxo do carrinho de compras permitindo ao cliente informar o CEP de entrega de sua residência ou obra. O motor de cotação (*ShippingStrategyManager*) computa o peso bruto total e a cubagem agregada das mercadorias, cotando as modalidades de envio compatíveis (Correios para pacotes leves, Transportadora fracionada/caminhão munck para materiais pesados e Retirada presencial na loja física - BOPIS).

---

## 2. ⚡ Pré-Condições
- Ao menos um produto no carrinho de compras com peso e dimensões cadastrados (RN002).

---

## 3. ✅ Pós-Condições
- Lista de opções de frete cotadas exibidas com valor em reais, prazo estimado de entrega em dias úteis e identificação da transportadora.

---

## 4. 🚀 Gatilho (Trigger)
O usuário digita o CEP de 8 dígitos no carrinho ou na PDP e clica em "Calcular".

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Digita o CEP de destino (ex: `01310-100`) no campo de simulação de frete do carrinho e clica em "Calcular".
2. **Sistema:** Valida o formato numérico do CEP.
3. **Sistema:** Aciona o `ShippingStrategyManager`, que calcula o peso total e o volume cúbico dos itens:
   - Para itens leves/pequenos (ferramentas, torneiras): seleciona estratégias PAC e SEDEX (RN007);
   - Para itens pesados/volumosos (tijolos, cimento, telhas, pisos): seleciona estratégias de Transportadora dedicada de carga pesada com caminhão munck (RN007).
4. **Sistema:** Avalia se o valor do pedido atinge o teto de **Frete Grátis** condicionado para a região do cliente (RN008).
5. **Sistema:** Inclui sempre a opção gratuita "Retirar na Loja Física (BOPIS)" com prazo de separação de 2 horas (RN008).
6. **Sistema:** Renderiza as opções em tela para seleção do usuário.
7. **Ator:** Seleciona a opção desejada; o sistema adiciona a taxa de entrega ao valor total do pedido.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Auto-preenchimento para Cliente Logado:**
  1. O cliente está autenticado na loja.
  2. O sistema resgata o CEP de seu endereço padrão (`UC_CLI_019`) e exibe as cotações de frete automaticamente sem necessidade de digitação.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - CEP Inválido ou Fora de Cobertura:**
  1. O usuário informa um CEP inexistente na base dos Correios.
  2. O sistema exibe alerta *"CEP inválido. Por favor, confira os números digitados."*
- **FE02 - Timeout no WebService dos Correios:**
  1. O serviço externo de cotação dos Correios não responde em até 3 segundos.
  2. O sistema aciona a tabela de contingência *offline* baseada em faixas de CEP locais, exibindo os prazos e valores estimados sem travar o cliente.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN002 & RN007:** Seleção de modalidade de transporte orientada estritamente pelo peso bruto e cubagem da carga.
- **RN008:** Validação de regras de frete grátis regional e oferta de retirada BOPIS.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `postcode` (string de 8 dígitos).

### Saídas:
- Tabela com: Modalidade (PAC, SEDEX, Transportadora Expressa, Retirada na Loja), Prazo (dias úteis) e Custo (R$).
