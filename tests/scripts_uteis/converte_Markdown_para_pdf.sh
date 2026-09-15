#!/usr/bin/env bash
# -*- coding: utf-8 -*-
""":"
# ==============================================================================
# Script: converte_Markdown_para_pdf.sh
# Descrição: Converte arquivos Markdown (.md) em documentos PDF na pasta docs/
#            (mantendo o arquivo original .md e gerando uma cópia em PDF).
#
# Uso:
#   ./converte_Markdown_para_pdf.sh [OPÇÕES] [CAMINHO_OU_PADRÃO]
#
# Exemplos:
#   # 1. Converter todos os arquivos .md da pasta docs/ (recursivo):
#   ./converte_Markdown_para_pdf.sh
#
#   # 2. Converter apenas uma subpasta específica:
#   ./converte_Markdown_para_pdf.sh docs/business/use-cases/customer
#
#   # 3. Converter apenas arquivos específicos por padrão (ex: casos de uso de cliente):
#   ./converte_Markdown_para_pdf.sh "UC_CLI_*.md"
#
#   # 4. Converter um único arquivo:
#   ./converte_Markdown_para_pdf.sh docs/catalog_progress.md
#
#   # 5. Forçar a reconversão de todos (mesmo os que já têm PDF atualizado):
#   ./converte_Markdown_para_pdf.sh --force
#
#   # 6. Converter e também espelhar/copiar PDFs para o diretório de documentação externa:
#   ./converte_Markdown_para_pdf.sh --mirror
#
#   # 7. Limpar os arquivos .pdf gerados a partir de .md na pasta docs/:
#   ./converte_Markdown_para_pdf.sh --clean
# ==============================================================================

set -o pipefail

# Diretórios padrão
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
DIR_ORIGEM_DEFAULT="${PROJECT_ROOT}/docs"
DIR_DESTINO_EXTERNO="${HOME:-/home/wattrelos}/Documentos/AlphaEngine/docs"

# Cores para saída no terminal
C_RESET="\033[0m"
C_BOLD="\033[1m"
C_GREEN="\033[32m"
C_YELLOW="\033[33m"
C_BLUE="\033[34m"
C_CYAN="\033[36m"
C_RED="\033[31m"
C_GRAY="\033[90m"

# Configurações padrão
TARGET_PATH="$DIR_ORIGEM_DEFAULT"
FILE_PATTERN="*.md"
FORCE=false
MIRROR=false
CLEAN=false
VERBOSE=false

show_help() {
    cat << 'EOF'
Uso: ./converte_Markdown_para_pdf.sh [OPÇÕES] [CAMINHO_OU_PADRÃO]

Opções:
  -f, --force          Força a conversão mesmo se o PDF já existir e for mais recente
  -m, --mirror         Copia/espelha os PDFs gerados para ~/Documentos/AlphaEngine/docs
  -d, --dest DIR       Define um diretório de destino externo personalizado para espelhamento
  -p, --pattern PADRÃO Filtra arquivos por nome/padrão (ex: "UC_CLI_*.md")
  -c, --clean          Remove todos os PDFs gerados a partir de .md no diretório alvo
  -v, --verbose        Exibe avisos detalhados do motor WeasyPrint
  -h, --help           Exibe esta mensagem de ajuda

Exemplos:
  ./converte_Markdown_para_pdf.sh
  ./converte_Markdown_para_pdf.sh docs/business/use-cases/customer
  ./converte_Markdown_para_pdf.sh "UC_CLI_*.md"
  ./converte_Markdown_para_pdf.sh -f "UC_ADM_*.md"
  ./converte_Markdown_para_pdf.sh --clean
EOF
}

# Parsing de argumentos
while [[ $# -gt 0 ]]; do
    case "$1" in
        -h|--help)
            show_help
            exit 0
            ;;
        -f|--force)
            FORCE=true
            shift
            ;;
        -m|--mirror)
            MIRROR=true
            shift
            ;;
        -d|--dest)
            DIR_DESTINO_EXTERNO="$2"
            MIRROR=true
            shift 2
            ;;
        -p|--pattern)
            FILE_PATTERN="$2"
            shift 2
            ;;
        -c|--clean)
            CLEAN=true
            shift
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        *)
            # Se for um caminho de diretório ou arquivo existente
            if [[ -d "$1" ]]; then
                TARGET_PATH="$(cd "$1" && pwd)"
            elif [[ -f "$1" && "$1" == *.md ]]; then
                TARGET_PATH="$(cd "$(dirname "$1")" && pwd)/$(basename "$1")"
            elif [[ -d "${DIR_ORIGEM_DEFAULT}/$1" ]]; then
                TARGET_PATH="${DIR_ORIGEM_DEFAULT}/$1"
            elif [[ -f "${DIR_ORIGEM_DEFAULT}/$1" ]]; then
                TARGET_PATH="${DIR_ORIGEM_DEFAULT}/$1"
            elif [[ "$1" == *"*"* || "$1" == *.md ]]; then
                FILE_PATTERN="$1"
            else
                echo -e "${C_YELLOW}Aviso:${C_RESET} Argumento não é um caminho direto. Tratando como padrão de busca: $1"
                FILE_PATTERN="$1"
            fi
            shift
            ;;
    esac
done

# Verificação de dependências
check_dependencies() {
    local missing=0
    if ! command -v pandoc &> /dev/null; then
        echo -e "${C_RED}[ERRO]${C_RESET} Pandoc não está instalado."
        echo "       Instale com: sudo apt update && sudo apt install -y pandoc"
        missing=1
    fi
    if ! command -v weasyprint &> /dev/null; then
        echo -e "${C_RED}[ERRO]${C_RESET} WeasyPrint não está instalado."
        echo "       Instale com: sudo apt update && sudo apt install -y weasyprint"
        missing=1
    fi
    if [[ $missing -eq 1 ]]; then
        exit 1
    fi
}

# Função para extrair título amigável para os metadados do PDF
get_markdown_title() {
    local md_file="$1"
    local title
    title=$(grep -m 1 -E "^#[[:space:]]+" "$md_file" 2>/dev/null | sed -E "s/^#[[:space:]]+//" | tr -d '\r\n')
    if [[ -z "$title" ]]; then
        title="$(basename "$md_file" .md)"
    fi
    echo "$title"
}

# Modo de Limpeza (--clean)
if [[ "$CLEAN" == true ]]; then
    echo -e "${C_YELLOW}🧹 Iniciando limpeza de PDFs gerados a partir de .md em: ${TARGET_PATH}${C_RESET}"
    deleted=0
    if [[ -d "$TARGET_PATH" ]]; then
        while IFS= read -r -d '' md_file; do
            pdf_file="${md_file%.md}.pdf"
            if [[ -f "$pdf_file" ]]; then
                rm -f "$pdf_file"
                echo -e "  ${C_RED}[REMOVIDO]${C_RESET} ${pdf_file#"${PROJECT_ROOT}"/}"
                ((deleted++))
            fi
        done < <(find "$TARGET_PATH" -type f -name "$FILE_PATTERN" -print0 | sort -z)
    elif [[ -f "$TARGET_PATH" ]]; then
        pdf_file="${TARGET_PATH%.md}.pdf"
        if [[ -f "$pdf_file" ]]; then
            rm -f "$pdf_file"
            echo -e "  ${C_RED}[REMOVIDO]${C_RESET} ${pdf_file#"${PROJECT_ROOT}"/}"
            ((deleted++))
        fi
    fi
    echo -e "${C_GREEN}Concluído! ${deleted} arquivos PDF foram removidos.${C_RESET}"
    exit 0
fi

# Verifica ferramentas necessárias
check_dependencies

echo -e "${C_BOLD}${C_BLUE}====================================================${C_RESET}"
echo -e "${C_BOLD}${C_BLUE}  Conversor de Documentação Markdown para PDF       ${C_RESET}"
echo -e "${C_BOLD}${C_BLUE}====================================================${C_RESET}"
echo -e "${C_CYAN}Origem / Alvo :${C_RESET} ${TARGET_PATH#"${PROJECT_ROOT}"/}"
echo -e "${C_CYAN}Padrão        :${C_RESET} ${FILE_PATTERN}"
echo -e "${C_CYAN}Modo          :${C_RESET} $([[ "$FORCE" == true ]] && echo -e "${C_YELLOW}Forçar todos (--force)${C_RESET}" || echo "Apenas novos/modificados")"
if [[ "$MIRROR" == true ]]; then
    echo -e "${C_CYAN}Espelhar em   :${C_RESET} ${DIR_DESTINO_EXTERNO}"
fi
echo -e "----------------------------------------------------"

# Coleta de arquivos a converter
FILES=()
if [[ -f "$TARGET_PATH" ]]; then
    FILES=("$TARGET_PATH")
elif [[ -d "$TARGET_PATH" ]]; then
    while IFS= read -r -d '' f; do
        FILES+=("$f")
    done < <(find "$TARGET_PATH" -type f -name "$FILE_PATTERN" -print0 | sort -z)
fi

TOTAL=${#FILES[@]}
if [[ $TOTAL -eq 0 ]]; then
    echo -e "${C_YELLOW}Nenhum arquivo .md correspondente ao padrão em: ${TARGET_PATH}${C_RESET}"
    exit 0
fi

echo -e "Encontrado(s) ${C_BOLD}${TOTAL}${C_RESET} arquivo(s) Markdown para processamento.\n"

count_converted=0
count_skipped=0
count_failed=0
idx=0

ENGINE_OPTS=("-V" "papersize=a4" "-V" "margin-top=20mm" "-V" "margin-bottom=20mm" "-V" "margin-left=20mm" "-V" "margin-right=20mm")
if [[ "$VERBOSE" == false ]]; then
    ENGINE_OPTS+=("--pdf-engine-opt=-q")
fi

for file in "${FILES[@]}"; do
    ((idx++))
    pdf_file="${file%.md}.pdf"
    rel_path="${file#"${PROJECT_ROOT}"/}"
    rel_pdf="${pdf_file#"${PROJECT_ROOT}"/}"

    # Verifica se já está atualizado (incremental)
    if [[ "$FORCE" == false && -f "$pdf_file" && "$pdf_file" -nt "$file" ]]; then
        echo -e "[${idx}/${TOTAL}] ${C_GRAY}[PULADO]${C_RESET} ${rel_path} ${C_GRAY}(PDF já atualizado)${C_RESET}"
        ((count_skipped++))
        continue
    fi

    doc_title="$(get_markdown_title "$file")"
    echo -ne "[${idx}/${TOTAL}] ${C_YELLOW}[CONVERTENDO]${C_RESET} ${rel_path}..."

    # Executa a conversão via pandoc + weasyprint
    error_output=$(pandoc "$file" \
        --pdf-engine=weasyprint \
        "${ENGINE_OPTS[@]}" \
        --metadata title="$doc_title" \
        -o "$pdf_file" 2>&1)
    status=$?

    if [[ $status -eq 0 ]]; then
        echo -e "\r[${idx}/${TOTAL}] ${C_GREEN}[SUCESSO]${C_RESET} ${rel_path} -> ${C_BOLD}$(basename "$pdf_file")${C_RESET}"
        ((count_converted++))

        # Espelhamento opcional
        if [[ "$MIRROR" == true ]]; then
            if [[ "$file" == "${PROJECT_ROOT}/docs/"* ]]; then
                rel_from_docs="${pdf_file#"${PROJECT_ROOT}/docs/"}"
                dest_file="${DIR_DESTINO_EXTERNO}/${rel_from_docs}"
                mkdir -p "$(dirname "$dest_file")"
                cp -p "$pdf_file" "$dest_file"
            fi
        fi
    else
        echo -e "\r[${idx}/${TOTAL}] ${C_RED}[ERRO]${C_RESET} Falha ao converter: ${rel_path}"
        if [[ -n "$error_output" ]]; then
            echo -e "${C_RED}       ${error_output}${C_RESET}"
        fi
        ((count_failed++))
    fi
done

echo -e "\n----------------------------------------------------"
echo -e "${C_BOLD}Resumo da Conversão:${C_RESET}"
echo -e "  - Total de arquivos analisados : ${TOTAL}"
echo -e "  - ${C_GREEN}Convertidos com sucesso       : ${count_converted}${C_RESET}"
echo -e "  - ${C_GRAY}Ignorados (já atualizados)   : ${count_skipped}${C_RESET}"
if [[ $count_failed -gt 0 ]]; then
    echo -e "  - ${C_RED}Falhas na conversão           : ${count_failed}${C_RESET}"
fi
echo -e "===================================================="

if [[ "$MIRROR" == true && $count_converted -gt 0 ]]; then
    echo -e "${C_GREEN}Arquivos PDF espelhados em: ${DIR_DESTINO_EXTERNO}${C_RESET}"
fi

exit 0
"""
# Fallback para execução direta com python3 (ex: python3 script.sh)
import os, sys
os.execv("/usr/bin/bash", ["bash", __file__] + sys.argv[1:])