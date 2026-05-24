<?php

namespace Alpha\Model\Domain\Repositories;

/**
 * ReturnDictionaryRepository
 * Um repositório "Facade" para facilitar a busca dos dicionários auxiliares
 * (Ações, Motivos e Status) diretamente pelo idioma do cliente.
 */
class ReturnDictionaryRepository extends AbstractRepository
{
    // Como este Repository é um agregador, não declaramos um mapper principal estrito.
    // Usaremos as fábricas de mappers instanciadas dinamicamente se necessário.

    /**
     * Obtém todas as ações de devolução (Troca, Estorno, etc) para um dado idioma.
     *
     * @param int $languageId
     * @return array
     */
    public function getActionsByLanguage(int $languageId): array
    {
        $mapper = $this->factory->getMapper('ReturnAction');
        return $mapper->search(
            ['languageId' => $languageId],
            ['name' => 'ASC']
        );
    }

    /**
     * Obtém todos os motivos de devolução (Defeito, Arrependimento) para um dado idioma.
     *
     * @param int $languageId
     * @return array
     */
    public function getReasonsByLanguage(int $languageId): array
    {
        $mapper = $this->factory->getMapper('ReturnReason');
        return $mapper->search(
            ['languageId' => $languageId],
            ['name' => 'ASC']
        );
    }

    /**
     * Obtém todos os status de devolução disponíveis para um dado idioma.
     *
     * @param int $languageId
     * @return array
     */
    public function getStatusesByLanguage(int $languageId): array
    {
        // O status reside diretamente na entidade ReturnStatus que contém languageId
        $mapper = $this->factory->getMapper('ReturnStatus');
        return $mapper->search(
            ['languageId' => $languageId],
            ['name' => 'ASC']
        );
    }
}