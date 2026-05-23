<?php

namespace Alpha\Model\Domain\Repositories;

use Alpha\Mappers\EntityMappers\CountryMapper;
use Alpha\Model\Domain\InterfaceEntity;
use Alpha\Model\Domain\Entities\Country;

/**
 * CountryRepository - Autoridade de Domínio para Países.
 *
 * Centraliza o acesso aos dados de países, utilizando o CountryMapper para persistência.
 * A hidratação de traduções (CountryDescription) e estados (Zone) ocorre de 
 * forma 100% nativa e automática via DAO ORM, respeitando os Attributes #[OneToMany].
 */
class CountryRepository extends AbstractRepository implements BaseRepositoryInterface
{
    protected function getMapper(): CountryMapper
    {
        return $this->mapperFactory->get(CountryMapper::class);
    }

    /**
     * [DOMAIN] Busca a Entidade Rica do País pelo ID (com Zonas e Traduções hidratadas).
     *
     * @param int $country_id
     * @return Country|null
     */
    public function find(int $id): ?Country
    {
        $cacheKey = "country.entity.{$id}";

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $country = $this->getMapper()->getCountry($id);

        if ($country && $this->cache !== null) {
            // Tempo de vida longo (24h) pois países raramente mudam
            $this->cache->set($cacheKey, $country, 86400);
        }

        return $country;
    }

    /**
     * [DOMAIN] Lista todas as Entidades Ricas de países ativos.
     *
     * @return Country[]
     */
    public function findAll(): array
    {
        $cacheKey = "country.entity.all";

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $countries = $this->getMapper()->getCountries();

        if ($this->cache !== null) {
            $this->cache->set($cacheKey, $countries, 86400);
        }

        return $countries;
    }

    /**
     * [LEGACY DTO] Retorna um país formatado como Array Plano para views (checkout).
     *
     * @param int $country_id
     * @return array
     */
    public function getCountry(int $country_id): array
    {
        $country = $this->find($country_id);
        return $country ? $this->toLegacyDTO($country) : [];
    }

    /**
     * [LEGACY DTO] Retorna todos os países como Arrays Planos para views legadas.
     *
     * @return array
     */
    public function getCountries(): array
    {
        return array_map(fn($c) => $this->toLegacyDTO($c), $this->findAll());
    }

    /**
     * Converte a Entidade Country num DTO reconhecido pelo padrão OpenCart.
     * Extrai o nome traduzido nativamente e formata as chaves.
     */
    private function toLegacyDTO(Country $country): array
    {
        $name = method_exists($country, 'getName') ? $country->getName() : '';
        
        // Sobrescreve com o nome traduzido de CountryDescription, respeitando o Idioma atual da loja
        if (method_exists($country, 'getDescriptions') && !empty($country->getDescriptions())) {
            $langId = property_exists($this, 'registry') && $this->registry 
                        ? (int)$this->registry->get('config')->get('config_language_id') 
                        : null;

            foreach ($country->getDescriptions() as $desc) {
                if ($langId !== null && $desc->getLanguageId() === $langId) {
                    $name = $desc->getName();
                    break;
                }
                $name = $desc->getName(); // Fallback automático
            }
        }

        return [
            'country_id'        => $country->getId(),
            'name'              => $name,
            'iso_code_2'        => method_exists($country, 'getIsoCode2') ? $country->getIsoCode2() : '',
            'iso_code_3'        => method_exists($country, 'getIsoCode3') ? $country->getIsoCode3() : '',
            'address_format'    => method_exists($country, 'getAddressFormat') ? $country->getAddressFormat() : '',
            'postcode_required' => method_exists($country, 'getPostcodeRequired') ? (int)$country->getPostcodeRequired() : 0,
            'status'            => method_exists($country, 'getStatus') ? (int)$country->getStatus() : 0,
        ];
    }

    // BaseRepositoryInterface bindings remanescentes
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array { 
        return $this->getMapper()->findBy($criteria, $orderBy, $limit, $offset); 
    }
    public function findOneBy(array $criteria): ?InterfaceEntity { 
        return $this->getMapper()->findOneBy($criteria); 
    }
}