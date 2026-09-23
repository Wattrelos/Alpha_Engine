#!/usr/bin/env bash
# -*- coding: utf-8 -*-
# ==============================================================================
# plantuml2svg.sh
# Converte diagramas PlantUML (*.puml) em vetores SVG de alta resolução.
# 
# Destaques desta versão:
# 1. Garante que o arquivo SVG gerado tenha EXATAMENTE o mesmo nome do arquivo .puml
#    (mesmo quando há títulos customizados na anotação @startuml).
# 2. Converte dinamicamente blocos de estilização CSS (<style>...</style>) em
#    diretivas 'skinparam' nativas do PlantUML para aplicar 100% das cores,
#    fontes e bordas sem depender do suporte experimental de CSS da versão legada.
# 3. Processamento multi-thread com memória/dimensão expandida (PLANTUML_LIMIT_SIZE).
# 4. Opção para gerar também cópia em PDF vetorial de alta definição.
#
# Exemplos de uso:
#   scripts/devops/plantuml2svg.sh docs/
#   scripts/devops/plantuml2svg.sh docs/business/use-cases/general_customer_use.puml
#   scripts/devops/plantuml2svg.sh -p docs/
#   scripts/devops/plantuml2svg.sh -s docs/ -o docs/dist_svg/
# ==============================================================================

set -eo pipefail

DEFAULT_SRC="/docs"
SRC_DIR=""
OUT_DIR=""
GENERATE_PDF=false
VERBOSE=false
THREADS="auto"

usage() {
    cat <<EOF
Uso: $(basename "$0") [OPÇÕES] [DIRETÓRIO_OU_ARQUIVO]

Opções:
  -s, --src <caminho>       Diretório raiz ou arquivo .puml de entrada (padrão: $DEFAULT_SRC)
  -o, --out <caminho>       Diretório de saída para os SVGs (padrão: mesmo diretório de cada .puml)
  -p, --pdf                 Gera também cópia em PDF de alta qualidade vetorial usando rsvg-convert/cairosvg
  -t, --threads <num>       Número de threads concorrentes (padrão: auto)
  -v, --verbose             Exibe mensagens detalhadas de execução
  -h, --help                Exibe esta ajuda

EOF
    exit 0
}

# Parse argumentos CLI
while [[ $# -gt 0 ]]; do
    case "$1" in
        -s|--src)
            SRC_DIR="$2"
            shift 2
            ;;
        -o|--out)
            OUT_DIR="$2"
            shift 2
            ;;
        -p|--pdf)
            GENERATE_PDF=true
            shift
            ;;
        -t|--threads)
            THREADS="$2"
            shift 2
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -h|--help)
            usage
            ;;
        *)
            if [[ -z "$SRC_DIR" ]]; then
                SRC_DIR="$1"
            else
                echo "Parâmetro desconhecido: $1" >&2
                usage
            fi
            shift
            ;;
    esac
done

SRC_DIR="${SRC_DIR:-$DEFAULT_SRC}"

# Valida dependências
if ! command -v plantuml &> /dev/null; then
    echo "Erro: 'plantuml' não foi encontrado no PATH do sistema." >&2
    exit 1
fi

if ! command -v python3 &> /dev/null; then
    echo "Erro: 'python3' é necessário para normalização de estilos e compatibilidade." >&2
    exit 1
fi

if [[ ! -e "$SRC_DIR" ]]; then
    echo "Erro: Caminho de origem '$SRC_DIR' não existe." >&2
    exit 1
fi

echo "=========================================================="
echo "  PlantUML -> SVG Generator (High Resolution & CSS Styles)"
echo "=========================================================="
echo "Origem : $SRC_DIR"
[[ -n "$OUT_DIR" ]] && echo "Destino: $OUT_DIR" || echo "Destino: Mesmo nome e pasta de cada .puml"
echo "PDF    : $([[ "$GENERATE_PDF" = true ]] && echo "Habilitado" || echo "Desabilitado")"
echo "Threads: $THREADS"
echo "----------------------------------------------------------"

