

# ***Alpha Engine: Plataforma E-commerce SaaS & Ponto de Venda para Materiais de Construção***

***Documento de Visão***

1. **Índice**

[1\.	Índice	2](#índice)  
[2\.	Objetivo	3](#objetivo)  
[3\.	Necessidade do Negócio	3](#heading)  
[4\.	Descrição do Escopo	3](#heading-1)  
[5\.	Equipe	3](#heading-2)  
[6\.	Especificações Técnicas	3](#heading-3)  
[7\.	Riscos	3](#heading-4)  
[8\.	Cronograma de Marcos Resumido	4](#heading-5)  
[9\.	Orçamento Resumido	4](#heading-6)

2. # **OBJETIVOS** {#objetivos}

   2.1. ## **Objetivo Geral** {#objetivo-geral}

Desenvolver uma plataforma de comércio eletrônico (*e-commerce*) praticamente do zero e com suporte nativo para o mercado brasileiro, com um motor (*engine*) de *back-end* desacoplado baseado nos padrões de projeto do *Gang of Four* (GoF), além de um *front-end* baseado em telas dinâmicas Twig, com *Java Script,* gerenciamento de sessão com assinaturas de páginas e certificados digitais, utilizando ferramentas de assistência baseadas em Inteligência Artificial para viabilizar a arquitetura e garantir a conformidade (*compliance*) fiscal e logística do modelo de negócios.

   2.2. ## **Objetivos Específicos** {#objetivos-específicos}

Para alcançar o objetivo geral, estabelecem-se os seguintes objetivos específicos:

* **Desacoplamento.** Utilizando arquiteturas de *e-commerce*, com foco no desacoplamento e dinamismo de consultas SQL de operações CRUD na camada de persistência;  
* **Projetar e implementar um motor de *back-end* agnóstico** utilizando padrões GoF (como *Strategy* e *Factory*), garantindo que as entidades do sistema sejam descobertas e manipuladas exclusivamente em tempo de execução;  
* **Desenvolver uma camada de tradução sintática eficiente** capaz de mediar a comunicação entre o padrão *snake\_case* (padrão em banco de dados) e os padrões *PascalCase* e *camelCase* exigidos pela arquitetura de objetos do *back-end,* além do *kebab case* geralmente utilizados em páginas html;  
* **Avaliar o impacto da utilização do assistentes de códigos baseados em I.A.** no processo de desenvolvimento solo, mensurando sua eficácia na detecção instantânea de inconsistências lógicas e erros de digitação (*typos*) sob restrições de tempo;  
* **Incorporar nativamente os requisitos fiscais e logísticos** obrigatórios do cenário tributário brasileiro (ICMS, NCM, Inscrição Estadual, CPF, CNPJ), e campos de endereço como Bairro e CEP) à lógica de fechamento de pedido *checkout*);  
* **Validar a solução proposta** por meio de um estudo de caso aplicado a uma loja de varejo do segmento de materiais de construção, demonstrando a manutenibilidade do sistema e a eficiência de sua arquitetura independente.


<!-- Descreva aqui o objetivo final do projeto, por exemplo, a criacao de um sistema de software. Qual? -->
Ao final, o objetivo é ter um sistema de e-commerce funcional e escalável, que possa ser utilizado por lojas de materiais de construção de pequeno e médio porte. O sistema deve ser capaz de gerenciar produtos, clientes, pedidos, estoque, pagamentos, entregas, etc. O sistema deve ser capaz de funcionar como um sistema de e-commerce e como um sistema de ponto de venda, com sincronização em tempo real entre as duas modalidades. 

3. **Necessidade do Negócio**

**O Cenário do Comércio Eletrônico no Brasil e as Barreiras de Localização**



3.1 O setor de comércio varejista de materiais de construção historicamente fundamentou suas operações em interações físicas, dependendo fortemente do atendimento em balcão e de canais de comunicação tradicionais, como o telefone. No entanto, o cenário contemporâneo apresenta uma transformação impulsionada pela digitalização dos hábitos de consumo. Constata-se que tanto o consumidor final quanto profissionais da área (engenheiros, arquitetos e empreiteiros) demandam maior agilidade, transparência de preços e conveniência no processo de aquisição de insumos.

Diante desse panorama, a ausência de um canal de vendas digital gera uma série de gargalos operacionais e comerciais para a organização em estudo, os quais justificam a necessidade latente de modernização tecnológica. A implementação de plataformas internacionais de *e-commerce* no mercado nacional frequentemente esbarra na complexidade das obrigações acessórias e na estrutura logística do país. Softwares globais conceituados demandam extensas customizações para mitigar lacunas funcionais que não atendem às especificidades brasileiras. Sob a perspectiva fiscal e operacional, essas peculiaridades dividem-se em dois eixos centrais:

3.1.1 **Limitação Geográfica e de Horário:** As vendas ficam restritas ao horário comercial e ao alcance físico da loja, impedindo a captação de clientes que realizam planejamentos de obras ou compras em horários alternativos.

3.1.1.1 **Ineficiência no Processo de Orçamentação:** O modelo tradicional exige que o cliente solicite orçamentos manualmente. Isso gera sobrecarga na equipe de atendimento e lentidão nas respostas, resultando em perda de vendas para concorrentes mais ágeis.

3.1.1.2 **Complexidade na Gestão de Catálogo e Estoque:** Materiais de construção possuem alta diversidade de SKUs (unidades de manutenção de estoque), variações de peso, volume e restrições de entrega logística. A falta de uma plataforma integrada dificulta a exibição em tempo real da disponibilidade dos produtos.

Portanto, a necessidade do negócio centraliza-se na expansão de sua presença de mercado e na otimização de suas operações por meio de uma plataforma de comércio eletrônico. A opção pelo modelo Software as a Service (SaaS) justifica-se pela urgência em adotar uma solução robusta, escalável e de rápida implementação, reduzindo a necessidade de investimentos elevados em infraestrutura de TI local e permitindo que a empresa foque em sua atividade-fim: a comercialização e a logística de materiais de construção.

3.1.2. **Peculiaridades Tributárias:**  
3.1.2.1. **Cálculo Automático de ICMS:** Necessidade de processamento do ICMS-ST (Substituição Tributária) e do Diferencial de Alíquota (DIFAL) nas operações interestaduais, dinâmicas que variam conforme o estado de destino;  
3.1.2.2. **Gestão de NCM:** Integração da Nomenclatura Comum do Mercosul (NCM) no catálogo de produtos, métrica indispensável para garantir a correta classificação fiscal e evitar autuações ou taxações incorretas;  
3.1.2.3. **Validação de Inscrição Estadual (IE):** Parametrização nativa para validar a IE de clientes cadastrados como Pessoa Jurídica (PJ), definindo a emissão de notas como isento ou não-contribuinte;  
3.1.2.4. **Emissão de Notas Fiscais (NF-e/NFC-e):** Integração para emissão de documentos fiscais eletrônicos diretamente pelo sistema, exigindo sincronização em tempo real com o controle de estoque.  
  *A engenharia de software aplicada ao comércio eletrônico no cenário brasileiro enfrenta desafios que superam as regras de negócio tradicionais de plataformas internacionais. A localização de um software para o mercado nacional exige o mapeamento nativo de obrigações acessórias fiscais e regras logísticas complexas. Sob a perspectiva tributária, o sistema deve computar de forma assíncrona o Imposto sobre Circulação de Mercadorias e Serviços (ICMS), gerenciando as particularidades da Substituição Tributária (ICMS-ST) e do Diferencial de Alíquota (DIFAL) nas operações interestaduais. Ademais, a classificação fiscal exige a vinculação da Nomenclatura Comum do Mercosul (NCM) ao catálogo de produtos, mitigando riscos de autuações fiscais. Diante do panorama contemporâneo de modernização fiscal, a arquitetura do banco de dados e do motor de persistência deve ser projetada de forma extensível para suportar a transição da Reforma Tributária nacional, prevendo a integração do Imposto sobre Bens e Serviços (IBS) e da Contribuição sobre Bens e Serviços (CBS).*

  3.3. **Logística e Formato de Endereçamento:** Ao contrário de sistemas estrangeiros orientados por *Zip Codes* genéricos, o ecossistema brasileiro exige campos estruturados para o Código de Endereçamento Postal (CEP) no formato 00000-000. Sistemas eficientes demandam rotinas de preenchimento automático (autocompletar) integradas à base dos Correios para determinar os logradouros, bairros, cidades e estados a partir do código, além de campos segregados para número e complemento. Adicionalmente, faz-se necessária a cubagem e cálculo nativo de frete integrando APIs dos Correios (SEDEX/PAC) e transportadoras regionais privadas.

4. **Descrição do Escopo**

Descreva o sistema de software que sera criado – quais subsistemas / modulos irao compor o produto final e as principais funcionalidades inclusas. Faça apenas uma breve descrição, os requisitos serãoo detalhados em outro documento.  
Aproveite essa secao para discriminar tambem todos os itens que estarao fora do escopo, com proposito de gerenciar expectativas do cliente. Ex. Modulos do sistema que nao serao implementados, mas poderao vir a ser, se o cliente optar por contratar um projeto de expansao apos a entrega do software.

5. **Equipe**

<!-- Liste os integrantes da equipe, formacao, experiencia e papeis e responsabilidades no projeto. -->
- Josias da Conceição Sobrinho

6. **Especificações Técnicas**

<!-- Descreva as especificacoes tecnicas do projeto. Quais tecnologias, plataformas e arquiteturas serao utilizadas? -->

7. **Riscos**

<!-- Riscos são eventos incertos (podem ou não ocorrer), mas se ocorrerem terão impactos direto sobre o planejamento do projeto. Riscos podem ser negativos (ameaças) ou positivos (oportunidades). Ex de categorias de riscos: Organizacionais (referente a política da empresa, verba, recursos e priorização), Gerenciais (estimativa, controle, comunicação), Técnicos (tecnologias deprecadas, término de licensas e suporte, riscos de qualidade, complexidade, requisitos) e Externos (Dependências de entidades externas, mercado, cliente). -->

<!-- Aproveite essa seção para identificar os riscos do projeto, mapeá-los de acordo com a matriz Probabilidade X Impacto e criar um plano de contingência para cada, com objetivo de mitigar, prevenir ou assumir o risco. -->

8. **Cronograma de Marcos Resumido**

<!-- Considerando o planejamento do projeto de acordo com as informacoes publicadas neste documento, os marcos iniciais do projeto sao: -->

| Marco | Data |
| :---- | :---- |
| Inicio do Projeto | dd/mm/yyyy |
| Especificacao de Requisitos | dd/mm/yyyy |
| Apresentacao de prototipos | .... |
| Modelagem da iteracao inicial | .... |
| Inicio do desenvolvimento do modulo X |  |
| Teste unitario do modulo X |  |
| .... |  |
| .... |  |
| Testes de Aceitacao do Usuario |  |
| Instalacao |  |
| Entrega do projeto final |  |

9. **Orçamento Resumido**

Apresentar um orcamento reduzido considerando:

* Custos fixos   
  * Hardware:
    Cenário 1:
      Como é um sistema SaaS, o cliente só precisará dos hardwares de PDV (Ponto de Venda), ou seja, os hardwares necessários são: Computador ou Tablet com navegador instalado e conexão com a internet, leitor de código de barras e impressora de nota fiscal.
    Cenário 2: 
      Caso o cliente deseje que o software funcione em modo local e/ou offline, o cliente precisará de um servidor local, o que implicaria em um custo adicional de hardware, tais como roteador, switches, cabos, computadores, etc. De forma similar ao cenário 1, precisará tambem de leitor de código de barras e impressora de nota fiscal.
    Resumindo: O custo dependerá da escolha do cliente em relação ao modelo de implantação (SaaS vs Local) e da quantidade de computadores/tablets que serão utilizados para o PDV.
    A estimativa de mínima de custo para um ponto de venda seria em torno de R$ 2.500,00, considerando: Computador ou Tablet, Leitor de código de barras e Impressora de nota fiscal.    

  * Licensas de software  
      O sistema em si não exigirá licensas de software, pois ele será desenvolvido utilizando tecnologias de código aberto.
      No entanto, dependendo das funcionalidades que o cliente desejar, pode haver necessidade de licensas de software, como por exemplo: 
      * Sistema de pagamento: Se o cliente desejar que o software tenha sistema de pagamento integrado, será necessário contratar um gateway de pagamento, que terá custo mensal.    
  * Treinamentos  
      O treinamento será realizado de forma presencial ou remota, dependendo da preferência do cliente.
* Custos variaveis (dependem do esforco de desenvolvimento e aumentam conforme o tempo do projeto)  
  * Hospedagem: Para o modelo SaaS, o cliente precisará contratar uma hospedagem para o software, que pode ser em um servidor local ou em um servidor na nuvem. O custo da hospedagem dependerá do plano escolhido, alem de 20% sobre cada venda realizada a título de consultoria. Há também o custo de dominio que é cobrada anualmente pelo Registro.br. Se o cliente optar pelo modelo local, não terá esse custo.   
  * Contrato de manutenção e assistência técnica: O custo de manutenção e assistência técnica dependerá do modelo de implantação (SaaS vs Local) e da quantidade de computadores/tablets que serão utilizados para o PDV. Como o software é livre, a manutenção e assistência técnica serão cobradas à parte, conforme o serviço contratado e de livre escolha do cliente.
  * Custo de instalacoes: O custo de instalacoes dependerá do modelo de implantação (SaaS vs Local) e da quantidade de computadores/tablets que serão utilizados para o PDV.  
  * Consumo de energia e materiais: O custo de consumo de energia e materiais dependerá do modelo de implantação (SaaS vs Local) e da quantidade de computadores/tablets que serão utilizados para o PDV.  
  * Operacao da rede de computadores: O custo de operacao da rede de computadores dependerá do modelo de implantação (SaaS vs Local) e da quantidade de computadores/tablets que serão utilizados para o PDV.  
* Orçamento para riscos (margem de contingência)

10. **Plano de Negócios**

10.1. **Modelo de Negócio**
Como o software é livre, o custo de desenvolvimento inicial será pago pelo próprio desenvolvedor. O retorno financeiro virá de serviços de consultoria, instalação, treinamento, manutenção e adaptações para clientes. Para a manutenção e assistência técnica, o cliente terá total liberdade de escolha, podendo contratar qualquer empresa ou profissional. O pagamento será feito diretamente ao desenvolvedor, sem intermediação de qualquer plataforma.  