# Parecer: [Papel de Domínio: Balconista / Atendente de Vendas]

A forma como está redigido, ele apresenta **vulnerabilidades jurídicas relevantes**, **riscos de passivos trabalhistas** (especialmente ligados a adicionais de insalubridade, quebra de caixa e ergonomia) e um **problema de formatação estrutural** (mistura descrição de função, parecer jurídico interno e minuta contratual no mesmo arquivo).

Abaixo está o parecer detalhado e a proposta de adequação.

---

### 1. Riscos Críticos e Confronto com a Legislação Vigente

#### 🔴 Risco 1: Limpeza do Ambiente vs. Adicional de Insalubridade (NR-15 e Súmula 448 do TST)
* **Texto atual:** Fala em *"limpeza leve, varrição e organização do seu setor de trabalho, das prateleiras e das áreas comuns de circulação de clientes"*.
* **O perigo jurídico:** Em ações trabalhistas, empregados que possuem essa atribuição genérica frequentemente alegam que faziam higienização de banheiros de clientes ou recolhimento de lixo sanitário. A **Súmula 448, II, do TST** assegura **adicional de insalubridade em grau máximo (40%)** para quem higieniza instalações sanitárias de uso público/coletivo ou recolhe lixo sanitário.
* **Adequação necessária:** É indispensável delimitar expressamente que a limpeza se restringe ao asseio seco e organização de superfícies do balcão e varrição leve, sendo **terminantemente vedada** a higienização de vasos sanitários, coleta de lixo de banheiros ou manuseio de produtos químicos cáusticos (sem EPI e sem previsão de insalubridade - NR-06/NR-15).

---

#### 🔴 Risco 2: Operação de Caixa, "Quebra de Caixa" e Descontos (Art. 462 da CLT e CCT)
* **Texto atual:** Prevê que o balconista pode *"receber pagamentos e emitir comprovantes"* e *"abertura e fechamento de caixa"*.
* **O perigo jurídico:**
  1. **Convenção Coletiva de Trabalho (CCT):** Quase todas as convenções sindicais dos comerciários exigem o pagamento de um adicional de **Quebra de Caixa** (geralmente entre 5% e 10% do piso) para qualquer colaborador que manuseie numerário habitualmente ou substitua o caixa. Se a sua loja exigir essa função sem pagar a rubrica prevista na CCT da sua região, gera passivo de cumprimento de convenção coletiva.
  2. **Desconto de diferenças de troco (Art. 462, § 1º da CLT):** A empresa só pode descontar diferenças de caixa do salário do funcionário se houver **cláusula expressa de autorização prévia por escrito no contrato** (em caso de culpa), ou se houver comprovação de dolo.
  3. **Comissionistas:** Se o balconista receber remuneração variável/comissão de vendas, colocá-lo no caixa durante horários de pico pode ensejar pedido de indenização por prejuízo salarial decorrente da perda de oportunidade de venda.
* **Adequação necessária:** Ressalvar se a remuneração é fixa ou variável, condicionar a operação de caixa aos termos da CCT local e assegurar a cláusula de autorização de desconto de eventuais diferenças no contrato individual.

---

#### 🟡 Risco 3: Ergonomia e Trabalho em Pé (NR-17 e Artigo 199 da CLT)
* **Obrigação legal:** O trabalho de balcão e de frente de caixa exige atenção à **NR-17 (Ergonomia)** e ao **Artigo 199 da CLT**:
  * Para atividades realizadas em pé, é obrigatória a disponibilização de **assentos para descanso** nos locais de trabalho para serem utilizados durante as pausas operacionais.
  * Para o operador de caixa/PDV, a NR-17 (Anexo I) prevê requisitos de assento regulável com encosto e apoio para os pés, além de leitor óptico para evitar digitação repetitiva de códigos de barras.

---

#### 🟡 Risco 4: Redação Vexatória e Risco de Assédio Moral ("Vedado o ócio ocioso")
* **Texto atual:** Linha 29: *"sendo vedado o ócio ocioso"*.
* **O perigo jurídico:** Além de ser um pleonasmo vicioso, esse termo tem conotação punitiva e pejorativa. Em litígios trabalhistas, esse tipo de linguagem é frequentemente citado por advogados para alegar cobrança desmedida, pressão psicológica ou assédio moral. O tempo de trabalho do colaborador já é legalmente definido como "tempo à disposição do empregador" (**Art. 4º da CLT**), que deve conviver com os intervalos regulares (**Art. 71 da CLT**).
* **Adequação necessária:** Substituir por redação profissional, corporativa e clara: *"direcionar proativamente sua força de trabalho às atividades complementares de organização e reposição nos momentos de baixa circulação de clientes"*.

---

#### 🟢 Risco 5: Proteção de Dados Pessoais dos Clientes (LGPD - Lei nº 13.709/2018)
* **Cenário Real:** Conforme os fluxos de pré-venda e balcão do sistema ([cenarioBalcao.puml](file:///var/www/html/agsonhos/docs/business/processes/cenarioBalcao.puml)), o balconista identifica o cliente no sistema (nome, CPF para emissão de nota, telefone).
* **Adequação necessária:** Incluir uma cláusula expressa de observância à LGPD e dever de confidencialidade dos dados dos clientes tratados no terminal.

---

#### 🟢 Risco 6: Classificação Brasileira de Ocupações (CBO) para o eSocial
* **Requisito:** É altamente recomendável que o descritivo mencione o código **CBO** oficial compatível, evitando autuações fiscais do eSocial por divergência entre o cargo registrado e as atividades executadas:
  * **CBO 5211-30:** Atendente de balcão
  * **CBO 5211-10:** Vendedor de comércio varejista
  * **CBO 5211-25:** Repositor de mercadorias (quando a atividade é subsidiária)

---

### 2. Problema de Estrutura do Documento

Atualmente o arquivo `balconista.md` mistura 3 gêneros textuais diferentes:
1. **Linhas 1 a 31:** Descritivo oficial de cargo / Papel de domínio.
2. **Linhas 32 a 60:** Parecer consultivo jurídico e anotações internas para a gerência.
3. **Linhas 61 a 70:** Minuta de cláusula para contrato de trabalho.

**Recomendação arquitetural:** O descritivo do papel deve ser um documento limpo, corporativo e com respaldo legal integrado. As notas jurídicas e a minuta de contrato devem ficar em seções anexas bem demarcadas ou documentos próprios de RH/Compliance.

---

### 3. Proposta de Revisão Completa para o Arquivo

Aqui está o documento reformulado, 100% blindado juridicamente e alinhado aos padrões da CLT, NRs, TST e LGPD:
([docs/domain/roles/balconista.md](/docs/domain/roles/balconista.md))
