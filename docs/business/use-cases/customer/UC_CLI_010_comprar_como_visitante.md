# UC_CLI_010 - Comprar como Visitante (Guest Checkout)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_CLI_010` |
| **Nome** | Comprar como Visitante |
| **Módulo** | Loja Virtual - Carrinho & Compras |
| **Atores Primários** | Visitante (*Guest*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Extensão de `UC_CLI_009` (`<<extend>>`) |
| **Frequência de Uso** | Alta |
| **Rastreabilidade** | **RF:** [RF014](/docs/requirements/functional/functional_requirements.yaml) (Cadastro/Identificação), [RF018](/docs/requirements/functional/functional_requirements.yaml) (Checkout multi-meios)<br>**RNF:** [RNF001](/docs/requirements/non_functional/non_functional_requirements.yaml) (Fricção mínima de compra), [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Conformidade LGPD) |

---

## 1. 🎯 Descrição Sumária
Estende o caso de uso de checkout (`UC_CLI_009`) permitindo ao consumidor que não deseja criar ou lembrar uma senha de acesso concluir a compra de forma ágil, fornecendo apenas os dados essenciais para emissão do documento fiscal e entrega (Nome, CPF/CNPJ, E-mail, Telefone e Endereço de Entrega).

---

## 2. ⚡ Pré-Condições
- Visitante não autenticado na tela de checkout com produtos no carrinho.

---

## 3. ✅ Pós-Condições
- Pedido emitido com vínculo a uma conta de convidado (*guest account*) persistida no banco com flag especial, permitindo posterior reivindicação de senha.

---

## 4. 🚀 Gatilho (Trigger)
O visitante escolhe a opção "Comprar como Visitante / Sem Senha" na tela de identificação do checkout.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Seleciona a opção "Finalizar sem Senha (Visitante)".
2. **Sistema:** Exibe formulário simplificado em uma única tela:
   - Tipo de Pessoa: Física (CPF) ou Jurídica (CNPJ / IE);
   - Nome Completo ou Razão Social;
   - E-mail e Telefone de contato para notificações WhatsApp/SMS;
   - Endereço completo com CEP e número.
3. **Ator:** Preenche os dados solicitados e clica em "Prosseguir para Pagamento".
4. **Sistema:** Valida o CPF/CNPJ contra algoritmo fiscal e valida o formato de e-mail.
5. **Sistema:** Armazena os dados do cliente na estrutura de sessão do checkout e retorna para o fluxo principal de `UC_CLI_009`.
6. **Sistema:** Ao final da compra, sugere opcionalmente no comprovante: *"Deseja criar uma senha com 1 clique para acompanhar este pedido futuramente?"*.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - E-mail Já Cadastrado na Base:**
  1. O visitante informa um e-mail que já possui conta ativa com senha.
  2. O sistema detecta o registro e exibe uma caixa de login rápida: *"Encontramos sua conta! Digite sua senha ou receba um código de acesso rápido por e-mail para continuar sem complicação."*

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - CPF ou CNPJ Inválido:**
  1. O usuário digita um número incorreto no documento.
  2. O sistema sinaliza o campo em vermelho e impede o avanço informando: *"CPF/CNPJ com dígitos verificadores incorretos."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RNF003 (Proteção de Dados LGPD):** Coleta e armazenamento exclusivo dos dados indispensáveis para o cumprimento de obrigação fiscal e logística.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `firstname`, `lastname`, `email`, `telephone`, `cpf_cnpj`, `address_1`, `city`, `postcode`, `zone_id`.

### Saídas:
- Perfil temporário validado e encaminhado para as etapas de frete e pagamento.
