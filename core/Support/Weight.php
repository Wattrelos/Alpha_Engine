<?php

namespace Alpha\Support;

use Alpha\Model\Domain\Repositories\WeightClassRepository;

class Weight
{
    private mixed $registry;
    private array $weights = [];

    public function __construct(mixed $registry)
    {
        $this->registry = $registry;
        $this->loadWeights();
    }

    private function loadWeights(): void
    {
        try {
            /** @var WeightClassRepository $weightRepository */
            $weightRepository = \Alpha\Model\Domain\Repositories\RepositoryFactory::getInstance()->get(WeightClassRepository::class);
            $results = $weightRepository->getAllByCurrentLanguage();
            foreach ($results as $result) {
                $descriptions = $result->getDescriptions();
                $description = !empty($descriptions) ? $descriptions[0] : null;
                $this->weights[$result->getId()] = [
                    'weight_class_id' => $result->getId(),
                    'title'           => $description ? $description->getTitle() : '',
                    'unit'            => $description ? $description->getUnit() : '',
                    'value'           => $result->getValue()
                ];
            }
        } catch (\Exception $e) {
            // Fallback se ocorrer erro antes de o DB estar pronto
        }
    }

    public function convert(float $value, int $from, int $to): float
    {
        if ($from == $to) {
            return $value;
        }

        $fromVal = isset($this->weights[$from]) ? (float)$this->weights[$from]['value'] : 1.0;
        $toVal = isset($this->weights[$to]) ? (float)$this->weights[$to]['value'] : 1.0;

        $fromVal = $fromVal > 0 ? $fromVal : 1.0;

        return $value * ($toVal / $fromVal);
    }

    public function format(float $value, int $weight_class_id, string $decimal_point = '.', string $thousand_point = ','): string
    {
        if (isset($this->weights[$weight_class_id])) {
            return number_format($value, 2, $decimal_point, $thousand_point) . ' ' . $this->weights[$weight_class_id]['unit'];
        }
        return number_format($value, 2, $decimal_point, $thousand_point);
    }

    public function getUnit(int $weight_class_id): string
    {
        return $this->weights[$weight_class_id]['unit'] ?? '';
    }
}
