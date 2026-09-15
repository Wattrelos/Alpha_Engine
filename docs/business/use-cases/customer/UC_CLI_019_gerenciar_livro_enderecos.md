# UC_CLI_019 - Gerenciar Livro de Endereços (Múltiplos Shiptos)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_019` |
| **Nome** | Gerenciar Livro de Endereços |
| **Módulo** | Loja Virtual - Área "Minha Conta" |
| **Atores Primários** | Cliente Logado (*Customer*) |
| **Atores Secundários** | API ViaCEP, Sistema Alpha Engine |
| **Tipo** | Condução / CRUD de Endereços |
| **Frequência de Uso** | Média |
| **Rastreabilidade** | **RF:** [RF017](/docs/requirements/functional/functional_requirements.yaml) (Múltiplos Shiptos / Endereços de entrega)<br>**RN:** [RN002](/docs/requirements/business_rules/business_rules.yaml) (Cálculo de frete por CEP de destino)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Ergonomia e facilidade) |

---

## 1. 🎯 Descrição Sumária
Permite ao cliente (em especial construtoras, arquitetos, empreiteiros e clientes em reforma) cadastrar, visualizar, editar, definir como padrão e excluir múltiplos locais de entrega (*Shiptos* / Endereços de Obra) em sua conta (`/account/addresses`), facilitando o roteamento logístico e cotação de frete no checkout.

---

## 2. ⚡ Pré-Condições
- Cliente autenticado na sessão.

---

## 3. ✅ Pós-Condições
- Endereços persistidos na tabela `tbkk_address` vinculados ao `customer_id`.
- Endereço marcado como "Padrão" atualizado na tabela `tbkk_customer`.

---

## 4. 🚀 Gatilho (Trigger)
O cliente clica em "Livro de Endereços" ou "Endereços de Entrega" no painel da conta ou durante a seleção de frete no checkout.

---

## 5. 🔄 Fluxo Principal (Cadastrar Novo Endereço de Obra)

1. **Ator:** Acessa `/account/addresses` e clica no botão "Adicionar Novo Endereço".
2. **Sistema:** Renderiza o formulário de cadastro de endereço.
3. **Ator:** Informa um apelido para o local (ex: *"Obra Condomínio Alphaville - Lote 14"*), nome do recebedor e digita o CEP.
4. **Sistema:** Consulta a API do ViaCEP e autopreenche Logradouro, Bairro, Cidade e Estado.
5. **Ator:** Preenche o número, complemento e ponto de referência (essencial para caminhões de carga pesada).
6. **Ator:** Marca se este deve ser o "Endereço de Entrega Padrão" e clica em "Salvar".
7. **Sistema:** Valida os dados, insere na tabela `tbkk_address` e redireciona para a listagem com mensagem de sucesso.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Editar Endereço Existente:**
  1. O ator clica em "Editar" no card de um endereço cadastrado.
  2. O sistema carrega os dados no formulário; o ator modifica e salva.
- **FA02 - Excluir Endereço Secundário:**
  1. O ator clica em "Excluir" em um endereço de obra concluída.
  2. O sistema confirma a exclusão no modal e remove o registro da base.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Tentativa de Excluir o Único Endereço ou o Endereço Padrão:**
  1. O cliente tenta deletar o endereço definido como padrão enquanto não houver outro cadastrado.
  2. O sistema bloqueia a exclusão e alerta: *"Você não pode excluir seu endereço padrão. Defina outro endereço como padrão antes de excluir este."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN002 (Cálculo de Frete):** Cada endereço possui CEP próprio que determina a rota logística e valor de frete para caminhões pesados.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `alias` (nome do local/obra), `firstname`, `lastname`, `company`, `address_1`, `address_2`, `postcode`, `city`, `zone_id`, `default`.

### Saídas:
- Grade com cards de todos os endereços de entrega salvos com badges "Padrão" e botões de ação.
