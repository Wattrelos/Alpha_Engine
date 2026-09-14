#!/usr/bin/env bash
# -*- coding: utf-8 -*-
""":"
# Este script gera os documentos finais em PDF para a documentação do projeto para a faculdade
set -e

DIR_ORIGEM="/var/www/html/agsonhos/docs"
DIR_DESTINO="${HOME:-/home/wattrelos}/Documentos/AlphaEngine/docs"

echo "Limpando e gerando SVGs..."
plantuml -tsvg "$DIR_ORIGEM/**.puml" || true

echo "Convertendo para PDF..."
if command -v cairosvg &> /dev/null; then
    find "$DIR_ORIGEM" -type f -name "*.svg" | while IFS= read -r file; do
        cairosvg "$file" -o "${file%.svg}.pdf"
    done
elif command -v rsvg-convert &> /dev/null; then
    find "$DIR_ORIGEM" -type f -name "*.svg" | while IFS= read -r file; do
        rsvg-convert -f pdf "$file" -o "${file%.svg}.pdf"
    done
elif python3 -m cairosvg --version &> /dev/null; then
    find "$DIR_ORIGEM" -type f -name "*.svg" | while IFS= read -r file; do
        python3 -m cairosvg "$file" -o "${file%.svg}.pdf"
    done
else
    echo "Aviso: Nem 'cairosvg' nem 'rsvg-convert' foram encontrados para converter SVG em PDF."
fi

echo "Organizando pastas..."
mkdir -p "$DIR_DESTINO"
rsync -av --remove-source-files --include="*/" --include="*.svg" --include="*.pdf" --exclude="*" "$DIR_ORIGEM/" "$DIR_DESTINO/"

# Remove pastas vazias que restarem na origem
find "$DIR_ORIGEM" -type d -empty -delete
echo "Documentação atualizada com sucesso!"

exit 0
"""
# Fallback para execução direta com python3 (ex: python3 script.sh)
import os, sys
os.execv("/usr/bin/bash", ["bash", __file__] + sys.argv[1:])
