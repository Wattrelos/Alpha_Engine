# UC_PRV_001 - Consultar Oportunidades no Raio de Atendimento

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_PRV_001` |
| **Nome** | Consultar Oportunidades de Obras no Raio de Atendimento |
| **Módulo** | Portal do Prestador - Oportunidades & Matching Geográfico |
| **Atores Primários** | Prestador de Serviços Autenticado (*Service Provider*) |
| **Atores Secundários** | Sistema Alpha Engine (Motor Geoespacial / Haversine) |
| **Tipo** | Condução / Descoberta de Oportunidades |
| **Frequência de Uso** | Alta (diária) |
| **Rastreabilidade** | **RF:** [RF033](/docs/requirements/functional/products_quotation.yaml) (RFQ de projetos), [RF034](/docs/requirements/functional/products_quotation.yaml) (Matching por raio geográfico)<br>**RN:** Regra de Raio de Cobertura ($X$ km, padrão 25 km), Fallback por Cidade/Estado<br>**RNF:** [RNF002](/docs/requirements/non_functional/non_functional_requirements.yaml) (Performance de busca geoespacial < 500ms) |

---

## 1. 🎯 Descrição Sumária
Permite ao prestador de serviços credenciado acessar o painel de oportunidades na rota `/prestador/oportunidades`, visualizando todas as solicitações de orçamento de projetos (RFQs) ativas cujos locais de execução estejam situados dentro do seu raio de atendimento geográfico configurado (calculado via fórmula de Haversine ou fallback municipal). O prestador pode filtrar as obras por categoria, raio de distância e prazo pretendido.

---

## 2. ⚡ Pré-Condições
1. Prestador cadastrado e autenticado no sistema com perfil ativo em `agsc_service_provider_profile`.
2. Perfil do prestador deve conter coordenadas geográficas válidas (latitude e longitude) ou CEP de base com raio de cobertura definido (`service_radius_km > 0`).
3. Existência de projetos RFQ cadastrados com status `'open'` por clientes na plataforma.

---

## 3. ✅ Pós-Condições
- O prestador visualiza a lista filtrada de projetos elegíveis, com distância estimada em km até o canteiro de obras, escopo sumário, data limite de proposta e categoria.

---

## 4. 🚀 Gatilho (Trigger)
O profissional clica no menu "Oportunidades de Serviços" no Portal do Prestador ou acessa a rota `/prestador/oportunidades`.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa `/prestador/oportunidades`.
2. **Sistema:** Recupera as coordenadas (latitude e longitude) e o raio configurado (`service_radius_km`, ex: 25 km) do perfil do prestador (`agsc_service_provider_profile`).
3. **Sistema:** Executa a consulta geoespacial no banco de dados (`GeoMatchingService`), calculando a distância esférica ortodrômica pela **fórmula de Haversine**:
   $$d = 2r \arcsin\left(\sqrt{\sin^2\left(\frac{\Delta \phi}{2}\right) + \cos(\phi_1)\cos(\phi_2)\sin^2\left(\frac{\Delta \lambda}{2}\right)}\right)$$
4. **Sistema:** Filtra apenas as RFQs abertas (`status = 'open'`) cuja distância $d \le \text{service\_radius\_km}$ e que ainda não atingiram o teto de 10 propostas.
5. **Sistema:** Renderiza o feed de oportunidades ordenado pela proximidade e data de publicação, exibindo:
   - Título e Categoria da Obra (ex: *"Reforma de Banheiro e Troca de Revestimento - 12m²"*);
   - Distância calculada em km (ex: *"A 6,4 km da sua base"*);
   - Bairro, Cidade e UF da obra (endereço completo e dados do cliente são omitidos por privacidade);
   - Expectativa de prazo de execução e data limite para envio da proposta;
   - Indicador de quantas propostas já foram submetidas por concorrentes.
6. **Ator:** Analisa os detalhes sumários e clica em uma oportunidade para preparar o orçamento (`UC_PRV_002`).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Filtragem por Categoria Técnica:**
  1. No passo 5, o prestador aplica o filtro para exibir apenas projetos de sua especialidade (ex: "Elétrica & Cabeamento" ou "Pisos & Porcelanatos").
  2. O sistema reaplica o filtro e atualiza a listagem sem recarregar a página (via AJAX).
- **FA02 - Expansão Provisória do Raio de Busca:**
  1. No passo 5, o prestador utiliza o slider visual para simular oportunidades em até 50 km.
  2. O sistema recalcula as distâncias e exibe as obras disponíveis na nova faixa.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Prestador sem Coordenadas Geográficas Cadastradas:**
  1. No passo 2, o sistema detecta ausência de latitude/longitude no perfil do prestador.
  2. O sistema ativa o mecanismo de fallback por Cidade e Estado (`address_city` e `address_state`).
  3. O sistema exibe um aviso orientando a atualização do CEP base: *"Para calcularmos a distância exata em km até cada obra, complete seu endereço completo no perfil."*
- **FE02 - Nenhuma Obra Aberta no Raio do Prestador:**
  1. No passo 4, nenhuma RFQ aberta atende aos critérios geográficos.
  2. O sistema exibe tela amigável: *"No momento não há novas solicitações de obra no seu raio de [X] km. Você receberá um e-mail/notificação assim que uma nova oportunidade for cadastrada na sua região."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN-GEO-01 (Raio de Cobertura Estrito):** O prestador só recebe e visualiza oportunidades onde a distância geográfica seja menor ou igual ao seu raio configurado (`service_radius_km`), evitando propostas logisticamente inviáveis.
- **RN-GEO-02 (Privacidade Pré-Contratação):** O endereço exato (número da casa e logradouro) e os dados de contato direto do cliente são omitidos até que uma proposta seja formalmente aceita.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Parâmetros opcionais de busca: `category`, `radius_km`, `order_by`.

### Saídas:
- Coleção de cards de projetos contendo: `rfq_id`, `title`, `category`, `distance_km`, `city`, `deadline_days`, `total_bids`.
