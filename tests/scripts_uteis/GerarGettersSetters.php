<?php

// Script gerar_getters_setters.php
$dir = __DIR__;
$arquivos = glob("*.php");

foreach ($arquivos as $arquivo) {
    if ($arquivo === basename(__FILE__)) continue;

    $conteudo = file_get_contents($arquivo);
    
    // 1. Captura todas as propriedades privadas: private $nomeAtributo;
    preg_match_all('/private\s+\$([a-zA-Z0-9_]+)\s*;/', $conteudo, $matches);
    $atributos = $matches[1];

    if (empty($atributos)) continue;

    $novosMetodos = "";
    foreach ($atributos as $atributo) {
        $metodoNome = ucfirst($atributo);
        
        // Gerar Getter se não existir
        if (!str_contains($conteudo, "function get$metodoNome")) {
            $novosMetodos .= "\n    public function get$metodoNome()\n    {\n        return \$this->$atributo;\n    }\n";
        }

        // Gerar Setter se não existir
        if (!str_contains($conteudo, "function set$metodoNome")) {
            $novosMetodos .= "\n    public function set$metodoNome(\$value)\n    {\n        \$this->$atributo = \$value;\n        return \$this;\n    }\n";
        }
    }

    // 2. Injetar os métodos antes do último fechamento de chave '}' da classe
    if (!empty($novosMetodos)) {
        // Encontra a última chave de fechamento do arquivo
        $posicaoUltimaChave = strrpos($conteudo, '}');
        if ($posicaoUltimaChave !== false) {
            $novoConteudo = substr_replace($conteudo, $novosMetodos . "}\n", $posicaoUltimaChave);
            file_put_contents($arquivo, $novoConteudo);
            echo "✅ Métodos gerados para: $arquivo\n";
        }
    }
}
