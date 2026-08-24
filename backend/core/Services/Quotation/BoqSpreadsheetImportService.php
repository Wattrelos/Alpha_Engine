<?php

declare(strict_types=1);

namespace Alpha\Services\Quotation;

use Alpha\Model\Domain\Entities\Quotation\ProjectBoqItem;
use Alpha\Model\Domain\Repositories\ProjectBoqRepository;
use Alpha\Model\Domain\Repositories\ProductRepository;

/**
 * BoqSpreadsheetImportService - Importação e processamento em lote de planilhas de insumos (CSV, TSV, Planilhas).
 * Atende ao requisito funcional RF036 (Material Takeoff / BoQ em lote).
 */
class BoqSpreadsheetImportService
{
    public function __construct(
        private ProjectBoqRepository $boqRepository,
        private ?ProductRepository $productRepository = null
    ) {}

    /**
     * Processa um arquivo CSV/TSV e insere os itens no BoQ especificado.
     *
     * @param int $boqId
     * @param string $filePath Caminho do arquivo temporário no servidor
     * @return array{success: bool, imported_count: int, error_count: int, errors: array, total_value: float}
     */
    public function importCsv(int $boqId, string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [
                'success' => false,
                'imported_count' => 0,
                'error_count' => 1,
                'errors' => ['Arquivo de planilha não encontrado ou inacessível.'],
                'total_value' => 0.0
            ];
        }

        $rawContent = file_get_contents($filePath);
        if ($rawContent === false || trim($rawContent) === '') {
            return [
                'success' => false,
                'imported_count' => 0,
                'error_count' => 1,
                'errors' => ['O arquivo fornecido está vazio.'],
                'total_value' => 0.0
            ];
        }

        // Normalização de encoding para UTF-8 caso seja Windows ANSI / ISO-8859-1
        if (!mb_check_encoding($rawContent, 'UTF-8')) {
            $rawContent = mb_convert_encoding($rawContent, 'UTF-8', 'ISO-8859-1, Windows-1252');
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($rawContent));
        if (empty($lines)) {
            return [
                'success' => false,
                'imported_count' => 0,
                'error_count' => 1,
                'errors' => ['Nenhuma linha válida encontrada no arquivo.'],
                'total_value' => 0.0
            ];
        }

        // Detecta delimitador (, ; ou \t)
        $firstLine = $lines[0];
        $delimiters = [',', ';', "\t", '|'];
        $bestDelimiter = ',';
        $maxCols = 0;

        foreach ($delimiters as $d) {
            $colCount = count(str_getcsv($firstLine, $d, '"', '\\'));
            if ($colCount > $maxCols) {
                $maxCols = $colCount;
                $bestDelimiter = $d;
            }
        }

        $headerRow = str_getcsv(array_shift($lines), $bestDelimiter, '"', '\\');
        $headerMap = $this->mapHeaders($headerRow);

        $imported = 0;
        $errors = [];
        $lineNum = 1;

        foreach ($lines as $line) {
            $lineNum++;
            $line = trim($line);
            if (empty($line)) continue;

            $row = str_getcsv($line, $bestDelimiter, '"', '\\');
            if (empty($row) || count(array_filter($row)) === 0) continue;

            $name = $this->extractField($row, $headerMap, 'name');
            $unit = $this->extractField($row, $headerMap, 'unit') ?: 'un';
            $qtyStr = $this->extractField($row, $headerMap, 'quantity') ?: '1';
            $priceStr = $this->extractField($row, $headerMap, 'price') ?: '0';
            $sku = $this->extractField($row, $headerMap, 'sku');
            $notes = $this->extractField($row, $headerMap, 'notes') ?: '';

            $quantity = (float)str_replace(',', '.', preg_replace('/[^\d.,]/', '', $qtyStr));
            $unitPrice = (float)str_replace(',', '.', preg_replace('/[^\d.,]/', '', $priceStr));

            if (empty($name)) {
                $errors[] = "Linha {$lineNum}: Nome do item/material não especificado.";
                continue;
            }

            if ($quantity <= 0) {
                $quantity = 1.0;
            }

            $productId = null;
            if (!empty($sku) && is_numeric($sku)) {
                $productId = (int)$sku;
            }

            $item = new ProjectBoqItem();
            $item->setBoqId($boqId)
                ->setProductId($productId)
                ->setItemName(trim($name))
                ->setUnit(trim($unit))
                ->setQuantity($quantity)
                ->setUnitPrice($unitPrice)
                ->setTotalPrice(round($quantity * $unitPrice, 2))
                ->setNotes(trim($notes));

            $this->boqRepository->saveItem($item);
            $imported++;
        }

        $totalValue = $this->boqRepository->recalculateTotal($boqId);

        return [
            'success' => $imported > 0,
            'imported_count' => $imported,
            'error_count' => count($errors),
            'errors' => $errors,
            'total_value' => $totalValue
        ];
    }

    /**
     * Mapeia colunas do cabeçalho de forma tolerante a variações em PT-BR e EN.
     */
    private function mapHeaders(array $headers): array
    {
        $map = [
            'name' => null,
            'quantity' => null,
            'unit' => null,
            'price' => null,
            'sku' => null,
            'notes' => null
        ];

        foreach ($headers as $idx => $header) {
            $h = mb_strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $header)));

            if (in_array($h, ['nome', 'item', 'material', 'descricao', 'produto', 'name', 'description'])) {
                $map['name'] = $idx;
            } elseif (in_array($h, ['quantidade', 'qtd', 'quant', 'quantity', 'qty'])) {
                $map['quantity'] = $idx;
            } elseif (in_array($h, ['unidade', 'un', 'unit', 'medida', 'um'])) {
                $map['unit'] = $idx;
            } elseif (in_array($h, ['preco', 'precounitario', 'valor', 'valorunitario', 'price', 'unitprice', 'valortotal'])) {
                $map['price'] = $idx;
            } elseif (in_array($h, ['sku', 'codigo', 'cod', 'productid', 'id'])) {
                $map['sku'] = $idx;
            } elseif (in_array($h, ['obs', 'observacao', 'observacoes', 'notas', 'notes', 'aplicacao'])) {
                $map['notes'] = $idx;
            }
        }

        // Se cabeçalhos não foram reconhecidos, assume formato padrão sequencial: [Nome, Unidade, Quantidade, Preço, Notas]
        if ($map['name'] === null) {
            $map['name'] = 0;
            $map['unit'] = 1;
            $map['quantity'] = 2;
            $map['price'] = 3;
            $map['notes'] = 4;
        }

        return $map;
    }

    private function extractField(array $row, array $map, string $field): ?string
    {
        $idx = $map[$field] ?? null;
        if ($idx !== null && isset($row[$idx])) {
            return trim($row[$idx]);
        }
        return null;
    }
}
