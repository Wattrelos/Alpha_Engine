<?php

namespace Alpha;

use Opencart\System\Engine\Factory;
use Opencart\System\Engine\Registry;
use Opencart\System\Library\Log;

/**
 * AlphaContainer - O interceptor inteligente da Alpha Engine.
 * 
 * Estende a Factory nativa do OpenCart para permitir a substituição
 * progressiva de modelos legados por Repositórios ou Mappers Alpha.
 */
class AlphaContainer extends Factory
{
    /**
     * Retorna a instância do logger de rastreamento (Trace).
     */
    private function getTraceLogger(): Log
    {
        static $traceLogger;
        if (!$traceLogger) {
            $traceLogger = new Log('alpha_trace.log');
        }
        return $traceLogger;
    }

    /**
     * Intercepta o carregamento de modelos.
     * 
     * @param string $route Caminho do modelo (ex: 'catalog/product')
     * @return object|\Exception
     */
    public function model(string $route): object
    {
        // Rastreamento de Fluxo (Trace)
        $this->getTraceLogger()->write("[Alpha TRACE] Solicitado Model: '{$route}'");

        // 1. Normaliza a rota para identificar o componente
        $sanitized_route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);
        
        // 2. Mapeamento de "Shortcuts": Se a rota do modelo coincidir com algo migrado
        // podemos retornar o Repositório diretamente do Registry.
        $repository_factory = $this->registry->get('alpha_repository_factory');

        if ($repository_factory) {
            $repositoriesMap = [
                'account/customer'          => \Alpha\Model\Domain\Repositories\CustomerRepository::class,
                'account/customer_group'    => \Alpha\Model\Domain\Repositories\CustomerGroupRepository::class,
                'account/affiliate'         => \Alpha\Model\Domain\Repositories\CustomerAffiliateRepository::class,
                'account/transaction'       => \Alpha\Model\Domain\Repositories\CustomerTransactionRepository::class,
                'account/download'          => \Alpha\Model\Domain\Repositories\DownloadRepository::class,
                'account/returns'           => \Alpha\Model\Domain\Repositories\OrderReturnRepository::class,
                'account/reward'            => \Alpha\Model\Domain\Repositories\CustomerRewardRepository::class,
                'account/order'             => \Alpha\Model\Domain\Repositories\OrderRepository::class,
                'account/returns'           => \Alpha\Model\Domain\Repositories\OrderReturnRepository::class,
                'account/reward'            => \Alpha\Model\Domain\Repositories\CustomerRewardRepository::class,
                'account/order'             => \Alpha\Model\Domain\Repositories\OrderRepository::class,
                'account/subscription'      => \Alpha\Model\Domain\Repositories\SubscriptionRepository::class,
                'account/wishlist'          => \Alpha\Model\Domain\Repositories\WishlistRepository::class,
                'catalog/category'          => \Alpha\Model\Domain\Repositories\CategoryRepository::class,
                'catalog/product'           => \Alpha\Model\Domain\Repositories\ProductRepository::class,

                'account/wishlist'          => \Alpha\Model\Domain\Repositories\WishlistRepository::class,
                'catalog/category'          => \Alpha\Model\Domain\Repositories\CategoryRepository::class,
                'catalog/product'           => \Alpha\Model\Domain\Repositories\ProductRepository::class,
                'catalog/manufacturer'      => \Alpha\Model\Domain\Repositories\ManufacturerRepository::class,
                'catalog/information'       => \Alpha\Model\Domain\Repositories\InformationRepository::class,
                'design/banner'             => \Alpha\Model\Domain\Repositories\BannerRepository::class,
                'design/theme'              => \Alpha\Model\Domain\Repositories\ThemeRepository::class,
                'design/translation'        => \Alpha\Model\Domain\Repositories\TranslationRepository::class,
                'design/seo_url'            => \Alpha\Model\Domain\Repositories\SeoUrlRepository::class,
                'localisation/language'     => \Alpha\Model\Domain\Repositories\LanguageRepository::class,
                'localisation/country'      => \Alpha\Model\Domain\Repositories\CountryRepository::class,
                'localisation/zone'         => \Alpha\Model\Domain\Repositories\ZoneRepository::class,
                'localisation/weight_class' => \Alpha\Model\Domain\Repositories\WeightClassRepository::class,
                'localisation/length_class' => \Alpha\Model\Domain\Repositories\LengthClassRepository::class,
                'localisation/tax_class'    => \Alpha\Model\Domain\Repositories\TaxClassRepository::class,
                'localisation/tax_rate'     => \Alpha\Model\Domain\Repositories\TaxRateRepository::class,
                'localisation/tax_rule'     => \Alpha\Model\Domain\Repositories\TaxRuleRepository::class,
                'setting/setting'           => \Alpha\Model\Domain\Repositories\SettingRepository::class,
                'setting/extension'         => \Alpha\Model\Domain\Repositories\ExtensionRepository::class,
            ];

            if (isset($repositoriesMap[$sanitized_route])) {
                return $repository_factory->get($repositoriesMap[$sanitized_route]);
            }
        }

