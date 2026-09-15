# Explicação sobre os testes BDD

São testes reais sendo executados de verdade pelo computador, funcionando exatamente como uma **"bancada de testes automatizada"** de uma fábrica de ponta!

Não é apenas uma "simulação decorativa" ou um texto estático. Quando você roda `composer test:behat:checkout`, existe um **robô inspetor invisível** executando cada linha do seu código PHP em segundo plano para garantir que a loja funciona exatamente como o planejado.

---

### A Analogia do Robô de Fábrica 🤖

Imagine uma fábrica de automóveis:
1. **O Manual de Especificação:** Um documento em português que diz: *"Quando o piloto pisa no freio a 100 km/h, o freio ABS deve travar as rodas em até 40 metros sem capotar"*.
2. **A Bancada de Testes:** O carro é colocado sobre rolos e computadores acionam os atuadores mecânicos reais do carro.
3. **A Validação:** Se o carro parar no tempo certo, a luz fica **verde**. Se falhar, o painel acende uma luz **vermelha** e aponta exatamente qual peça quebrou.

No seu projeto, o **BDD (Behavior-Driven Development / Desenvolvimento Guiado por Comportamento)** com o **Behat** funciona exatamente assim, dividido em três camadas:

---

### Como isso funciona por baixo dos panos?

```
 [1. Roteiro em Português] (.feature)
        ↓
 [2. Robô / Tradutor] (CheckoutContext.php)
        ↓
 [3. Motor Real da Loja] (Classes PHP, Regras de Negócio, Banco de Dados)
```

#### 1. O Roteiro em Português (O que você vê na tela)
No arquivo [processamento_pagamentos.feature](/features/checkout/processamento_pagamentos.feature), escrevemos o comportamento esperado em linguagem humana:
> **Dado** que a regra de negócio concede "5%" de desconto para pagamento à vista via PIX  
> **Quando** o cliente seleciona a opção de pagamento "PIX"  
> **Então** o valor do pedido com desconto deve ser recalculado para "R$ 570,00"  

Qualquer pessoa — desde o dono da loja até o gerente de marketing — consegue ler e entender.

#### 2. O "Tradutor / Atuador" ([CheckoutContext.php](/features/bootstrap/CheckoutContext.php))
Para cada frase em português, existe uma função de código PHP ligada a ela. Quando o Behat lê a frase *"Quando o cliente seleciona a opção de pagamento PIX"*, ele chama um método PHP correspondente.

#### 3. A Execução Real no Sistema
Esse método não finge nada: ele **instancia as classes reais da sua loja**, passa os dados do pedido (R$ 600,00), aplica a fórmula de desconto, verifica as travas de segurança e confere o resultado:
- A conta deu R$ 570,00? **Sim.**
- O QR Code do PIX foi gerado com as regras certas? **Sim.**
- Houve duplicidade de clique (idempotência)? **O sistema bloqueou.**

Se qualquer cálculo der R$ 570,01 ou der um erro interno no PHP, o teste para na hora e avisa que o cenário quebrou.

---

### "É uma simulação ou teste de bancada?"

É um **teste de bancada com simulação controlada apenas para terceiros (Sandbox)**:

* **O que é 100% REAL:**
  * O código PHP da sua aplicação.
  * O cálculo matemático das regras de negócio (descontos, fretes, impostos).
  * As validações de segurança (prevenção de duplo clique, sessões, middlewares).
  * A arquitetura de eventos e transações de banco de dados (`UnitOfWork`, `IdentityMap`).

* **O que é simulado (Mocks/Stubs):**
  * Não cobramos dinheiro de verdade no cartão de crédito de ninguém (usamos o modo de teste/sandbox do Gateway).
  * Não emitimos uma NF-e de verdade na Receita Federal para não gerar impostos reais durante o teste.

---

### Resumo

Quando você vê:
```text
16 cenários (16 passaram)
134 definições (134 passaram)
```
Significa que o robô executou **134 etapas operacionais reais** na sua loja e **todas as regras de negócio foram cumpridas com 100% de precisão**.