# Papel de Domínio: Operador de Caixa

**Classificação Brasileira de Ocupações (CBO de Referência):**
* 4211-25 (Operador de caixa)

---

## 1. Objetivo do Papel
Operar o terminal de Ponto de Venda (PDV/POS), responsabilizar-se pelo recebimento de valores, validação de meios de pagamento, conferência de mercadorias, emissão de documentos fiscais e conciliação de fechamento de caixa, assegurando agilidade, exatidão financeira, cordialidade no atendimento final ao cliente e estrita observância às normas fiscais e de segurança da empresa.

---

## 2. Atribuições Principais (Frente de Caixa e Cobrança)

* **Abertura e Conferência do Fundo de Troco:** Realizar a conferência inicial dos valores destinados ao fundo de troco (suprimento de caixa) no início do turno de trabalho, registrando a abertura no sistema POS.
* **Processamento de Vendas e Cobrança:**
  * **Cenário Direto ao Caixa:** Bipar e registrar mercadorias via leitor de código de barras óptico, conferir preços e aplicar descontos ou cupons autorizados pela gerência.
  * **Cenário Balcão / Pré-Venda:** Receber o ticket de pré-venda emitido pelos vendedores, digitar ou bipar o identificador do pedido no sistema e resgatar os itens para pagamento.
* **Recebimento e Validação de Pagamentos:**
  * Processar pagamentos através de cartões de débito/crédito (TEF ou POS), PIX, dinheiro e outros meios autorizados.
  * Conferir a autenticidade de cédulas recebidas e calcular/entregar o troco correto ao cliente com segurança e atenção.
  * **Política de Retentativa:** Em caso de transação negada por saldo ou falha de comunicação, orientar o cliente e aplicar a política operacional de retentativa com meios alternativos antes de sugerir o cancelamento.
* **Emissão Fiscal e Liberação de Mercadorias:**
  * Solicitar a identificação do cliente para emissão do documento fiscal (NFC-e / Cupom Fiscal), quando solicitado ou obrigatório por lei.
  * Emitir e entregar o cupom fiscal e comprovante de pagamento ao cliente.
  * Liberar a sacola/embalagem das mercadorias devidamente conferidas ao comprador ou despachar para o setor de entrega.
* **Cancelamentos e Devoluções:** Executar comandos de cancelamento de pedidos não finalizados ou com desistência do cliente no terminal POS, disparando o estorno da reserva e a liberação automática dos itens no catálogo/estoque pelo backend.
* **Sangrias e Fechamento de Caixa:**
  * Realizar retiradas periódicas de numerário acumulado (sangrias), conforme limites de segurança estabelecidos pela empresa, repassando os valores à tesouraria/gerência mediante protocolo.
  * Efetuar o fechamento financeiro do turno, conferindo os relatórios de vendas em cartão, PIX e o montante em espécie, registrando eventuais divergências no relatório de fechamento diário.

---

## 3. Atribuições Subsidiárias e Conexas (Organização e Suporte Operacional)
Com fundamento no **Artigo 456, parágrafo único, da CLT** e na jurisprudência do Tribunal Superior do Trabalho (TST), o operador de caixa colabora com atividades correlatas durante períodos de menor fluxo ou pausas operacionais programadas:

* **Manutenção e Insumos do Checkout:** Manter a estação de caixa abastecida com insumos essenciais (bobinas térmicas para emissão fiscal, sacolas, elásticos, canetas e formulários).
* **Organização dos Produtos de Conveniência:** Organizar e manter alinhados os produtos dispostos nas proximidades da frente de caixa (displays de conveniência/vitrines de balcão).
* **Triagem de Itens Desistidos:** Organizar e separar produtos deixados por desistência na área do caixa para posterior devolução e reposição nas prateleiras pela equipe de vendas/estoque.
* **Limpeza e Asseio do Checkout:** Realizar a higienização e organização superficial do balcão de checkout, do teclado e do leitor óptico.
  > ⚠️ **Diretriz de Segurança (NR-15 e Súmula 448 do TST):** É **expressamente vedada** ao operador de caixa a execução de limpeza pesada, higienização de sanitários de uso público/coletivo, recolhimento de lixo sanitário ou uso de produtos químicos cáusticos.

---

## 4. Integração com Sistemas e Arquitetura do Negócio (POS / Alpha Engine)
* **Terminal PDV (Frente de Caixa):** Operação contínua do software de checkout para processamento de transações, estornos, sangrias e consultas de pedidos pendentes.
* **Mecanismos de Idempotência e Transação:** Observar o fluxo seguro de finalização de vendas do sistema, aguardando o retorno de confirmação de transação bancária antes da entrega de produtos para evitar duplicidades de cobrança.
* **Comunicação com a Gerência:** Reportar imediatamente falhas de conexão de rede, lentidão no TEF/PIN Pad ou inconsistências de estoque no sistema.

---

## 5. Diretrizes de Segurança, Saúde do Trabalho e Conformidade Legal

### A. Ergonomia e Bem-Estar (NR-17 - Anexo I)
Em cumprimento à **Norma Regulamentadora nº 17 (Ergonomia)**, especificamente o seu **Anexo I (Trabalho dos Operadores de Checkout)**:
* **Mobiliário Adequado:** O posto de trabalho do caixa conta com cadeira ergonômica com encosto e altura ajustável, apoio regulável para os pés e espaço livre para membros inferiores.
* **Alternância de Postura:** O operador tem a prerrogativa de alternar o trabalho na posição sentada e em pé, conforme sua comodidade e recomendação ergonômica.
* **Uso de Scanner Óptico:** A leitura de produtos deve ser realizada prioritariamente com o leitor óptico (fixo ou pistola), evitando a digitação manual excessiva de códigos de barras.
* **Vedação a Cobrança Abusiva:** Em consonância com o item 6 do Anexo I da NR-17, é terminantemente proibida a cobrança de produtividade excessiva ou metas desmedidas de itens passados por minuto que possam comprometer a integridade física e postural do colaborador.

