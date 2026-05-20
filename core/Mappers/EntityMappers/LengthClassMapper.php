<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;
use Alpha\Model\Domain\Entities\LengthClass;
use Alpha\Model\Domain\Entities\LengthClassDescription;

/**
 * LengthClassMapper - Gerenciador de persistência para as unidades de medida de comprimento.
 *
 * Alpha Engine:
 * - Padroniza o acesso e hidratação de LengthClass e suas descrições.
 * - Garante a precisão decimal para os valores de conversão.
 */
class LengthClassMapper extends BaseMapper
{
    protected string $tableName = 'length_class';

    /**
     * Busca todas as classes de comprimento para um idioma específico.
     */
    public function getAll(int $languageId): array
    {
        $sql = "SELECT lc.*, lcd.title, lcd.unit FROM " . DB_PREFIX . "length_class lc LEFT JOIN " . DB_PREFIX . "length_class_description lcd ON (lc.length_class_id = lcd.length_class_id) WHERE lcd.language_id = :language_id";
        $results = $this->dao->executeQuery($sql, ['language_id' => $languageId]);

        $entities = [];
        foreach ($results as $result) {
            $entities[] = $this->hydrate($result);
        }

        return $entities;
    }

    /**
     * Busca uma classe de comprimento pelo ID.
     */
    public function getById(int $id, int $languageId): ?LengthClass
    {
        $sql = "SELECT lc.*, lcd.title, lcd.unit FROM " . DB_PREFIX . "length_class lc LEFT JOIN " . DB_PREFIX . "length_class_description lcd ON (lc.length_class_id = lcd.length_class_id) WHERE lc.length_class_id = :length_class_id AND lcd.language_id = :language_id";
        $result = $this->dao->executeQuery($sql, ['length_class_id' => $id, 'language_id' => $languageId]);

        if (empty($result)) {
            return null;
        }

        return $this->hydrate($result[0]);
    }

    /**
     * Hidrata a entidade LengthClass e sua descrição.
     */
    protected function hydrate(array $data): LengthClass
    {
        $lengthClass = new LengthClass();
        $lengthClass->setId((int)$data['length_class_id'])
                    ->setValue((float)$data['value']);

        $description = new LengthClassDescription();
        $description->setLengthClassId((int)$data['length_class_id'])
                    ->setLanguageId((int)$data['language_id'])
                    ->setTitle($data['title'])
                    ->setUnit($data['unit']);

        $lengthClass->setDescriptions([$description]); // Para este método, apenas uma descrição é hidratada

        // Apontamento Técnico:
        // O DAO.php se encarregará de carregar todas as descrições via OneToMany
        // quando a entidade for carregada por um Repository com processAssociations.
        // Aqui, estamos apenas garantindo que a descrição principal esteja presente.

        return $lengthClass;
    }

    // Métodos para persistência (insert, update, delete) seriam adicionados aqui
    // se a funcionalidade de gerenciamento de LengthClass fosse necessária.
    // Por enquanto, focamos na leitura para o motor de cálculo.
}