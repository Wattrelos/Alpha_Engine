# UC_CLI_015 - Sincronizar Sessão Redis & Carrinho

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_015` |
| **Nome** | Sincronizar Sessão Redis & Carrinho |
| **Módulo** | Loja Virtual - Autenticação & Sessão |
| **Atores Primários** | Sistema Alpha Engine |
| **Atores Secundários** | Servidor Redis, Banco de Dados MySQL |
| **Tipo** | Inclusão de `UC_CLI_013` (`<<include>>`) / Processamento Sistêmico |
| **Frequência de Uso** | Muito Alta |
| **Rastreabilidade** | **RF:** [RF009](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Carrinho persistente), [RF014](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Auth e sessão)<br>**RN:** [RN005](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Validação de estoque na fusão)<br>**RNF:** [RNF002](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Baixa latência Redis), [RNF004](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Disponibilidade com fallback) |

---

## 1. 🎯 Descrição Sumária
Caso de uso sistêmico disparado imediatamente após o login bem-sucedido do cliente para unificar os itens do carrinho que foram adicionados anonimamente durante a navegação como visitante (*Guest Cart*) com os itens previamente salvos na conta persistente do cliente no banco de dados (*Customer Cart*), revalidando preços, opções e estoques.

---

## 2. ⚡ Pré-Condições
- Login validado pelo sistema no caso de uso `UC_CLI_013`.
- Existência de sessão ativa em Redis.

---

## 3. ✅ Pós-Condições
- Carrinho único consolidado gravado no Redis e persistido na tabela `tbkk_cart` para o `customer_id`.
- Saldo de produtos e preços sincronizados com a tabela comercial vigente do cliente.

---

## 4. 🚀 Gatilho (Trigger)
Ocorre automaticamente no momento do login ou registro de um novo cliente.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Sistema:** Recupera a lista de itens do carrinho anônimo temporário gravado no Redis da sessão anônima (`session_id`).
2. **Sistema:** Consulta a tabela `tbkk_cart` para resgatar os produtos que o cliente já havia deixado salvos em acessos anteriores.
3. **Sistema:** Executa o algoritmo de fusão (*Merge Cart Strategy*):
   - Para itens idênticos (mesmo `product_id` e mesmas opções): soma as quantidades, respeitando o teto de estoque disponível (RN005);
   - Para itens novos: adiciona ao carrinho persistente do cliente.
4. **Sistema:** Recalcula os preços dos produtos conforme o grupo de preço do cliente (se for cliente PJ/Construtora, aplica o preço de atacado - RN017).
5. **Sistema:** Persiste o estado consolidado na tabela `tbkk_cart` e na chave Redis da nova sessão do usuário.
6. **Sistema:** Atualiza o contador de itens no cabeçalho (*cart badge*).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Carrinho Anônimo Vazio:**
  1. O visitante não havia adicionado nenhum produto antes de fazer login.
  2. O sistema apenas restaura os itens pré-existentes da conta do cliente no banco de dados para a sessão ativa.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Item da Sessão Anterior Sem Saldo em Estoque:**
  1. Um produto salvo anteriormente pelo cliente na semana passada agora está com estoque zerado.
  2. O sistema remove o item indisponível do carrinho e exibe um aviso informativo no topo da página: *"O item [Nome] foi removido do seu carrinho por estar esgotado."*
- **FE02 - Indisponibilidade Temporária do Redis:**
  1. O serviço Redis falha ou não responde.
  2. O sistema aciona o *fallback* de sessão nativa em PHP Session / MySQL (`autenticacao_redis_fallback`), garantindo a continuidade da navegação sem perda do carrinho.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN005 (Controle de Estoque):** Verificação de saldo disponível para cada item incorporado durante a mesclagem.
- **RN017 (Preço Varejo vs Atacado):** Ajuste de tabela tarifária ao migrar de visitante (anônimo) para cliente PJ.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `anonymous_session_id`, `customer_id`.

### Saídas:
- Carrinho consolidado e sincronizado no banco de dados e na sessão Redis.