### B. Proteção de Dados e Sigilo Bancário (LGPD - Lei nº 13.709/2018)
* **Privacidade do Cliente:** Os dados coletados na frente de caixa (como CPF para nota fiscal, telefone e nome) destinam-se única e exclusivamente ao cumprimento de obrigações fiscais e de cadastro formal.
* **Sigilo de Dados Financeiros:** É terminantemente proibido registrar, fotografar, anotar em papéis avulsos ou compartilhar dados de cartões de crédito/débito de clientes, senhas ou documentos pessoais.
* **Uso Exclusivo do Terminal:** É vedado o uso de dispositivos celulares pessoais ou câmeras particulares na área restrita de manuseio de dinheiro e cartões durante o atendimento.

### C. Segurança Física e Transporte de Valores (Lei nº 7.102/1983 e Súmulas do TST)
* **Vedação a Transporte Externo de Valores:** O operador de caixa é responsável pela guarda do dinheiro exclusivamente no interior de sua gaveta/posto de trabalho e repasse para o cofre da loja através de sangria interna.
* É **expressamente proibido** atribuir ao operador de caixa o transporte de valores fora do estabelecimento para depósitos bancários na rua, atividade privativa de empresas de transporte de valores ou pessoal devidamente especializado.

---

## 6. Requisitos de Conduta e Gestão Produtiva do Tempo
* **Pontualidade e Prontidão:** Respeitar rigorosamente a escala de abertura e fechamento de turno, garantindo que o posto esteja operacional nos horários de maior fluxo de clientes.
* **Atenção Concentrada:** Manter foco absoluto durante o recebimento de numerário e devolução de troco, minimizando margens de erro de digitação ou conferência.
* **Cortesia e Empatia:** Representar a etapa final da experiência do cliente na loja com presteza, resolução cordial de dúvidas e agilidade.
* **Gestão do Tempo e Pausas (Arts. 4º e 71 da CLT):** Utilizar os períodos de baixa circulação para organizar bobinas, insumos e relatórios de cartões, respeitadas as pausas ergonômicas e o intervalo intrajornada regulamentar.

---

## Anexo I: Diretrizes Legais para a Gerência e RH

1. **Adicional de Quebra de Caixa:**
   * Verificar a **Convenção Coletiva de Trabalho (CCT)** do sindicato dos comerciários da região. Caso a CCT determine o pagamento de adicional de "Quebra de Caixa", a rubrica deve constar expressamente no holerite do colaborador durante todo o período em que estiver investido na função.
2. **Desconto Salarial por Diferenças de Caixa (Art. 462, § 1º da CLT):**
   * O desconto de diferenças negativas de caixa na folha de pagamento só é juridicamente lícito se houver **cláusula expressa de autorização prévia assinada no Contrato de Trabalho**, com comprovação de culpa (imprudência ou negligência) na apuração do fechamento, observadas as tolerâncias e regras da CCT.
3. **Ponto e Conferência de Fechamento (Art. 74 da CLT):**
   * O procedimento de fechamento de caixa, contagem de cédulas e conciliação de comprovantes de cartão deve ocorrer **obrigatoriamente dentro da jornada de trabalho registrada no ponto eletrônico**, sendo vedada a contagem após o encerramento do ponto.

---

## Anexo II: Sugestão de Cláusula para Contrato de Trabalho

> **CLÁUSULA DE ATRIBUIÇÕES, RESPONSABILIDADE FINANCEIRA E OPERAÇÃO DE CAIXA:**
>
> **Item 1:** O(A) EMPREGADO(A) é contratado(a) para exercer a função de **OPERADOR(A) DE CAIXA** (CBO 4211-25), incumbindo-se da abertura e fechamento de terminal, atendimento aos clientes, registro de mercadorias, recebimento de pagamentos sob qualquer modalidade autorizada pela EMPREGADORA, emissão de cupons fiscais e conferência de numerário.
>
> **Item 2:** Em razão da natureza de suas atribuições, o(a) EMPREGADO(A) responde pela guarda, exatidão e conferência de todos os valores monetários, comprovantes de cartões e títulos de crédito confiados à sua guarda durante o seu turno de trabalho.
>
> **Item 3:** Havendo previsão na Convenção Coletiva de Trabalho aplicável, a EMPREGADORA pagará ao(à) EMPREGADO(A) o adicional de "Quebra de Caixa" estipulado pela categoria enquanto durar o exercício efetivo da função.
>
> **Item 4:** Nos termos do Artigo 462, § 1º, da Consolidação das Leis do Trabalho (CLT), fica expressamente ajustado que eventuais prejuízos, faltas injustificadas de caixa ou diferenças negativas apuradas nos fechamentos diários, resultantes de negligência, imprudência ou imperícia do(a) EMPREGADO(A), poderão ser descontados de sua remuneração, garantido o direito de conferência e fiscalização do fechamento na presença do próprio colaborador ou de testemunha.
>
> **Item 5:** O(A) EMPREGADO(A) compromete-se a observar rigorosamente as normas de ergonomia (NR-17), alternando posturas e utilizando os descansos e assentos fornecidos, bem como a guardar estrita confidencialidade sobre dados de identificação e meios de pagamento dos clientes da empresa, em estrita conformidade com a LGPD (Lei nº 13.709/2018).
