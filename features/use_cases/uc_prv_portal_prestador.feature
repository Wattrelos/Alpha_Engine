# language: pt

@prestador @portal_prestador @rfq @boq @takeoff @UC_PRV_001 @UC_PRV_002 @UC_PRV_003 @RF033 @RF034 @RF035 @RF036 @RF037
Funcionalidade: Portal do Prestador de Serviços (Alpha Pro)
  Como um prestador de serviços credenciado na plataforma Alpha Engine
  Eu quero consultar oportunidades de obras no meu raio geográfico, enviar propostas comerciais e elaborar listas técnicas de materiais (BoQ)
  Para que eu possa fechar contratos de mão de obra e permitir que meus clientes comprem materiais com desconto no e-commerce

  Contexto:
    Dado que a plataforma Alpha Engine possui prestadores de serviço credenciados
    E projetos de clientes (RFQs) estão abertos para concorrência na região

  @matching @geolocalizacao @UC_PRV_001 @RF034
  Cenário: Prestador consulta oportunidades abertas dentro do seu raio de atendimento
    Dado que o prestador está autenticado com base em "São Paulo - SP" e raio configurado de "35" km
    Quando o prestador acessa o feed de oportunidades em "/pt-br/prestador/oportunidades"
    Então o sistema deve listar os projetos com status "open" cuja distância seja menor ou igual a 35 km
    E deve exibir a distância calculada pela fórmula de Haversine para cada oportunidade
    E o prestador deve visualizar a expectativa orçamentária e prazo desejado pelo cliente

  @filtros @categoria @raio @UC_PRV_001
  Cenário: Prestador filtra oportunidades por especialidade técnica e expande o raio de busca
    Dado que o prestador está na página de oportunidades
    Quando o prestador filtra pela categoria "Elétrica & Infraestrutura" e simula o raio para "50" km
    Então a listagem deve exibir somente obras da especialidade selecionada
    E deve recalcular o número de oportunidades disponíveis na nova abrangência

  @proposta @bid @UC_PRV_002 @RF035
  Cenário: Submissão de proposta comercial de mão de obra com sucesso
    Dado que o prestador seleciona a oportunidade "Reforma de Banheiro e Troca de Revestimento - 12m²"
    Quando o prestador acessa o formulário de proposta em "/pt-br/prestador/projetos/1/proposta"
    E preenche o valor de mão de obra "2800.00", prazo de "10" dias e detalha o memorial descritivo
    E clica em "Enviar Proposta ao Cliente"
    Então a proposta deve ser persistida com status "submitted"
    E o prestador é redirecionado para o painel de oportunidades com mensagem de confirmação

  @regras_negocio @limite_propostas @RN_BID_01 @UC_PRV_002
  Cenário: Bloqueio de novas propostas após atingir o limite máximo de 10 concorrentes
    Dado que um projeto RFQ já atingiu 10 propostas comerciais submetidas
    Quando um novo prestador tenta enviar uma proposta para esta solicitação
    Então o sistema deve bloquear a gravação
    E deve informar que o limite máximo de 10 propostas concorrentes foi atingido

  @takeoff @boq @materiais @UC_PRV_003 @RF036
  Cenário: Prestador contratado acessa Takeoff Tool e adiciona insumos à lista de materiais
    Dado que o cliente aceitou a proposta do prestador para a obra "Construção de Espaço Gourmet"
    E o projeto está homologado com status "awarded" para o prestador
    Quando o prestador acessa a ferramenta em "/pt-br/prestador/projetos/3/takeoff"
    Então a grade de lista de materiais (BoQ) deve ser carregada
    Quando o prestador adiciona um insumo "Porcelanato Acetinado 60x60" com quantidade "45" e preço "79.90"
    Então o item deve ser inserido na tabela do BoQ
    E o total estimado da lista de materiais deve ser recalculado automaticamente

  @seguranca @autorizacao @UC_PRV_003
  Cenário: Tentativa de acesso à Takeoff Tool por prestador não homologado
    Dado que um prestador não foi o profissional selecionado para um projeto
    Quando ele tenta acessar diretamente a URL de Takeoff daquele projeto
    Então o sistema deve negar o acesso com código HTTP 403 Proibido
    E deve informar que a ferramenta é restrita ao profissional contratado pelo cliente
