# 🛡️ POP-042: Procedimento para Devoluções e Garantias (Defeitos)

| Parâmetro | Detalhes |
| :--- | :--- |
| **Código do Doc** | KB-POL-002 |
| **Última Revisão** | 12/07/2026 |
| **Público-Alvo** | Agentes de IA (SAC) e Operadores de Triagem |

---

## 1. Fundamentos Legais (Legislação Brasileira - CDC)

O tratamento de produtos com vício (defeito) funcional de fabricação deve estar em estrita consonância com o Código de Defesa do Consumidor (CDC). Para evitar contingências jurídicas (Procon, Juizados Especiais), a operação e a IA devem compreender a diferença entre garantia legal, prazos de reparo e políticas de cortesia.

### 1.1 Garantia Legal (Art. 26 do CDC)
O direito de reclamar pelos vícios aparentes ou de fácil constatação expira em:
* **30 dias:** Para produtos não duráveis (ex: colas, tintas, silicones, fitas adesivas).
* **90 dias:** Para produtos duráveis (ex: ferramentas, pisos, torneiras, lâmpadas, fiação, disjuntores).
* *Nota:* Para **vícios ocultos** (defeitos que só se manifestam após algum tempo de uso), o prazo de garantia legal começa a contar a partir do momento em que o defeito é constatado pelo cliente.

### 1.2 O Prazo de 30 dias para Sanar o Vício (Art. 18, § 1º do CDC)
Por lei, o fornecedor (loja física/e-commerce e o fabricante, de forma solidária) **não é obrigado a efetuar a troca imediata** ou o estorno de um produto defeituoso assim que o cliente reclama. O CDC concede ao fornecedor um prazo de **até 30 dias** para consertar/sanar o vício do produto.
* Apenas se o produto não for reparado dentro do prazo de 30 dias é que o consumidor ganha o direito de escolher entre:
  1. A substituição do produto por outro da mesma espécie em perfeitas condições.
  2. A restituição imediata da quantia paga, atualizada monetariamente.
  3. O abatimento proporcional do preço.

### 1.3 Exceção: Bens Essenciais (Art. 18, § 3º do CDC)
Se o produto com defeito for considerado um **bem essencial** (cujo defeito inviabilize o funcionamento básico do lar, como uma bomba d'água principal ou um disjuntor geral da residência), o prazo de 30 dias para reparo não se aplica, e a troca ou estorno deve ser **imediata**.

---

## 2. Fluxo de Prazos Operacionais da AG Sonhos

Para equilibrar a satisfação do cliente com a eficiência de custos da empresa, dividimos as regras de atendimento em três fases:

### Phase A: Até 30 dias corridos (Política de Troca Imediata - Cortesia Comercial)
* **Regra:** Como diferencial de mercado e **política de cortesia interna**, a AG Sonhos realiza a **troca imediata ou estorno direto** de produtos duráveis ou não duráveis que apresentem defeito de fabricação funcional em até 30 dias pós-recebimento. 
* *Nota Jurídica:* Esta troca imediata é uma cortesia comercial da AG Sonhos e não uma obrigação direta da lei (exceto para bens essenciais).
* **Ação do Sistema/IA:** Gerar o código de logística reversa sem custos e aprovar a troca/estorno assim que o produto for recebido e validado na triagem.

### Phase B: De 31 a 90 dias corridos (Garantia Legal - Direcionamento à Assistência)
* **Regra:** Passado o prazo de cortesia de 30 dias, entra em vigor o rito do Art. 18 do CDC. A AG Sonhos, embora solidária, orienta o cliente a acionar a **Rede de Assistência Técnica Autorizada do Fabricante** para que o produto seja reparado dentro do prazo legal de 30 dias.
* **Ação do Sistema/IA:** Direcionar o cliente para o fabricante oficial, fornecendo os contatos (telefone, site e endereços das assistências técnicas físicas mais próximas de São Paulo/região do cliente).
* *Exceção Operacional (Solidariedade):* Se o fabricante não possuir assistência técnica na região do cliente, se recusar a atender, ou se o prazo de 30 dias para reparo expirar sem solução, o cliente deve retornar à AG Sonhos e nós assumiremos a troca ou reembolso de forma solidária.

### Phase C: Após 90 dias corridos (Garantia Contratual do Fabricante)
* **Regra:** Expirada a garantia legal (90 dias para duráveis), o atendimento é realizado de forma exclusiva pelo fabricante através da garantia contratual (se houver), nos termos do manual do produto.
* **Ação do Sistema/IA:** Informar educadamente que o prazo legal de responsabilidade da loja se encerrou e orientar o acionamento da garantia contratual do fabricante.

---

## 3. Critérios de Exclusão de Garantia (Triagem Física e Virtual)

A troca ou o direcionamento para a garantia será **imediatamente cancelado** se a triagem detectar indícios de mau uso, imperícia na instalação ou falta de cuidados recomendados.

### ❌ Recusa Obrigatória por Mau Uso:
* **Danos Físicos por Impacto ou Queda:** Pisos/azulejos quebrados após a entrega, ferramentas com carcaça rachada por queda, lâmpadas ou louças trincadas por manuseio inadequado.
* **Imperícia na Instalação ou Armazenamento:** Cimento empedrado por umidade no local de armazenamento do cliente, argamassa misturada incorretamente, fiação queimada por ligação em voltagem incorreta.
* **Modificação Não Autorizada:** Rompimento de lacres de segurança de ferramentas elétricas ou produtos abertos por assistências não credenciadas.
* **Desgaste Natural:** Desgaste esperado de consumíveis (brocas cegas, lixas gastas, pincéis endurecidos por falta de limpeza pós-uso).

---

## 4. Scripts de Resposta para a IA (SAC Automatizado)

### Cenário 1: Defeito informado em produto durável após 45 dias da entrega (Direcionamento)
> *"Olá, [Nome]. Verifiquei que o seu produto [Nome do Produto] foi entregue há 45 dias. Conforme o Código de Defesa do Consumidor, após o prazo inicial de 30 dias da compra, o atendimento de garantia deve ser iniciado através da Rede de Assistência Técnica Autorizada do fabricante, que tem o direito legal de reparar o item. O fabricante [Nome da Marca] atende no telefone [Telefone] e no site [Link]. Vou gerar a segunda via da sua nota fiscal para que você possa apresentar à assistência. Caso encontre qualquer dificuldade no atendimento do fabricante ou caso eles não possuam assistência na sua região, por favor nos avise para que possamos te ajudar de forma solidária!"*

### Cenário 2: Cliente solicita troca imediata de defeito com 15 dias (Cortesia Operada)
> *"Olá, [Nome]. Sinto muito pelo inconveniente com o seu produto. Como você está dentro do nosso período de cortesia de 30 dias para trocas rápidas, faremos a substituição direta do item sem custos para você. Já gerei o seu código de logística reversa: [Código]. Basta levar o produto bem embalado a uma agência dos Correios. Assim que o item chegar ao nosso centro de distribuição e passar pela triagem rápida, liberaremos o envio do novo produto ou o seu estorno, conforme sua preferência!"*
