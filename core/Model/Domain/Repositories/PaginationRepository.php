<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Model\Domain\DTOs\PaginationDataDTO;
use Alpha\Model\Domain\InterfaceEntity;

/**
 * PaginationRepository - Abstrai a lógica de cálculo de páginas do OpenCart.
 */
class PaginationRepository extends AbstractRepository
{
    public function prepare(array $setting): PaginationDataDTO
    {
        $total = (int)($setting['total'] ?? 0);
        $page  = (int)($setting['page'] ?? 1);
        $limit = (int)($setting['limit'] ?? 10);
        $url   = str_replace('%7Bpage%7D', '{page}', (string)($setting['url'] ?? ''));

        $num_links = 8;
        $num_pages = ceil($total / $limit);

        $data = [
            'page'  => $page,
            'back'  => ($url && $page > 1 && $num_pages < $page),
            'links' => []
        ];

        if ($page > 1) {
            $data['first'] = str_replace(['&amp;page={page}', '?page={page}', '&page={page}'], '', $url);
            
            if ($page - 1 === 1) {
                $data['prev'] = str_replace(['&amp;page={page}', '?page={page}', '&page={page}'], '', $url);
            } else {
                $data['prev'] = str_replace('{page}', $page - 1, $url);
            }
        } else {
            $data['first'] = '';
            $data['prev'] = '';
        }

        if ($num_pages > 1) {
            if ($num_pages <= $num_links) {
                $start = 1;
                $end   = $num_pages;
            } else {
                $start = $page - floor($num_links / 2);
                $end   = $page + floor($num_links / 2);

                if ($start < 1) {
                    $end   += abs($start) + 1;
                    $start = 1;
                }

                if ($end > $num_pages) {
                    $start -= ($end - $num_pages);
                    $end   = $num_pages;
                }
            }

            for ($i = $start; $i <= $end; $i++) {
                $data['links'][] = [
                    'page' => $i,
                    'href' => str_replace('{page}', $i, $url)
                ];
            }
        }

        if ($num_pages > $page) {
            $data['next'] = str_replace('{page}', $page + 1, $url);
            $data['last'] = str_replace('{page}', $num_pages, $url);
        } else {
            $data['next'] = '';
            $data['last'] = '';
        }

        return new PaginationDataDTO($data, $num_pages > 1);
    }

    public function find(int $id): ?InterfaceEntity { return null; }
    public function findAll(): array { return []; }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { return []; }
    public function findOneBy(array $criteria): ?InterfaceEntity { return null; }
}