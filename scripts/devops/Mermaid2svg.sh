#!/usr/bin/env bash
# ==============================================================================
# Script: Mermaid2svg.sh
# Uso: ./scripts/devops/Mermaid2svg.sh <arquivo.mmd|arquivo.mm> [arquivo_saida.svg]
# ==============================================================================

set -euo pipefail

INPUT_FILE="${1:-}"
OUTPUT_FILE="${2:-}"

# 1. Validação de parâmetros
if [ -z "$INPUT_FILE" ]; then
    echo "❌ Erro: Forneça o arquivo Mermaid como argumento."
    echo "Uso: $0 <caminho/para/diagrama.mmd> [saida.svg]"
    exit 1
fi

if [ ! -f "$INPUT_FILE" ]; then
    echo "❌ Erro: Arquivo '$INPUT_FILE' não encontrado."
    exit 1
fi

# 2. Definir nome de saída caso não especificado
if [ -z "$OUTPUT_FILE" ]; then
    OUTPUT_FILE="${INPUT_FILE%.*}.svg"
fi

echo "🔄 Convertendo '$INPUT_FILE' para '$OUTPUT_FILE'..."

# 3. Estratégia 1: Se o CLI local (mmdc) estiver disponível, utiliza-o
if command -v mmdc &> /dev/null; then
    echo "ℹ️  Usando Mermaid CLI local (mmdc)..."
    mmdc -i "$INPUT_FILE" -o "$OUTPUT_FILE"
    echo "✅ Sucesso! Imagem gerada em: $OUTPUT_FILE"
    exit 0
fi

# 4. Estratégia 2: Fallback autônomo via Python 3 (sem precisar de sudo ou npm)
# Codifica com compactação zlib/deflate (pako) para suportar diagramas grandes
echo "ℹ️  Mermaid CLI local não detectado. Usando renderizador autônomo (Python/Mermaid API)..."

python3 - "$INPUT_FILE" "$OUTPUT_FILE" << 'EOF'
import sys
import json
import zlib
import base64
import urllib.request
import urllib.error

input_file = sys.argv[1]
output_file = sys.argv[2]

try:
    with open(input_file, 'r', encoding='utf-8') as f:
        diagram_code = f.read().strip()
except Exception as e:
    sys.exit(f"❌ Erro ao ler arquivo de entrada: {e}")

# Remove marcações de bloco ```mermaid caso existam
if diagram_code.startswith("```mermaid"):
    diagram_code = diagram_code[len("```mermaid"):].strip()
if diagram_code.endswith("```"):
    diagram_code = diagram_code[:-3].strip()

# Payload no formato esperado pelo Mermaid Live Editor / mermaid.ink
payload = {
    "code": diagram_code,
    "mermaid": {"theme": "default"}
}

json_bytes = json.dumps(payload).encode('utf-8')

# 1ª Tentativa: Mermaid.ink com compactação pako (zlib deflate)
try:
    # zlib deflate compatível com pako.deflate
    compressor = zlib.compressobj(level=9, wbits=15, memLevel=8, strategy=zlib.Z_DEFAULT_STRATEGY)
    deflated = compressor.compress(json_bytes) + compressor.flush()
    encoded_pako = base64.urlsafe_b64encode(deflated).decode('ascii').rstrip('=')
    url = f"https://mermaid.ink/svg/pako:{encoded_pako}"
    
    req = urllib.request.Request(
        url,
        headers={"User-Agent": "Mozilla/5.0 (Mermaid2svg CLI)"}
    )
    with urllib.request.urlopen(req, timeout=20) as resp:
        content = resp.read()
        if b"<svg" in content:
            with open(output_file, 'wb') as out:
                out.write(content)
            print(f"✅ Sucesso! Imagem gerada em: {output_file}")
            sys.exit(0)
except Exception as pako_err:
    pass

# 2ª Tentativa: Kroki API (suporta diagramas muito complexos via POST direto)
try:
    kroki_url = "https://kroki.io/mermaid/svg"
    req = urllib.request.Request(
        kroki_url,
        data=diagram_code.encode('utf-8'),
        headers={
            "Content-Type": "text/plain; charset=utf-8",
            "User-Agent": "Mozilla/5.0 (Mermaid2svg CLI)"
        },
        method="POST"
    )
    with urllib.request.urlopen(req, timeout=25) as resp:
        content = resp.read()
        if b"<svg" in content:
            with open(output_file, 'wb') as out:
                out.write(content)
            print(f"✅ Sucesso via Kroki! Imagem gerada em: {output_file}")
            sys.exit(0)
except Exception as kroki_err:
    sys.exit(f"❌ Falha na conversão: {kroki_err}")

sys.exit("❌ Não foi possível obter uma resposta SVG válida dos renderizadores.")
EOF

chmod 664 "$OUTPUT_FILE" 2>/dev/null || true
