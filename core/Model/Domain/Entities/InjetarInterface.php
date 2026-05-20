<?php
namespace Alpha\Model\Domain\Entities;
$dir = __DIR__;
$arquivos = glob("*.<?<?php
namespace Alpha\Model\Domain\Entities;");
foreach ($arquivos as $arquivo) {
    // Ignora este script e arquivos que não são classes (opcional)
    if ($arquivo === basename(__FILE__)) continue;
    // 1. Pega o nome do arquivo sem a extensão .<?<?php
    $nomeClasse = pathinfo($arquivo, PATHINFO_FILENAME);
    
    // 2. Lê o conteúdo do arquivo
    $conteudo = file_get_contents($arquivo);
    // 3. Define o padrão de busca (pega 'class NomeDaClasse' ou 'class Nome_Antigo')
    // Vamos usar uma regex flexível para garantir que ele ache a declaração
    $padrao = "/class\s+" . preg_quote($nomeClasse) . "/";
    $substituicao = "class " . $nomeClasse . " extends BaseEntity";
    // 4. Verifica se já não implementa a interface para não duplicar
    if (str_contains($conteudo, "extends BaseEntity")) {
        echo "Pular: $arquivo (já implementa)\n";
        continue;
    }
    // 5. Faz o replace e salva
    if (preg_match($padrao, $conteudo)) {
        $novoConteudo = preg_replace($padrao, $substituicao, $conteudo);
        file_put_contents($arquivo, $novoConteudo);
        echo "Sucesso: $arquivo -> implementado\n";
    } else {
        echo "Aviso: Nome da classe dentro de $arquivo não coincide com o nome do arquivo.\n";
    }
}
