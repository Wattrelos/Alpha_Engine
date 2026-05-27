#!/bin/bash
# Script inteligente para refatorar injeções de linguagem no padrão Alpha Engine
# Ele preserva endpoints JSON (save, confirm, etc.) e atualiza apenas os métodos de View.

WORKSPACE_DIR="/var/www/html/agsonhos"
TARGET_DIR="$WORKSPACE_DIR/catalog/controller/account"

echo "Iniciando varredura em: $TARGET_DIR"

find "$TARGET_DIR" -type f -name "*.php" | while read -r file; do
    # Usa Perl inplace (-i) para processar o arquivo memorizando o escopo do método
    perl -i.bak -ne '
        # Se a linha define uma função, captura o nome dela
        if (/public\s+function\s+([a-zA-Z0-9_]+)/) {
            $current_method = $1;
        }
        
        # Se a linha contiver o carregamento legado de linguagem
        if (/^(\s*)\$this->load->language\((['\''"])(.*?)\2\);/) {
            my $indent = $1; # Captura espaços/tabs de indentação originais
            my $quote = $2;  # Captura aspas simples ou duplas
            my $path = $3;   # Captura o caminho (ex: account/address)
            
            # Verifica se é um método seguro (que renderiza View)
            if ($current_method =~ /^(index|add|edit|info|form|list|history|reset|password)$/) {
                print "${indent}\$data = [];\n${indent}\$this->loadLanguageData(${quote}${path}${quote}, \$data);\n";
                next; # Pula para a próxima linha (não imprime a original)
            }
        }
        print $_; # Imprime a linha original se não cair nas regras acima
    ' "$file"
    
    # Compara com o backup. Se houve mudança, avisa no terminal.
    if ! cmp -s "$file" "${file}.bak"; then
        echo "[Refatorado] Alpha Engine aplicada em: $file"
    fi
    rm "${file}.bak"
done

echo "Processo concluído! Verifique as mudanças com 'git diff'."