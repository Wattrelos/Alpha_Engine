# Este script gera os documentos finais em PDF para a documentação do projeto para a faculdade
DIR_ORIGEM="/var/www/html/agsonhos/docs"
DIR_DESTINO="/home/kiruma/Documentos/AlphaEngine/docs"

echo "Limpando e gerando SVGs..."
plantuml -tsvg "$DIR_ORIGEM/**.puml"

echo "Convertendo para PDF..."
find "$DIR_ORIGEM" -type f -name "*.svg" -exec sh -c 'rsvg-convert -f pdf "$1" -o "${1%.svg}.pdf"' _ {} \;

echo "Organizando pastas..."
mkdir -p "$DIR_DESTINO"
rsync -av --remove-source-files --include="*/" --include="*.svg" --include="*.pdf" --exclude="*" "$DIR_ORIGEM/" "$DIR_DESTINO/"

# Remove pastas vazias que restarem na origem
find "$DIR_ORIGEM" -type d -empty -delete
echo "Documentação atualizada!"
