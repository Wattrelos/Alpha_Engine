#!/bin/bash
# Script para criar a estrutura base da nota técnica com a data correta do sistema
# Uso: ./criar_nota_gemini.sh "Resumo da Tarefa"

DATA_ATUAL=$(date +"%Y%m%d_%H%M%S")
ARQUIVO="/var/www/html/agsonhos/nota_${DATA_ATUAL}.md"
RESUMO=${1:-"Nome da Tarefa"}

echo "Criando nota técnica para o Gemini preencher..."

echo "# Nova Implementação: $RESUMO" > "$ARQUIVO"
echo "" >> "$ARQUIVO"

echo "✅ Arquivo gerado em: $ARQUIVO"
echo "Agora você pode pedir para o Gemini: 'Preencha o arquivo nota_${DATA_ATUAL}.md com a documentação do que acabamos de fazer.'"