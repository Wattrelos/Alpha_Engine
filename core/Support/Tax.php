<?php

namespace Alpha\Support;

use Alpha\Model\DataAccessObject\DataAccessObject;
use Alpha\Model\DataAccessObject\QueryBuilder;

/**
 * Classe de Suporte a Impostos (Tax) para a Alpha Engine.
 * 
 * Substitui o motor de cálculo de impostos legado, integrando-se
 * aos repositórios modernos de domínio da Alpha Engine.
 */
class Tax
{
    private mixed $registry = null;
    private ?object $config = null;
    private ?object $customer = null;
    
    private int $shipping_country_id = 0;
    private int $shipping_zone_id = 0;
    private int $payment_country_id = 0;
    private int $payment_zone_id = 0;
    private int $store_country_id = 0;
    private int $store_zone_id = 0;

    public function __construct(mixed $registry = null)
    {
        $this->registry = $registry;
        if ($registry) {
            $this->config = $registry->get('config');
            $this->customer = $registry->get('customer');
            
            if ($this->config) {
                $this->store_country_id = (int)$this->config->get('config_country_id');
                $this->store_zone_id = (int)$this->config->get('config_zone_id');
            }
        }
    }

    /**
     * Define o endereço de entrega para cálculo de impostos.
     */
    public function setShippingAddress(int $country_id, int $zone_id): void
    {
        $this->shipping_country_id = $country_id;
        $this->shipping_zone_id = $zone_id;
    }

    /**
     * Define o endereço de cobrança para cálculo de impostos.
     */
    public function setPaymentAddress(int $country_id, int $zone_id): void
    {
        $this->payment_country_id = $country_id;
        $this->payment_zone_id = $zone_id;
    }

    /**
     * Define o endereço da loja física para cálculo de impostos.
     */
    public function setStoreAddress(int $country_id, int $zone_id): void
    {
        $this->store_country_id = $country_id;
        $this->store_zone_id = $zone_id;
    }

    /**
     * Calcula o valor acrescido de impostos para um produto.
     */
    public function calculate(float $value, int $tax_class_id, bool $calculate = true): float
    {
        if ($calculate && $tax_class_id) {
            $amount = 0.0;
            $rates = $this->getRates($value, $tax_class_id);
            foreach ($rates as $rate) {
                $amount += $rate['amount'];
            }
            return $value + $amount;
        }
        return $value;
    }

    /**
     * Retorna a lista de taxas e alíquotas aplicáveis.
     */
    public function getRates(float $value, int $tax_class_id): array
    {
        $tax_rates = [];

        if (!$tax_class_id) {
            return $tax_rates;
        }

        $customer_group_id = 1;
        if ($this->customer && method_exists($this->customer, 'isLogged') && $this->customer->isLogged()) {
            $customer_group_id = (int)$this->customer->getGroupId();
        } elseif ($this->config) {
            $customer_group_id = (int)$this->config->get('config_customer_group_id');
        }

        $dao = new DataAccessObject();
        
        // Query de regras de impostos baseadas no grupo do cliente e na classe de imposto
        $query = (new QueryBuilder())
            ->from(DB_PREFIX . 'tax_rule', 'tr')
            ->join(DB_PREFIX . 'tax_rate', 'tra', 'tr.tax_rate_id = tra.id')
            ->join(DB_PREFIX . 'tax_rate_to_customer_group', 'tr2cg', 'tra.id = tr2cg.tax_rate_id')
            ->where('tr.tax_class_id = ?', [$tax_class_id])
            ->where('tr2cg.customer_group_id = ?', [$customer_group_id])
            ->select('tr.based', 'tr.priority', 'tra.id AS tax_rate_id', 'tra.name', 'tra.rate', 'tra.type', 'tra.geo_zone_id');

        $rules = $dao->executeQuery($query);

        foreach ($rules as $rule) {
            $country_id = 0;
            $zone_id = 0;

            if ($rule['based'] === 'shipping') {
                $country_id = $this->shipping_country_id;
                $zone_id = $this->shipping_zone_id;
            } elseif ($rule['based'] === 'payment') {
                $country_id = $this->payment_country_id;
                $zone_id = $this->payment_zone_id;
            } elseif ($rule['based'] === 'store') {
                $country_id = $this->store_country_id;
                $zone_id = $this->store_zone_id;
            }

            // Verifica se o endereço se enquadra na zona geográfica associada à taxa
            $geo_zone_query = (new QueryBuilder())
                ->from(DB_PREFIX . 'zone_to_geo_zone', 'z2gz')
                ->where('z2gz.geo_zone_id = ?', [(int)$rule['geo_zone_id']])
                ->where('z2gz.country_id = ?', [$country_id])
                ->where('(z2gz.zone_id = 0 OR z2gz.zone_id = ?)', [$zone_id])
                ->select('z2gz.geo_zone_id');

            $geo_zone_match = $dao->executeQuery($geo_zone_query);

            if ($geo_zone_match) {
                $amount = 0.0;
                if ($rule['type'] === 'F') {
                    $amount = (float)$rule['rate'];
                } elseif ($rule['type'] === 'P') {
                    $amount = ($value * (float)$rule['rate']) / 100;
                }

                if (!isset($tax_rates[$rule['tax_rate_id']])) {
                    $tax_rates[$rule['tax_rate_id']] = [
                        'tax_rate_id' => $rule['tax_rate_id'],
                        'name'        => $rule['name'],
                        'rate'        => $rule['rate'],
                        'type'        => $rule['type'],
                        'amount'      => $amount
                    ];
                } else {
                    $tax_rates[$rule['tax_rate_id']]['amount'] += $amount;
                }
            }
        }

        return $tax_rates;
    }
}
