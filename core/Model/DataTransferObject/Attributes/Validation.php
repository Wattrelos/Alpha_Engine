<?php

namespace Alpha\Model\DataTransferObject\Attributes;

use Attribute;

/**
 * Validation - Atributo para definir regras de validação declarativas em propriedades de DTOs.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Validation
{
    public function __construct(
        public bool $required = false,
        public bool $email = false,
        public int $minLength = 0,
        public int $maxLength = 0,
        public ?string $callback = null
    ) {}
}