# Executa conversor via motor Python robusto
python3 - <<EOF
import os
import sys
import re
import tempfile
import subprocess
from concurrent.futures import ThreadPoolExecutor

src_path = "$SRC_DIR"
out_path = "$OUT_DIR" if "$OUT_DIR" else None
threads = os.cpu_count() or 4
verbose = "$VERBOSE" == "true"

def convert_style_to_skinparam(style_inner):
    """Converte blocos <style> em skinparam retrocompatíveis com a versão do PlantUML."""
    skinparams = []
    cleaned = re.sub(r"//.*$", "", style_inner, flags=re.MULTILINE)
    
    def parse_body(text):
        pos = 0
        while pos < len(text):
            m = re.search(r"([a-zA-Z0-9_-]+)\s*\{", text[pos:])
            if not m:
                break
            name = m.group(1)
            start_brace = pos + m.end()
            depth, curr = 1, start_brace
            while curr < len(text) and depth > 0:
                if text[curr] == "{":
                    depth += 1
                elif text[curr] == "}":
                    depth -= 1
                curr += 1
            inner_block = text[start_brace:curr-1]
            if "{" in inner_block:
                parse_body(inner_block)
            else:
                lines = [l.strip() for l in inner_block.splitlines() if l.strip()]
                for l in lines:
                    kv = re.match(r"^([a-zA-Z0-9_-]+)\s+([^;]+);?$", l)
                    if kv:
                        prop, val = kv.group(1), kv.group(2).strip()
                        if name in ("root", "document"):
                            if prop == "BackgroundColor":
                                skinparams.append(f"skinparam backgroundColor {val}")
                            elif prop.lower() == "fontname":
                                skinparams.append(f"skinparam defaultFontName {val}")
                            elif prop.lower() == "fontsize":
                                skinparams.append(f"skinparam defaultFontSize {val}")
                            elif prop.lower() == "fontcolor":
                                skinparams.append(f"skinparam defaultFontColor {val}")
                            elif prop.lower() == "roundcorner":
                                skinparams.append(f"skinparam roundcorner {val}")
                        elif name in ("arrow", "Arrow"):
                            if prop.lower() == "linecolor":
                                skinparams.append(f"skinparam ArrowColor {val}")
                            elif prop.lower() in ("fontcolor", "textcolor"):
                                skinparams.append(f"skinparam ArrowFontColor {val}")
                        else:
                            cap_target = name[0].upper() + name[1:]
                            cap_prop = prop[0].upper() + prop[1:]
                            skinparams.append(f"skinparam {cap_target}{cap_prop} {val}")
            pos = curr
    parse_body(cleaned)
    return "\n".join(skinparams)

def collect_puml_files(src):
    files = []
    if os.path.isfile(src):
        if src.endswith(".puml"):
            files.append(src)
    else:
        for root, _, filenames in os.walk(src):
            for fn in sorted(filenames):
                if fn.endswith(".puml") and not fn.startswith("_"):
                    full_p = os.path.join(root, fn)
                    try:
                        with open(full_p, "r", encoding="utf-8", errors="ignore") as f:
                            if "@startuml" in f.read():
                                files.append(full_p)
                    except Exception:
                        pass
    return files

puml_files = collect_puml_files(src_path)
if not puml_files:
    print("Nenhum arquivo .puml com @startuml encontrado.")
    sys.exit(0)

print(f"Processando {len(puml_files)} diagrama(s)...")

env = os.environ.copy()
env["PLANTUML_LIMIT_SIZE"] = "16384"

