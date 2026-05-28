<?php

namespace Alpha\Services\Menu;

use PDO;

/**
 * CategoryMenuService
 *
 * Serviço standalone para o contexto do Slim (sem OpenCart Registry).
 * Conecta ao banco diretamente via PDO e retorna a árvore de categorias
 * pronta para o Twig renderizar com dropdown-recursive.twig.
 *
 * Substitui o acoplamento de CategoryRepository ao OpenCart para o novo
 * pipeline de requisição: Slim → HomeAction → Twig.
 */
class CategoryMenuService
{
    private PDO $pdo;
    private int $languageId;
    private int $storeId;
    private string $prefix;

    public function __construct(
        PDO    $pdo,
        int    $languageId = 2,    // 2 = Português (pt-br) — default do AgSonhos
        int    $storeId    = 0,    // 0 = loja padrão do OpenCart
        string $prefix     = 'tbkk_'
    ) {
        $this->pdo        = $pdo;
        $this->languageId = $languageId;
        $this->storeId    = $storeId;
        $this->prefix     = $prefix;
    }

    /**
     * Retorna a árvore aninhada de categorias ativas para o Twig.
     *
     * Formato de cada nó (compatível com dropdown-recursive.twig):
     *   ['id' => int, 'name' => string, 'url' => string, 'children' => array]
     *
     * Estratégia: 1 única query (Batch Load) + montagem da árvore em PHP O(N).
     * Elimina o problema N+1 de queries que o menu legado sofria.
     */
    public function getCategoryTree(): array
    {
        $p = $this->prefix;

        $sql = "
            SELECT
                c.id,
                c.parent_id,
                cd.name,
                IFNULL(su.keyword, '') AS seo_keyword
            FROM {$p}category c
            INNER JOIN {$p}category_description cd
                ON c.id = cd.category_id
                AND cd.language_id = :lang_id
            INNER JOIN {$p}category_to_store cs
                ON c.id = cs.category_id
                AND cs.store_id = :store_id
            LEFT JOIN {$p}seo_url su
                ON su.`key` = 'category_id'
                AND su.value = c.id
                AND su.store_id = :store_id2
                AND su.language_id = :lang_id2
            WHERE c.status = 1
            ORDER BY c.sort_order ASC, cd.name ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':lang_id'  => $this->languageId,
            ':store_id' => $this->storeId,
            ':store_id2'=> $this->storeId,
            ':lang_id2' => $this->languageId,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ── 1. Indexa todas as categorias pelo id (com referência para poder aninhar)
        $index = [];
        foreach ($rows as $row) {
            $index[$row['id']] = [
                'id'       => (int)$row['id'],
                'name'     => $row['name'],
                // SEO URL se disponível, senão URL padrão do OpenCart para não quebrar os links
                'url'      => $row['seo_keyword']
                    ? '/' . ltrim($row['seo_keyword'], '/')
                    : '/index.php?route=product/category&language=pt-br&path=' . $row['id'],
                'children' => [],
            ];
        }

        // ── 2. Monta a árvore aninhada em PHP (nenhuma query adicional)
        $tree = [];
        foreach ($rows as $row) {
            $id       = (int)$row['id'];
            $parentId = (int)$row['parent_id'];

            if ($parentId === 0) {
                // Categoria raiz → entra direto na árvore
                $tree[] = &$index[$id];
            } elseif (isset($index[$parentId])) {
                // Subcategoria → aninha no pai
                $index[$parentId]['children'][] = &$index[$id];
            }
        }

        return $tree;
    }
}
