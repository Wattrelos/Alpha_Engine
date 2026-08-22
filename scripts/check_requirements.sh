#!/usr/bin/env bash

# ==============================================================================
# Alpha Engine - Pre-flight Environment & Requirements Checker
# ==============================================================================
# Script de verificação de pré-requisitos para instalação e deployment
# ==============================================================================

# Cores e Formatação
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# Contadores
TOTAL_CHECKS=0
PASSED_CHECKS=0
WARNING_CHECKS=0
FAILED_CHECKS=0

# Diretórios base
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
BACKEND_DIR="${ROOT_DIR}/backend"
STORAGE_DIR="${BACKEND_DIR}/storage"

print_header() {
    clear 2>/dev/null || true
    echo -e "${CYAN}${BOLD}"
    echo "========================================================================"
    echo "          🏛️  ALPHA ENGINE - VERIFICAÇÃO DE PRÉ-REQUISITOS              "
    echo "========================================================================"
    echo -e "${NC}"
    echo -e " Diretório do Projeto: ${BOLD}${ROOT_DIR}${NC}"
    echo -e " Data da Verificação:  $(date '+%d/%m/%Y %H:%M:%S')"
    echo ""
}

check_status() {
    local status="$1"
    local title="$2"
    local details="$3"

    TOTAL_CHECKS=$((TOTAL_CHECKS + 1))

    if [ "$status" -eq 0 ]; then
        PASSED_CHECKS=$((PASSED_CHECKS + 1))
        echo -e " [ ${GREEN}✔ OK${NC} ]  ${BOLD}${title}${NC} ${details}"
    elif [ "$status" -eq 1 ]; then
        WARNING_CHECKS=$((WARNING_CHECKS + 1))
        echo -e " [ ${YELLOW}▲ AVISO${NC} ] ${BOLD}${title}${NC} ${YELLOW}${details}${NC}"
    else
        FAILED_CHECKS=$((FAILED_CHECKS + 1))
        echo -e " [ ${RED}✖ ERRO${NC} ]  ${BOLD}${title}${NC} ${RED}${details}${NC}"
    fi
}

section() {
    echo ""
    echo -e "${BLUE}${BOLD}--- $1 ---${NC}"
}

# ------------------------------------------------------------------------------
# 1. Verificação do PHP & CLI
# ------------------------------------------------------------------------------
section "1. Runtime PHP & CLI"

if command -v php >/dev/null 2>&1; then
    PHP_BIN=$(which php)
    PHP_VER=$(php -r 'echo PHP_VERSION;')
    PHP_MAJOR_MINOR=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
    
    # Compara se versão >= 8.1
    IS_GE_81=$(php -r 'echo version_compare(PHP_VERSION, "8.1.0", ">=") ? 1 : 0;')
    
    if [ "$IS_GE_81" -eq 1 ]; then
        check_status 0 "PHP instalado e compatível" "(Versão: v${PHP_VER} em ${PHP_BIN})"
    else
        check_status 2 "Versão do PHP incompatível" "(Encontrado: v${PHP_VER} - Mínimo exigido: PHP >= 8.1)"
    fi
else
    check_status 2 "PHP CLI não encontrado" "(Instale via: sudo apt install php php-cli)"
fi

# ------------------------------------------------------------------------------
# 2. Extensões Obrigatórias do PHP
# ------------------------------------------------------------------------------
section "2. Extensões do PHP"

if command -v php >/dev/null 2>&1; then
    REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "mbstring" "gd" "xml" "curl" "zip" "openssl" "fileinfo" "json")
    RECOMMENDED_EXTENSIONS=("intl" "bcmath" "redis")

    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if php -m | grep -qi "^${ext}$"; then
            check_status 0 "Extensão [${ext}]" "(Carregada)"
        else
            check_status 2 "Extensão [${ext}] ausente" "(Obrigatória - Instale: php-${ext})"
        fi
    done

    for ext in "${RECOMMENDED_EXTENSIONS[@]}"; do
        if php -m | grep -qi "^${ext}$"; then
            check_status 0 "Extensão recomendada [${ext}]" "(Carregada)"
        else
            check_status 1 "Extensão recomendada [${ext}] ausente" "(Opcional/Recomendado)"
        fi
    done
else
    echo -e " ${YELLOW}Ignorando checagem de extensões pois o PHP CLI não está disponível.${NC}"
fi

# ------------------------------------------------------------------------------
# 3. Gerenciador Composer & Dependências
# ------------------------------------------------------------------------------
section "3. Composer & Autoload"

if command -v composer >/dev/null 2>&1; then
    COMPOSER_VER=$(composer --version 2>/dev/null | awk '{print $3}' | head -n 1)
    check_status 0 "Composer CLI instalado" "(Versão: ${COMPOSER_VER})"
else
    check_status 2 "Composer não encontrado no PATH" "(Instale o Composer 2.x globalmente)"
fi

if [ -f "${BACKEND_DIR}/vendor/autoload.php" ]; then
    check_status 0 "Dependências de Backend (vendor/)" "(Autoload presente)"
else
    check_status 2 "Dependências ausentes (backend/vendor/)" "(Execute: cd backend && composer install)"
fi

# ------------------------------------------------------------------------------
# 4. Servidor de Banco de Dados (MariaDB / MySQL)
# ------------------------------------------------------------------------------
section "4. Banco de Dados (MariaDB / MySQL)"

DB_CLIENT_FOUND=0
if command -v mariadb >/dev/null 2>&1; then
    DB_CLIENT_FOUND=1
    DB_VER=$(mariadb --version 2>/dev/null | head -n 1)
    check_status 0 "Cliente MariaDB instalado" "(${DB_VER})"
