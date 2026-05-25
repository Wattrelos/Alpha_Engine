#!/bin/bash
# Script para encontrar e renomear models não utilizados no OpenCart

WORKSPACE_DIR="/var/www/html/agsonhos"
cd "$WORKSPACE_DIR" || exit

echo "Iniciando varredura na pasta catalog/model/..."

find catalog/model -type f -name "*.php" | while read -r file; do
    # Extrai o nome de chamada do model (ex: de catalog/model/account/activity.php para account/activity)
    model_name=$(echo "$file" | sed 's|catalog/model/||' | sed 's|\.php||')
    
    # Busca por ocorrências do nome do model na base de código (ignorando o próprio model atual para evitar falsos positivos)
    # A busca procura por referências de carregamento como 'account/activity'
    occurrences=$(grep -rn "$model_name" . --exclude="$(basename "$file")" 2>/dev/null | wc -l)
    
    if [ "$occurrences" -eq 0 ]; then
        echo "[Inativo] Nenhuma referência externa encontrada para: $model_name"
        mv "$file" "${file%.php}.old"
        echo "  -> Arquivo renomeado para: ${file%.php}.old"
    fi
done

echo "Varredura concluída com sucesso!"