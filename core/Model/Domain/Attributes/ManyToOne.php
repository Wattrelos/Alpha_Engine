<?php
namespace Alpha\Model\Domain\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToOne {
    public function __construct(public string $targetEntity, public ?string $foreignKey = null) {}
}