elif command -v mysql >/dev/null 2>&1; then
    DB_CLIENT_FOUND=1
    DB_VER=$(mysql --version 2>/dev/null | head -n 1)
    check_status 0 "Cliente MySQL instalado" "(${DB_VER})"
else
    check_status 1 "Cliente de linha de comando MariaDB/MySQL não encontrado" "(Opcional, mas útil para testes)"
fi

# Checa se o serviço local está em execução (em sistemas baseados em systemd)
if command -v systemctl >/dev/null 2>&1; then
    if systemctl is-active --quiet mariadb 2>/dev/null || systemctl is-active --quiet mysql 2>/dev/null; then
        check_status 0 "Serviço de Banco de Dados Local" "(Ativo e em execução)"
    else
        check_status 1 "Serviço local MySQL/MariaDB não detectado ativo" "(Certifique-se de que o host remoto ou local esteja acessível no Setup)"
    fi
fi

# ------------------------------------------------------------------------------
# 5. Permissões de Arquivos e Diretórios
# ------------------------------------------------------------------------------
section "5. Permissões de Escrita (Storage & Configuração)"

# Permissão no diretório backend (para gravação do .env inicial)
if [ -w "${BACKEND_DIR}" ]; then
    check_status 0 "Diretório Backend (criação do .env)" "(Gravável: ${BACKEND_DIR})"
else
    check_status 2 "Diretório Backend sem permissão de escrita" "(Execute: chmod 775 ${BACKEND_DIR})"
fi

# Permissão no arquivo .env se existir
if [ -f "${BACKEND_DIR}/.env" ]; then
    if [ -w "${BACKEND_DIR}/.env" ]; then
        check_status 0 "Arquivo de Configuração (.env)" "(Gravável)"
    else
        check_status 2 "Arquivo .env existente sem permissão de escrita" "(Execute: chmod 664 ${BACKEND_DIR}/.env)"
    fi
else
    check_status 0 "Arquivo .env pronto para criação inicial" "(Será gerado pelo Setup Wizard)"
fi

# Permissões do diretório storage e subpastas
STORAGE_SUBDIRS=("cache" "cache/twig_slim" "cache/twig_setup" "cache/alpha_proxies" "download" "logs" "session" "upload")

if [ ! -d "${STORAGE_DIR}" ]; then
    mkdir -p "${STORAGE_DIR}" 2>/dev/null || true
fi

if [ -w "${STORAGE_DIR}" ]; then
    check_status 0 "Diretório Storage base" "(Gravável: storage/)"
else
    check_status 2 "Diretório Storage sem permissão de escrita" "(Execute: chmod -R 775 ${STORAGE_DIR})"
fi

ALL_SUBDIRS_OK=1
for sub in "${STORAGE_SUBDIRS[@]}"; do
    TARGET_PATH="${STORAGE_DIR}/${sub}"
    if [ ! -d "${TARGET_PATH}" ]; then
        mkdir -p "${TARGET_PATH}" 2>/dev/null || true
    fi
    if [ ! -w "${TARGET_PATH}" ]; then
        ALL_SUBDIRS_OK=0
    fi
done

if [ "$ALL_SUBDIRS_OK" -eq 1 ]; then
    check_status 0 "Subdiretórios do Storage (cache, logs, sessions, etc.)" "(Graváveis)"
else
    check_status 2 "Alguns subdiretórios de storage/ estão sem permissão" "(Execute: chmod -R 775 ${STORAGE_DIR})"
fi

# ------------------------------------------------------------------------------
# Resumo Final e Ações Recomendadas
# ------------------------------------------------------------------------------
echo ""
echo -e "${CYAN}${BOLD}========================================================================${NC}"
echo -e "${BOLD}                        RESUMO DA VERIFICAÇÃO                          ${NC}"
echo -e "${CYAN}${BOLD}========================================================================${NC}"
echo -e " Total de Checagens: ${BOLD}${TOTAL_CHECKS}${NC}"
echo -e " ${GREEN}✔ Passaram:${NC}         ${BOLD}${PASSED_CHECKS}${NC}"
echo -e " ${YELLOW}▲ Avisos:${NC}           ${BOLD}${WARNING_CHECKS}${NC}"
echo -e " ${RED}✖ Falhas:${NC}           ${BOLD}${FAILED_CHECKS}${NC}"
echo ""

if [ "$FAILED_CHECKS" -eq 0 ]; then
    echo -e "${GREEN}${BOLD}🎉 SUCESSO! Todos os requisitos obrigatórios foram atendidos.${NC}"
    echo -e "👉 Você já pode iniciar seu servidor web e acessar ${BOLD}http://localhost/setup${NC} para concluir a instalação."
    echo ""
    exit 0
else
    echo -e "${RED}${BOLD}⚠️  ATENÇÃO: Existem requisitos obrigatórios pendentes de resolução.${NC}"
    echo -e "Corrija os itens marcados com [ ${RED}✖ ERRO${NC} ] antes de acessar o Setup Wizard."
    echo ""
    echo -e "${BOLD}Comandos sugeridos para Debian/Ubuntu:${NC}"
    echo -e "  sudo apt update"
    echo -e "  sudo apt install php php-cli php-fpm php-mysql php-mbstring php-gd php-xml php-curl php-zip mariadb-server"
    echo -e "  cd ${BACKEND_DIR} && composer install"
    echo -e "  sudo chown -R \$USER:www-data ${ROOT_DIR}"
    echo -e "  chmod -R 775 ${STORAGE_DIR}"
    echo ""
    exit 1
fi
