---
adr: 6
title: Adoção do Forgejo como Plataforma de Controle de Versão e CI/CD Self-Hosted no Debian 13
status: Approved
date: 2026-08-30
authors:
  - Antigravity AI
  - Josias
impacted_components:
  - infrastructure: "Git Server / VCS"
  - os_platform: "Debian GNU/Linux 13 (Trixie)"
  - ci_cd_engine: "Forgejo Actions"
  - deployment: "Docker / Standalone Go Binary"
rules:
  license_model: "100% Free & Open Source (GPLv3)"
  hosting_mode: "Self-Hosted / Local Mirror & Fallback"
  ci_cd_compatibility: "GitHub Actions YAML format compatible"
  resource_constraints:
    max_memory_footprint: "< 150MB RAM"
    storage: "Local persistent volumes / Git bare repos"
---

# ADR 006: Adoção do Forgejo como Plataforma de Controle de Versão e CI/CD Self-Hosted no Debian 13

## Status
Aprovado (2026-08-30)

## Contexto
Recentemente, o ecossistema de desenvolvimento e entrega contínua enfrentou incidentes de indisponibilidade e instabilidade técnica em plataformas de hospedagem Git de terceiros (como o GitHub). Diante do risco operacional de interrupção nas esteiras de deploy, perda temporária de acesso ao código-fonte da Alpha Engine e potenciais bloqueios de conta ou repositório, surgiu a necessidade premente de estabelecer uma infraestrutura de controle de versão *self-hosted* resiliente.

Para garantir a soberania técnica e a segurança operacional do projeto, a solução a ser adotada precisava satisfazer a 3 requisitos não-funcionais inegociáveis:
1. **Completude Funcional**: Disponibilizar gestão completa de repositórios, controle de issues/tarefas, documentação via Wiki, quadros Kanban e automação de testes com esteira de CI/CD nativa.
2. **Software 100% Livre e Governança Comunitária**: Código totalmente auditável, sem modelos comerciais agressivos do tipo *open-core*, com licença de código aberto (FOSS) e governança transparente sem risco de privatização do software.
3. **Leveza, Baixo Consumo e Compatibilidade com Debian 13 (Trixie)**: Operar de forma nativa e estável no Debian 13, mantendo consumo de memória RAM inferior a 150MB sem poluir o sistema operacional host com dependências complexas.

---

## Comparativo Técnico e Avaliação de Alternativas

Para embasar a tomada de decisão, foram comparadas as 3 principais soluções self-hosted do mercado frente aos critérios definidos:

| Critério de Avaliação | Forgejo (Opção Escolhida) | Gitea | GitLab CE (Community Edition) |
|---|---|---|---|
| **Completude Funcional** | **Muito Completo**: Gerenciamento de código, Issues, Wiki, Projetos, Kanban e o **Forgejo Actions** (esteira CI/CD totalmente compatível com a sintaxe do GitHub Actions). | **Médio**: Possui interface visual e ferramentas semelhantes, mas o ecossistema de CI/CD nativo é menos maduro e diverge em padrões comunitários. | **Máximo**: Plataforma DevOps corporativa integral (Monitoramento avançado, DAST/SAST, Container Registry, CI/CD robusto). |
| **Licenciamento & Liberdade** | **100% Livre (GPLv3)**: Criado e gerido pela comunidade sob a *Codeberg e-Forgejo Foundation*, sem fins lucrativos e com foco estrito no interesse público. | **Parcial (MIT)**: Embora aberto, o projeto é governado por uma empresa com fins lucrativos (*Gitea Ltd*), sujeitando a comunidade a decisões comerciais unilaterais. | **Open-Core**: A versão comunitária é gratuita, mas recursos críticos de segurança, governança e relatórios são proprietários e pagos (*GitLab EE*). |
| **Compatibilidade & Recursos (Debian 13)** | **Excelente**: Binário único em Go ou imagem Docker ultraotimizada. Consumo médio de **< 100MB de RAM**. Pacotes e guias dedicados para o Debian 13. | **Excelente**: Binário único em Go e baixo consumo de hardware (< 120MB de RAM). | **Complexo & Pesado**: O instalador oficial (*Omnibus*) é monolítico e pesado. Exige no mínimo **4GB a 8GB de RAM** e dezenas de serviços dependentes (PostgreSQL, Puma, Sidekiq, Redis). |

---

## Decisão Arquitetural

Decidiu-se pela **adoção do Forgejo** como a plataforma oficial *self-hosted* de controle de versões, espelhamento contínuo de código e contingência operacional para o projeto AgSonhos e a Alpha Engine no ambiente Debian 13.

### Pilares da Escolha:

1. **Garantia de Liberdade e Soberania (GPLv3)**:
   * O Forgejo surgiu como um fork comunitário ético após a privatização do Gitea. Ele assegura que todas as funcionalidades desenvolvidas presentes e futuras permanecerão 100% livres, sem recursos artificialmente bloqueados sob assinaturas corporativas.
2. **Interoperabilidade com GitHub Actions (Forgejo Actions)**:
   * O Forgejo Actions implementa suporte direto à sintaxe de workflows YAML padrão do GitHub (`.forgejo/workflows` ou `.github/workflows`). Isso permite que os testes automatizados (Behat, PHPUnit e Playwright) rodem identicamente no servidor local sem necessidade de reescrever esteiras de automação.
3. **Footprint Mínimo no Debian 13**:
   * O software é empacotado como um binário único compilado em Go, consumindo menos de 100MB de RAM em repouso. A instalação pode ser operada via Docker Compose ou *systemd service* no Debian 13 de maneira limpa e isolada.
4. **Migração e Espelhamento com Zero Fricção**:
   * Suporte nativo a *Push/Pull Mirroring*, permitindo sincronizar automaticamente repositórios com o GitHub em tempo real para manter contingência ativa (backup vivo).

---

## Consequências

### Positivas (Prós)
* **Alta Disponibilidade e Independência**: A equipe mantém capacidade plena de desenvolvimento, deploy e revisão de código mesmo em cenários de queda global de serviços de nuvem de terceiros.
* **Economia de Recursos de Hardware**: Utilização irrisória de CPU e memória RAM no servidor Debian 13, permitindo compartilhar a mesma máquina com os serviços da loja sem concorrência de recursos.
* **Portabilidade de CI/CD**: Reutilização direta dos arquivos de testes e pipelines já configurados no projeto sem complexidade de adaptação.
* **Privacidade Total de Dados**: Nenhum dado sensível de código, chave de API interna ou histórico de commits trafega para terceiros sem autorização explícita.

### Negativas / Mitigações (Contras)
* **Responsabilidade sobre Backup da Infraestrutura**: A infraestrutura do servidor Forgejo exige rotina periódica de cópia de segurança (dump do banco SQLite/PostgreSQL e volumes Git).
  * *Mitigação*: Criação de cron job diário automatizado com compactação e envio seguro dos repositórios para armazenamento offsite criptografado.
* **Manutenção e Atualização Manual**: Atualizações de segurança da plataforma precisam ser aplicadas pela equipe de TI através da atualização das imagens Docker ou substituição do binário Go.
  * *Mitigação*: Utilização de containers Docker com versionamento semântico claro para atualizações rápidas via script.
