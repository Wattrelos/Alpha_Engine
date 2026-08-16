### 🎨 1. Dashboard HTML Executivo (Modo Projeção & Slides)
Criamos um gerador de relatório visual interativo em HTML/CSS com estética moderna (Glassmorphism & Dark Mode), ideal para ser exibido em projetores ou anexado à documentação do trabalho.

- **Como gerar o relatório:**
  ```bash
  composer test:report
  ```
- **Arquivo gerado:** [relatorio_behat_academic.html](file:///var/www/html/agsonhos/docs/relatorio_behat_academic.html)
- **O que ele exibe:**
  - **Cards de Métricas:** Total de 26 Cenários, 161 Passos executados, Taxa de Sucesso de 100% e Indicação de 100% de Reaproveitamento PHPUnit.
  - **Grid de Funcionalidades:** Status de cada suíte (`CSRF Checkout`, `Segurança OWASP & RBAC`, `Idempotência Redis/RabbitMQ`, `Jornada do Cliente`, `Arquitetura DDD`).

---

### 🖥️ 2. Execução Passo a Passo em Tempo Real (Demonstração Ao Vivo no Terminal)
Para demonstrar a execução ao vivo em sala de aula, configuramos o modo *Pretty Output* do Behat:

- **Como executar ao vivo:**
  ```bash
  ./vendor/bin/behat --format=pretty
  ```
- **Resultado:** Exibe cada regra `Dado`, `Quando`, `Então` sendo validada em tempo real com realce em verde vivo e ícones de checagem.

---

### 📋 3. Matriz de Rastreabilidade Acadêmica
No arquivo [features/reame.md](file:///var/www/html/agsonhos/features/reame.md), adicionamos a tabela formal de rastreabilidade que vincula:
**Caso de Uso de Negócio $\rightarrow$ Arquivo `.feature` (Gherkin) $\rightarrow$ Contexto Behat $\rightarrow$ Teste Backend (PHPUnit)**.

---

### 🚀 Atalhos Rápidos para a Apresentação:

```bash
# 1. Rodar os testes Gherkin/Behat
composer test:behat

# 2. Rodar a suíte PHPUnit
composer test:phpunit

# 3. Gerar o Relatório Visual HTML para a Banca
composer test:report
```