        $mapper_factory = $this->registry->get('alpha_mapper_factory');

        if ($mapper_factory) {
            $mappersMap = [
                'setting/module'        => \Alpha\Mappers\EntityMappers\ModuleMapper::class,
                'localisation/currency' => \Alpha\Mappers\EntityMappers\CurrencyMapper::class,
                'catalog/review'        => \Alpha\Mappers\EntityMappers\ReviewMapper::class,
            ];

            if (isset($mappersMap[$sanitized_route])) {
                return $mapper_factory->get($mappersMap[$sanitized_route]);
            }
        }

        // 3. Rastreamento de Débito Técnico: Logs de Depreciação
        // Captura chamadas que ainda dependem do motor de carregamento legado (Loader/Factory nativa).
        /** @var Log|null $log */
        $log = $this->registry->get('log');
        $config = $this->registry->get('config');

        if ($log && $config->get('config_error_log')) {
            $request = $this->registry->get('request');
            $current_route = $request->get['route'] ?? 'N/A';
            $ip = $request->server['REMOTE_ADDR'] ?? 'CLI';

            $log->write(sprintf(
                "[Alpha DEPRECATION] Carregamento de Modelo Legado: '%s' | Originado na rota: '%s' | IP: %s",
                $route,
                $current_route,
                $ip
            ));
        }

        // 4. Delega para a Factory nativa se não houver interceptação Alpha
        return parent::model($route);
    }

    /**
     * Intercepta o carregamento de bibliotecas.
     * 
     * @param string       $route
     * @param array<mixed> $args
     * @return object
     */
    public function library(string $route, array $args = []): object
    {
        // Rastreamento de Fluxo (Trace)
        $this->getTraceLogger()->write("[Alpha TRACE] Solicitada Library: '{$route}'");

        // 1. Normaliza a rota
        $sanitized_route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);

        // 2. Exemplo de Intercepção: Se tentarem carregar uma biblioteca que agora é gerida pela Alpha Engine
        // como um validador de documentos ou um driver de exportação customizado.
        // if ($sanitized_route === 'tool/my_custom_library') { ... }

        // 3. Rastreamento de Débito Técnico para Bibliotecas
        /** @var Log|null $log */
        $log = $this->registry->get('log');
        $config = $this->registry->get('config');

        if ($log && $config->get('config_error_log')) {
            $request = $this->registry->get('request');
            $current_route = $request->get['route'] ?? 'N/A';
            $ip = $request->server['REMOTE_ADDR'] ?? 'CLI';

            // Verifica se a classe existe no padrão PSR-4 da Alpha antes de logar como legado
            // Isso ajuda a diferenciar o que é biblioteca nativa do OC e o que é Alpha.
            $alpha_class = 'Alpha\\Library\\' . str_replace(['_', '/'], ['', '\\'], ucwords($sanitized_route, '_/'));
            
            if (!class_exists($alpha_class)) {
                $log->write(sprintf(
                    "[Alpha DEPRECATION] Carregamento de Biblioteca Legada: '%s' | Originado na rota: '%s' | IP: %s",
                    $route,
                    $current_route,
                    $ip
                ));
            }
        }

        // 4. Delega para a Factory nativa
        return parent::library($route, $args);
    }

    /**
     * Método utilitário para checar se uma rota já possui domínio Alpha
     */
    private function isAlphaDomain(string $route): bool
    {
        // Implementar lógica de verificação baseada em nomes de arquivos core/
        return false;
    }

    public function config(string $route): void
    {
        // Rastreamento de Fluxo (Trace)
        $this->getTraceLogger()->write("[Alpha TRACE] Solicitada Config: '{$route}'");

        // Log de depreciação da Alpha Engine
        if ($this->registry->get('config')->get('config_error_log')) {
            $this->registry->get('log')->write(
                "[Alpha DEPRECATION] Carregamento de Config Legado: '{$route}'"
            );
        }

        // Passa o bastão para o repositório Alpha em vez do fluxo nativo
        $repositoryFactory = $this->registry->get('alpha_repository_factory');
        $repositoryFactory->get(\Alpha\Model\Domain\Repositories\ConfigurationRepository::class)->loadFile($route);
    }

}