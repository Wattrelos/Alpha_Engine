# DP-68: Configuração e Otimização do PHPStan para Agentes de IA

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-09 16:04:59
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/68

## Descrição

# Plano de Configuração e Otimização do PHPStan para Agentes de IA

Este plano estabelece uma configuração de análise estática com **PHPStan 2.x** altamente otimizada para a atuação de **Agentes de IA (como Antigravity/Gemini)**.

## O Desafio: Ruído vs. Sinal para a Inteligência Artificial

Atualmente, o comando `phpstan analyse` reporta **606 erros legados** na base de código `core/`.
Para um **Agente de IA**, receber 606 erros de código legado a cada edição gera "poluição de contexto" (noise), dificultando identificar se a modificação atual introduziu um novo bug ou se é um erro pré-existente.

## Soluções Propostas

### 1. Baselining de Código Legado (`phpstan-baseline.neon`)
- Gerar o arquivo `phpstan-baseline.neon` contendo o mapa de erros legados acumulados.
- Incluir o baseline no `phpstan.neon`.
- **Efeito**: Execuções rotineiras de análise estática pelos Agentes de IA retornarão **0 erros** no estado limpo. Se o agente cometer um erro ao editar ou criar código, o PHPStan reportará **APENAS a regressão ou o novo erro específico**, permitindo correção cirúrgica e instantânea.

### 2. Otimização do `phpstan.neon` para IA
- Ajustar os caminhos analisados (`core`, `Config`, `Containers`, `config.php`).
- Configurar o diretório de cache isolado (`.phpunit.cache/phpstan` ou `.reports/cache`).
- Configurar flags para evitar falsos positivos comuns em código PHP dinâmico (como mágicos `__get` em entidades e repositórios).

### 3. Automação via Composer Scripts em `composer.json`
- `composer stan`: Executa a análise estática rápida filtrada pelo baseline.
- `composer stan:report`: Gera relatório estruturado JSON em `.reports/phpstan-report.json` para parsing automatizado por IA.
- `composer stan:baseline`: Script para atualizar o baseline quando o código legado for refatorado.

---

## Proposed Changes

### Arquivos de Configuração

#### [MODIFY] [phpstan.neon](/phpstan.neon)
- Atualizar parâmetros do PHPStan, registrar inclusão do `phpstan-baseline.neon` e diretórios de cache.

#### [NEW] [phpstan-baseline.neon](/phpstan-baseline.neon)
- Gerar o arquivo de baseline do PHPStan com os 606 erros legados mapeados.

#### [MODIFY] [composer.json](/composer.json)
- Registrar scripts padronizados `stan`, `stan:report` e `stan:baseline`.

---

## Verification Plan

### Automated Tests
1. Executar `./vendor/bin/phpstan analyse --generate-baseline phpstan-baseline.neon` para criar o baseline.
2. Executar `composer stan` (ou `./vendor/bin/phpstan analyse`) e validar que a resposta seja **[OK] No errors** (ruído zero).
3. Executar `composer stan:report` e verificar que o relatório `.reports/phpstan-report.json` é gerado corretamente.

# Tarefas para Otimização do PHPStan para Agentes de IA

- [x] `[x]` Gerar baseline de erros legados (`phpstan-baseline.neon`)
- [x] `[x]` Configurar `phpstan.neon` incluindo o baseline e caminhos do projeto
- [x] `[x]` Atualizar `composer.json` com scripts `stan`, `stan:report` e `stan:baseline`
- [x] `[x]` Validar execução do PHPStan sem ruído e geração de relatórios JSON em `.reports/`
- [x] `[x]` Gerar walkthrough com instruções e documentação

# Guia de Configuração e Uso do PHPStan Otimizado para Agentes de IA

O **PHPStan 2.x** foi totalmente configurado e otimizado para proporcionar uma análise estática sem ruído (Zero-Noise Static Analysis), acelerando o ciclo de desenvolvimento e permitindo que **Agentes de IA (como Antigravity/Gemini)** identifiquem erros de código com máxima precisão.

---

## 🛠️ Principais Recursos Configurados

1. **Baselining de Erros Legados (`phpstan-baseline.neon`)**:
   - Isolamento dos 604 avisos legados em `phpstan-baseline.neon`.
   - Permite que `composer stan` retorne **[OK] No errors** no estado limpo.
   - Qualquer erro introduzido por refatorações ou novas rotas criadas por Agentes de IA será apontado imediatamente de forma isolada.

2. **Configuração `phpstan.neon`**:
   - `level: 5` equilibrado para arquitetura PSR-15 e Slim Framework.
   - Escopo de verificação em `core`, `Config`, `Containers` e `config.php`.
   - Diretório de cache isolado em `.phpunit.cache/phpstan`.

3. **Comandos Integrados no `composer.json`**:
   - `composer stan`: Executa a verificação estática padrão rápida.
   - `composer stan:report`: Gera relatório em formato JSON estruturado em `.reports/phpstan-report.json`.
   - `composer stan:baseline`: Regenera o baseline caso refatorações de código antigo sejam consolidadas.

---

## 🚀 Como Executar

### Análise Estática Padrão (Saída Limpa no Terminal)
```bash
composer stan
```

**Resultado Esperado:**
```text
Note: Using configuration file /var/www/html/agsonhos/phpstan.neon.
 [OK] No errors
```

---

### Geração de Relatório JSON para Agentes de IA
```bash
composer stan:report
```

O arquivo [.reports/phpstan-report.json](/.reports/phpstan-report.json) será atualizado com a estrutura JSON parseável:
```json
{
  "totals": {
    "errors": 0,
    "file_errors": 0
  },
  "files": {},
  "errors": []
}
```

---

### Atualização do Baseline Pós-Refatoração
Após corrigir um grupo de erros legados, execute:
```bash
composer stan:baseline
```

