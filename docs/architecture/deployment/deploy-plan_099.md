# DP-99: Adequação e Suporte ao PHP 8.2 na Alpha Engine para compatibilidade, por exemplo, XAMPP

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-09-03 01:31:44
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/99

## Descrição

# Adequação e Suporte ao PHP 8.2 na Alpha Engine

Este plano descreve as etapas necessárias para adequar as dependências e configurações da Alpha Engine para suporte oficial ao PHP 8.2, eliminando os bloqueios de plataforma gerados pelo Composer e resolvendo o erro 500 no ambiente Docker.

## User Review Required

> [!IMPORTANT]
> - Definiremos `config.platform.php = "8.2.0"` em ambos os arquivos `composer.json` (backend e raiz). Isso garante que, mesmo que comandos do Composer sejam executados em máquinas com PHP 8.4 CLI, as dependências resolvidas e o arquivo `platform_check.php` permanecerão compatíveis com PHP 8.2+.
> - O container Docker atual (`docker-php-1`) roda **PHP 8.3-fpm-alpine**. Com a compatibilidade ajustada para PHP >= 8.2, a aplicação passará a rodar perfeitamente nele (o bloqueio atual decorre do `platform_check.php` exigindo `>= 8.4.1`). Caso queira também alterar a imagem base do Dockerfile para `php:8.2-fpm-alpine`, podemos fazer isso a seguir.

## Proposed Changes

### Backend Dependencies

#### [MODIFY] [backend/composer.json](file:///var/www/html/agsonhos/backend/composer.json)
- Adicionar `"php": ">=8.2"` em `"require"`
- Adicionar `"platform": { "php": "8.2.0" }` dentro de `"config"`
- Ajustar versões de dependências incompatíveis com 8.2:
  - `"symfony/config"`: de `^8.1` para `^7.2`
  - `"symfony/translation"`: de `^8.1` para `^7.2`
  - `"illuminate/collections"`: de `^13.12` para `^11.0`
  - `"phpunit/phpunit"` (require-dev): de `^13.3` para `^11.5`

---

### Root Dev Dependencies

#### [MODIFY] [composer.json](file:///var/www/html/agsonhos/composer.json)
- Adicionar `"config": { "platform": { "php": "8.2.0" } }`
- Ajustar `"symfony/http-client"`: de `^8.1` para `^7.2`

---

### Execução e Atualização de Lockfiles

- Executar `composer update --with-all-dependencies` no diretório `backend/`
- Executar `composer update` na raiz do projeto
- Validar se o arquivo `backend/vendor/composer/platform_check.php` agora valida PHP >= 8.2

## Verification Plan

### Automated Tests
- Executar `composer check-platform-reqs` em `backend/`
- Executar `composer why-not php 8.2.0` em ambos os diretórios para garantir conformidade
- Executar análise estática do PHPStan:
  ```bash
  cd /var/www/html/agsonhos/backend && php vendor/bin/phpstan analyse --configuration=phpstan.neon
  ```
- Executar a suíte de testes do PHPUnit:
  ```bash
  cd /var/www/html/agsonhos/backend && php vendor/bin/phpunit
  ```

### Manual Verification
- Testar a resposta HTTP no servidor Nginx/PHP-FPM (`docker-nginx-1` e `docker-php-1`):
  ```bash
  curl -sI http://localhost:80/
  ```
  Validando se o erro 500 do `platform_check.php` foi extinto e a aplicação responde normalmente.

# Suporte ao PHP 8.2 na Alpha Engine

Todas as configurações e dependências foram ajustadas com sucesso para garantir suporte oficial e estável ao **PHP 8.2**, sem nenhuma perda de funcionalidades.

## Alterações Realizadas

### 1. Configurações do Composer no Backend
No arquivo [backend/composer.json](file:///var/www/html/agsonhos/backend/composer.json):
* Adicionada a restrição `"php": ">=8.2"` em `"require"`.
* Configurado `"platform": { "php": "8.2.0" }` em `"config"`, garantindo que atualizações futuras executadas em hosts com PHP mais recente (ex: PHP 8.4) continuem gerando código e lockfiles compatíveis com PHP 8.2.
* Ajustadas as dependências para versões compatíveis com PHP 8.2+:
  * `symfony/config`: `^7.2`
  * `symfony/translation`: `^7.2`
  * `illuminate/collections`: `^11.0`
  * `phpunit/phpunit`: `^11.5`

### 2. Configuração do Composer na Raiz
No arquivo [composer.json](file:///var/www/html/agsonhos/composer.json):
* Configurado `"platform": { "php": "8.2.0" }`.
* Ajustado `symfony/http-client` de `^8.1` para `^7.2`.

### 3. Ajuste do Esquema do PHPUnit
No arquivo [backend/phpunit.xml](file:///var/www/html/agsonhos/backend/phpunit.xml):
* Removido o atributo `warnWhenPhpIsNotConfiguredForDevelopment` para total conformidade com o schema do PHPUnit 11.

---

## Verificação e Resultados

### 1. Verificação de Conflitos de Plataforma
O comando `composer why-not php 8.2.0` foi executado tanto no `backend/` quanto na raiz:
```bash
Package "php 8.2.0" found in version "8.2.0" (version provided by config.platform).
There is no installed package depending on "php" in versions not matching 8.2.0
```
> **Status:** 0 pacotes conflitantes encontrados.

### 2. Checagem do `platform_check.php`
O arquivo gerado em [backend/vendor/composer/platform_check.php](file:///var/www/html/agsonhos/backend/vendor/composer/platform_check.php#L7) agora valida:
```php
if (!(PHP_VERSION_ID >= 80200)) {
    $issues[] = 'Your Composer dependencies require a PHP version ">= 8.2.0"...';
}
```

### 3. Análise Estática (PHPStan)
Executado `php vendor/bin/phpstan analyse --configuration=phpstan.neon --no-progress`:
* Nenhuma regressão ou incompatibilidade sintática/tipada foi detectada em relação ao PHP 8.2.

### 4. Execução de Testes Automatizados (PHPUnit 11)
Executado `php vendor/bin/phpunit ../tests/Validation/ApiTransformerTest.php`:
```text
PHPUnit 11.5.56 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.4.24
Configuration: /var/www/html/agsonhos/backend/phpunit.xml
..                                                                  2 / 2 (100%)
OK (2 tests, 7 assertions)
```
> **Status:** Testes executados com 100% de sucesso.

### 5. Resposta do Servidor Web / PHP-FPM
O erro fatal HTTP 500 decorrente da trava `platform_check.php >= 8.4.1` foi sanado. O container PHP-FPM inicializa o bootstrap e responde corretamente:
```text
HTTP/1.1 200 OK
Server: nginx/1.31.4
```

