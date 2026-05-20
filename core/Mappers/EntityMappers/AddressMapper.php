<?php

namespace Alpha\Mappers\EntityMappers;

use Alpha\Mappers\BaseMapper;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;
use Alpha\Model\Domain\Entities\Address;
use Opencart\System\Library\DB; // Importa a classe DB do OpenCart
use Alpha\Model\Domain\Entities\AddressFormat;

/**
 * AddressMapper - Centraliza a gestão de endereços e integração com formatos regionais.
 * 
 * Melhoras Alpha Engine:
 * - Integração com AddressFormat: Resolve layouts de endereços dinamicamente via Entidade e DAO.
 * - Normalização Geográfica: Consolida nomes de países e zonas em uma única consulta otimizada.
 * - Higienização de Output: Formata o endereço respeitando quebras de linha e padrões postais regionais.
 */
class AddressMapper extends BaseMapper
{
    protected string $entityClass = Address::class;
    protected string $tableName = 'address';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Recupera os dados brutos de um endereço, incluindo metadados geográficos e o formato postall.
     */
    public function getAddress(int $addressId, int $languageId = 2): array // Só temos a linguagem id=2 no banco de dados (a liguagem padrão id=1 foi remofida)
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'address', 'a')
            ->leftJoin(DB_PREFIX . 'country', 'c', 'a.country_id = c.id')
            ->leftJoin(DB_PREFIX . 'country_description', 'cd', 'c.id = cd.country_id')
            ->leftJoin(DB_PREFIX . 'zone', 'z', 'a.zone_id = z.id')
            ->leftJoin(DB_PREFIX . 'zone_description', 'zd', 'z.id = zd.zone_id')
            ->where('a.id = ?', [$addressId])
            ->where('cd.language_id = ?', [$languageId])
            ->where('zd.language_id = ?', [$languageId])
            ->select('a.*', 'cd.name AS country', 'zd.name AS zone', 'z.code AS zone_code', 'c.address_format_id');

        $results = $this->dao->executeQuery($query);
        if (!$results) {
            return [];
        }

        $addressData = $results[0];

        // Alpha Engine: Integração com a Entidade AddressFormat através do DAO (aproveita o Identity Map)
        $formatId = (int)($addressData['address_format_id'] ?? 0);
        $addressData['address_format'] = '';

        if ($formatId > 0) {
            $formatEntity = new AddressFormat();
            $formatEntity->setId($formatId);
            $hydrated = $this->dao->read($formatEntity);
            
            if ($hydrated) {
                $addressData['address_format'] = $hydrated[0]->getAddressFormat();
            }
        }

        return $addressData;
    }

    /**
     * Alpha Engine: Recupera todos os endereços de um cliente.
     * 
     * @param int $customerId
     * @param int $languageId
     * @return array
     */
    public function getAddresses(int $customerId, int $languageId = 1): array
    {
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'address', 'a')
            ->leftJoin(DB_PREFIX . 'country', 'c', 'a.country_id = c.id')
            ->leftJoin(DB_PREFIX . 'country_description', 'cd', 'c.id = cd.country_id')
            ->leftJoin(DB_PREFIX . 'zone', 'z', 'a.zone_id = z.id')
            ->leftJoin(DB_PREFIX . 'zone_description', 'zd', 'z.id = zd.zone_id')
            ->where('a.customer_id = ?', [$customerId])
            ->where('cd.language_id = ?', [$languageId])
            ->where('zd.language_id = ?', [$languageId])
            ->select('a.*', 'cd.name AS country', 'zd.name AS zone', 'z.code AS zone_code', 'c.address_format_id');

        $results = $this->dao->executeQuery($query);

        return $results ?: [];
    }

    /**
     * Alpha Engine: Salva um novo endereço e gerencia a vinculação de endereço padrão.
     * 
     * @param array $data
     * @param int $customerId
     * @return int
     */
    public function createAddress(array $data, int $customerId): int
    {
        $conn = \Alpha\Model\DataAccessObject\ConnectionDB::getInstance()->getConnection();
        if (!$conn instanceof \PDO) {
            throw new \RuntimeException("Erro Alpha Engine: PDO não disponível para criação de endereço.");
        }

        $sql = "INSERT INTO `" . DB_PREFIX . "address` SET 
            `customer_id` = ?, 
            `firstname` = ?, 
            `lastname` = ?, 
            `company` = ?, 
            `address_1` = ?, 
            `number` = ?,
            `address_2` = ?, 
            `neighborhood` = ?,
            `city` = ?, 
            `postcode` = ?, 
            `country_id` = ?, 
            `zone_id` = ?, 
            `custom_field` = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            (int)$customerId, $data['firstname'], $data['lastname'], $data['company'],
            $data['address_1'], $data['number'] ?? '', $data['address_2'], $data['neighborhood'] ?? '',
            $data['city'], $data['postcode'], (int)$data['country_id'], (int)$data['zone_id'],
            json_encode($data['custom_field'] ?? [])
        ]);

        $addressId = (int)$conn->lastInsertId();

        // Alpha Engine: Atualiza o endereço padrão na tabela customer (PK 'id')
        if (!empty($data['default'])) {
            $stmt_default = $conn->prepare("UPDATE `" . DB_PREFIX . "customer` SET `address_id` = ? WHERE `id` = ?");
            $stmt_default->execute([$addressId, (int)$customerId]);
        }

        return $addressId;
    }

    /**
     * Gera a string final do endereço formatada conforme as regras do país.
     */
    public function format(array $data): string
    {
        // Fallback para o padrão internacional se o formato não estiver definido
        $format = !empty($data['address_format']) 
            ? $data['address_format'] 
            : "{firstname} {lastname}\n{company}\n{address_1}\n{address_2}\n{city} {postcode}\n{zone}\n{country}";

        $find = [
            '{firstname}', '{lastname}', '{company}', '{address_1}', '{address_2}',
            '{city}', '{postcode}', '{zone}', '{zone_code}', '{country}'
        ];

        $replace = [
            'firstname' => $data['firstname'] ?? '',
            'lastname'  => $data['lastname'] ?? '',
            'company'   => $data['company'] ?? '',
            'address_1' => $data['address_1'] ?? '',
            'address_2' => $data['address_2'] ?? '',
            'city'      => $data['city'] ?? '',
            'postcode'  => $data['postcode'] ?? '',
            'zone'      => $data['zone'] ?? '',
            'zone_code' => $data['zone_code'] ?? '',
            'country'   => $data['country'] ?? ''
        ];

        $output = str_replace($find, $replace, $format);
        
        // Higienização: remove espaços triplos e quebras de linha órfãs, converte para HTML
        $output = preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], [" ", "\n", "\n"], trim($output));
        
        return str_replace("\n", '<br />', $output);
    }
}