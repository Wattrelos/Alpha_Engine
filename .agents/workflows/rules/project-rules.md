---
description: 
---

# 🛑 Travas de Segurança e Arquitetura - Stack PHP/Slim

## 1. Preservação da Arquitetura do Sistema
- **Camada de Dados (MySQL / DTOs)**: Antes de alterar qualquer tabela ou Data Transfer Object (DTO), o agente deve inspecionar as referências em todo o projeto. Nunca remova propriedades de DTOs ou colunas de tabelas se houver dependências ativas no código.
- **Estrutura Slim / Rotas**: Classes de Action/Controller do Slim Framework e arquivos de definição de rotas são o núcleo do sistema. Bloqueie qualquer comando de exclusão direta desses arquivos e exija validação de impacto.
- **Camada Visual (Twig/JS)**: Se o usuário pedir para remover um arquivo JS ou template `.twig`, verifique se ele está sendo renderizado por alguma rota do Slim ou incluído (`{% include %}`) em outros templates antes de agir.

## 2. Intervenção contra Más Práticas em PHP/JS
Interrompa a execução automática e alerte o usuário se ele solicitar:
- **Queries Cruas/Injeção de SQL**: Tentar concatenar variáveis diretamente em strings SQL. Exija o uso de Prepared Statements via PDO ou o Query Builder do projeto.
- **Lógica de Negócio em Views**: Tentar escrever lógica PHP pesada, consultas de banco de dados ou manipulação de DTOs diretamente dentro dos templates Twig.
- **Manipulação Global em JS**: Códigos JavaScript legados que poluam o escopo global (`window.x`). Exija padrões modulares modernos.
- **Quebra do padrão DTO**: Injetar arrays associativos cruas do MySQL diretamente nas views, ignorando a tipagem dos DTOs estabelecidos.

## 3. Protocolo de Resposta
Caso o usuário envie uma ordem destrutiva ou antipadrão, congele o terminal e responda no formato:
1. **[ALERTA PHP/SLIM ARCHITECTURE]**: Explique o risco.
2. **[IMPACTO]**: Onde o código ou banco vai quebrar.
3. **[SOLUÇÃO RECOMENDADA]**: Proponha a refatoração correta dentro dos padrões do Slim/DTO.
