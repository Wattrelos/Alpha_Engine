<?php

namespace Alpha\Support;

/**
 * EvolutionGenerator - Gerador automático de Log de Evolução Alpha Engine.
 * 
 * Este script lê os commits do Git e extrai mensagens marcadas com [EVO]
 * para atualizar o arquivo README3.md.
 */
class EvolutionGenerator
{
    private string $targetFile;

    public function __construct(string $targetFile = 'README3.md')
    {
        $this->targetFile = $targetFile;
    }

    public function run(): void
    {
        // 1. Obtém o log do Git filtrando pelo padrão [EVO]
        // %s = assunto, %b = corpo do commit (detalhes técnicos)
        $delimiter = '||EVO_END||';
        $separator = '||EVO_SEP||';
        $cmd = sprintf('git log --pretty=format:"%%s%s%%b%s" --grep="\[EVO\]"', $separator, $delimiter);
        $output = shell_exec($cmd);

        if (empty($output)) {
            echo "Nenhuma nova evolução encontrada com o padrão [EVO].\n";
            return;
        }

        $records = array_filter(explode($delimiter, trim($output)));
        $content = file_get_contents($this->targetFile);

        // 2. Localiza o último índice numérico no arquivo
        preg_match_all('/(\d+)\.\s\*\*/', $content, $matches);
        $lastIndex = !empty($matches[1]) ? (int)max($matches[1]) : 0;

        $newEntries = "";
        foreach (array_reverse($records) as $record) {
            $parts = explode($separator, $record);
            $subject = trim($parts[0] ?? '');
            $body = trim($parts[1] ?? '');

            $description = trim(str_replace('[EVO]', '', $subject));
            
            // Verifica se o item já existe no arquivo para evitar duplicidade
            if (str_contains($content, $description)) {
                continue;
            }

            $lastIndex++;

            $bulletPoints = "";
            if (!empty($body)) {
                $lines = explode("\n", $body);
                foreach ($lines as $line) {
                    // Limpa a linha e remove marcadores de lista manuais para não duplicar o estilo
                    $line = trim($line, " \t\n\r\0\x0B*-");
                    if ($line !== '') {
                        $bulletPoints .= sprintf("    *   %s\n", $line);
                    }
                }
            } else {
                $bulletPoints = "    *   (Auto-gerado via Git Commit)\n";
            }

            $newEntries .= sprintf("\n%d. **%s**:\n%s", $lastIndex, $description, $bulletPoints);
        }

        if (empty($newEntries)) {
            echo "Todas as evoluções já estão documentadas.\n";
            return;
        }

        // 3. Insere antes do rodapé
        $separator = "---";
        $parts = explode($separator, $content);
        
        if (count($parts) >= 2) {
            $parts[count($parts) - 2] .= $newEntries;
            file_put_contents($this->targetFile, implode($separator, $parts));
            echo "README3.md atualizado com sucessso!\n";
        }
    }
}