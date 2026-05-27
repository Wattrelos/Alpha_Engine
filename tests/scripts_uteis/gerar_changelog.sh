#!/bin/bash
# Script para consolidar notas técnicas (nota_YYYYMMDD_*.md) em um único CHANGELOG e limpá-las da raiz.

WORKSPACE="/var/www/html/agsonhos"
DOCS_DIR="$WORKSPACE/docs/anotações"
DATA_ATUAL=$(date +"%Y%m%d")
CHANGELOG="$DOCS_DIR/CHANGELOG_${DATA_ATUAL}.md"

# Garante que a pasta de destino exista
mkdir -p "$DOCS_DIR"

# Ativa nullglob para evitar falhas se não existirem arquivos
shopt -s nullglob
NOTAS=("$WORKSPACE"/nota_[0-9]*.md)

if [ ${#NOTAS[@]} -eq 0 ]; then
    echo "⚠️ Nenhuma nota técnica isolada encontrada para consolidar na raiz do projeto."
    exit 0
fi

echo "# Registro de Modificações IA - Data: $(date +"%d/%m/%Y")" >> "$CHANGELOG"
echo "" >> "$CHANGELOG"

for nota in "${NOTAS[@]}"; do
    echo "Consolidando: $(basename "$nota")..."
    cat "$nota" >> "$CHANGELOG"
    echo -e "\n---\n" >> "$CHANGELOG"
    rm "$nota"
done

echo "✅ Concluído! Foram consolidadas ${#NOTAS[@]} nota(s) em: $CHANGELOG"