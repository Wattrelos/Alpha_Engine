<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Cart;

/**
 * CartMapper - Gerencia a persistência de itens do carrinho.
 * 
 * Melhoras Alpha Engine:
 * - Persistência Segura: Utiliza QueryBuilder e DAO para operações atômicas.
 * - Busca por Critérios: Implementa findBy para localizar itens por sessão ou cliente.
 * - Tipagem Estrita: Manipulação de entidades Cart hidratadas.
 */
class CartMapper extends BaseMapper
{
    protected string $entityClass = Cart::class;
    protected string $tableName = 'cart';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Recupera um item do carrinho pelo ID.
     */
    public function findById(int $id): ?Cart
    {
        $cart = new Cart();
        $cart->setId($id);
        
        $results = $this->dao->read($cart);
        return $results ? $results[0] : null;
    }

    /**
     * Localiza itens do carrinho com base em critérios (ex: customerId, sessionId).
     * 
     * @param array $criteria
     * @return Cart[]
     */
    public function findBy(array $criteria): array
    {
        $query = (new QueryBuilder())->from($this->getFullTableName());
        
        foreach ($criteria as $key => $value) {
            // Alpha Engine: Converte camelCase para snake_case para alinhar com o banco
            $column = $this->dao->convertPascalCaseToSnakeCase($key);
            $query->where("{$column} = ?", [$value]);
        }

        $results = $this->dao->executeQuery($query->select('id'));
        $ids = array_map('intval', array_column($results, 'id'));

        return !empty($ids) ? $this->dao->readByIds($this->entityClass, $ids) : [];
    }

    /**
     * Salva ou atualiza um item no carrinho.
     */
    public function save(Cart $cart): ?int
    {
        return ($cart->getId() > 0) ? $this->dao->update($cart) : $this->dao->create($cart);
    }

    /**
     * Remove um item do carrinho.
     */
    public function delete(Cart $cart): void
    {
        if ($cart->getId() > 0) {
            $this->dao->delete($cart);
        }
    }
}