<?php

namespace Alpha\Model\Domain\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToMany
{
    public function __construct(
        public string $targetEntity, 
        public ?string $mappedBy = null, 
        public ?string $foreignKey = null
    ) {}
}