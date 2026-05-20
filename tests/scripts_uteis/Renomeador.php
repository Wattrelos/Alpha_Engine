<?php
$dir = __DIR__;
$arquivos = scandir($dir);

foreach ($arquivos as $arquivo) {
    // 1. Pular diretórios e o próprio script
    if (is_dir($arquivo) || $arquivo === basename(__FILE__)) continue;

    // 2. Remover o arquivo fantasma 'agsc_agscagsc*.php' se ele existir
    if (str_contains($arquivo, '*')) {
        unlink($arquivo);
        echo "Lixo removido: $arquivo\n";
        continue;
    }

    // 3. Processar apenas arquivos que terminam em .Php ou .php (case-insensitive)
    if (preg_match('/\.php$/i', $arquivo)) {
        
        // Remove prefixos agsc_ repetidos e limpa espaços/extensão antiga
        $nomeLimpo = str_ireplace(['agsc_', '.php'], '', $arquivo);
        
        // Converte snake_case para PascalCase (ex: address_format -> AddressFormat)
        $partes = explode('_', $nomeLimpo);
        $partesFormatadas = array_map('ucfirst', $partes);
        $novoNome = implode('', $partesFormatadas) . '.php'; // Força .php minúsculo

        if ($arquivo !== $novoNome) {
            if (rename($dir . '/' . $arquivo, $dir . '/' . $novoNome)) {
                echo "Corrigido: $arquivo -> $novoNome\n";
            }
        }
    }
}
