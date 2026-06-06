<?php

namespace Alpha;

/**
 * AlphaContainer - O container de resolução da Alpha Engine.
 *
 * Resolve modelos diretamente para Repositórios ou Mappers Alpha,
 * sem nenhuma dependência do motor legadodo código legado.
 */
class AlphaContainer
{

/* Cógigo para debug. Usado com frequência (não apague)-------------------------------------------------------------
    private function getTraceLogger(): \Alpha\Library\Log
    {
        static $traceLogger;
        if (!$traceLogger) {
            $traceLogger = new \Alpha\Library\Log('alpha_trace.log');
        }
        return $traceLogger;
    }
// -----------------------------------------------------------------------------------------------------------------
*/

    /**
     * Resolve um modelo para o Repositório ou Mapper Alpha correspondente.
     *
     * @param string $route Caminho do modelo (ex: 'catalog/product')
     * @return object
     * @throws \InvalidArgumentException Se a rota não tiver mapeamento Alpha.
     */
    public function model(string $route): object
    {
        // $this->getTraceLogger()->write("[Alpha TRACE] Solicitado Model: '{$route}'");

        $sanitized_route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);

        // --- Repositórios ---
        $repositoriesMap = [
            'account/customer'            => \Alpha\Model\Domain\Repositories\CustomerRepository::class,
            'account/customer_group'      => \Alpha\Model\Domain\Repositories\CustomerGroupRepository::class,
            'account/affiliate'           => \Alpha\Model\Domain\Repositories\CustomerAffiliateRepository::class,
            'account/transaction'         => \Alpha\Model\Domain\Repositories\CustomerTransactionRepository::class,
            'account/download'            => \Alpha\Model\Domain\Repositories\DownloadRepository::class,
            'account/returns'             => \Alpha\Model\Domain\Repositories\OrderReturnRepository::class,
            'account/reward'              => \Alpha\Model\Domain\Repositories\CustomerRewardRepository::class,
            'account/order'               => \Alpha\Model\Domain\Repositories\OrderRepository::class,
            'account/orders'              => \Alpha\Model\Domain\Repositories\OrderRepository::class,
            'account/subscription'        => \Alpha\Model\Domain\Repositories\SubscriptionRepository::class,
            'account/wishlist'            => \Alpha\Model\Domain\Repositories\WishlistRepository::class,
            'catalog/category'            => \Alpha\Model\Domain\Repositories\CategoryRepository::class,
            'catalog/product'             => \Alpha\Model\Domain\Repositories\ProductRepository::class,
            'catalog/manufacturer'        => \Alpha\Model\Domain\Repositories\ManufacturerRepository::class,
            'catalog/information'         => \Alpha\Model\Domain\Repositories\InformationRepository::class,
            'design/banner'               => \Alpha\Model\Domain\Repositories\BannerRepository::class,
            'design/theme'                => \Alpha\Model\Domain\Repositories\ThemeRepository::class,
            'design/translation'          => \Alpha\Model\Domain\Repositories\TranslationRepository::class,
            'design/seo_url'              => \Alpha\Model\Domain\Repositories\SeoUrlRepository::class,
            'localisation/language'       => \Alpha\Model\Domain\Repositories\LanguageRepository::class,
            'localisation/zone'           => \Alpha\Model\Domain\Repositories\ZoneRepository::class,
            'localisation/address_format' => \Alpha\Model\Domain\Repositories\AddressFormatRepository::class,
            'localisation/weight_class'   => \Alpha\Model\Domain\Repositories\WeightClassRepository::class,
            'localisation/length_class'   => \Alpha\Model\Domain\Repositories\LengthClassRepository::class,
            'localisation/tax_class'      => \Alpha\Model\Domain\Repositories\TaxClassRepository::class,
            'localisation/tax_rate'       => \Alpha\Model\Domain\Repositories\TaxRateRepository::class,
            'localisation/tax_rule'       => \Alpha\Model\Domain\Repositories\TaxRuleRepository::class,
            'setting/setting'             => \Alpha\Model\Domain\Repositories\SettingRepository::class,
            'setting/extension'           => \Alpha\Model\Domain\Repositories\ExtensionRepository::class,
            'setting/store'               => \Alpha\Model\Domain\Repositories\StoreRepository::class,
        ];

        if (isset($repositoriesMap[$sanitized_route])) {
            return \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()
                ->get($repositoriesMap[$sanitized_route]);
        }

        // --- Mappers ---
        $mappersMap = [
            'setting/module'        => \Alpha\Mappers\EntityMappers\ModuleMapper::class,
            'localisation/currency' => \Alpha\Mappers\EntityMappers\CurrencyMapper::class,
            'catalog/review'        => \Alpha\Mappers\EntityMappers\ReviewMapper::class,
        ];

        if (isset($mappersMap[$sanitized_route])) {
            return \Alpha\Mappers\MapperFactory::getInstance()
                ->get($mappersMap[$sanitized_route]);
        }

        throw new \InvalidArgumentException(
            "[AlphaContainer] Rota sem mapeamento Alpha: '{$route}'. Adicione ao repositoriesMap ou mappersMap."
        );
    }
}