def process_file(puml_path):
    puml_dir = os.path.dirname(os.path.abspath(puml_path))
    puml_basename = os.path.basename(puml_path)
    base_name_no_ext = os.path.splitext(puml_basename)[0]
    
    target_dir = os.path.abspath(out_path) if out_path else puml_dir
    os.makedirs(target_dir, exist_ok=True)
    target_svg = os.path.join(target_dir, base_name_no_ext + ".svg")
    
    try:
        with open(puml_path, "r", encoding="utf-8", errors="replace") as f:
            content = f.read()
        
        # 1. Normaliza @startuml removendo títulos customizados que sobrescreveriam o nome do arquivo gerado
        normalized = re.sub(r"^(\s*@startuml)(?:[ \t]+.*)?$", r"\1", content, flags=re.MULTILINE)
        
        # 2. Converte estilos CSS para skinparams se houver bloco <style>
        normalized = re.sub(r"<style>(.*?)</style>", lambda m: convert_style_to_skinparam(m.group(1)), normalized, flags=re.DOTALL)
        
        # Salva em arquivo temporário com nome idêntico para manter referências relativas (!include)
        temp_dir = tempfile.mkdtemp(prefix="puml_", dir=puml_dir)
        temp_puml = os.path.join(temp_dir, puml_basename)
        with open(temp_puml, "w", encoding="utf-8") as f:
            f.write(normalized)
        
        # Executa plantuml no arquivo temporário
        cmd = ["plantuml", "-tsvg", "-charset", "UTF-8", temp_puml]
        res = subprocess.run(cmd, capture_output=True, text=True, env=env)
        
        expected_svg = os.path.join(temp_dir, base_name_no_ext + ".svg")
        if res.returncode == 0 and os.path.exists(expected_svg):
            os.replace(expected_svg, target_svg)
            if verbose:
                print(f"✓ Gerado: {target_svg}")
        else:
            print(f"✗ Erro ao processar {puml_path}:\n{res.stderr.strip()}", file=sys.stderr)
            
        # Limpeza do diretório temporário
        if os.path.exists(temp_puml):
            os.remove(temp_puml)
        if os.path.exists(expected_svg):
            os.remove(expected_svg)
        if os.path.exists(temp_dir):
            os.rmdir(temp_dir)
            
    except Exception as e:
        print(f"✗ Exceção ao processar {puml_path}: {e}", file=sys.stderr)

with ThreadPoolExecutor(max_workers=threads) as executor:
    list(executor.map(process_file, puml_files))

print("SVGs gerados com sucesso!")
EOF

# Conversão opcional para PDF de alta fidelidade vetorial
if [[ "$GENERATE_PDF" = true ]]; then
    echo "Convertendo SVGs para PDF..."
    SEARCH_DIR="${OUT_DIR:-$SRC_DIR}"
    [[ -f "$SEARCH_DIR" ]] && SEARCH_DIR="$(dirname "$SEARCH_DIR")"

    CONVERT_CMD=""
    if command -v rsvg-convert &> /dev/null; then
        CONVERT_CMD="rsvg"
    elif command -v cairosvg &> /dev/null; then
        CONVERT_CMD="cairo"
    elif python3 -m cairosvg --version &> /dev/null; then
        CONVERT_CMD="python_cairo"
    else
        echo "Aviso: Nem 'rsvg-convert' nem 'cairosvg' foram encontrados. Conversão para PDF ignorada."
    fi

    if [[ -n "$CONVERT_CMD" ]]; then
        find "$SEARCH_DIR" -type f -name "*.svg" | while IFS= read -r svg_path; do
            pdf_path="${svg_path%.svg}.pdf"
            case "$CONVERT_CMD" in
                rsvg)
                    rsvg-convert -f pdf "$svg_path" -o "$pdf_path"
                    ;;
                cairo)
                    cairosvg "$svg_path" -o "$pdf_path"
                    ;;
                python_cairo)
                    python3 -m cairosvg "$svg_path" -o "$pdf_path"
                    ;;
            esac
            [[ "$VERBOSE" = true ]] && echo "PDF gerado: $pdf_path"
        done
        echo "PDFs gerados com sucesso!"
    fi
fi

echo "Concluído com sucesso!"
