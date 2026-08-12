<?php
namespace Alpha\Model\Domain\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class HasOne {
    public function __construct(public string $className, public string $foreignKey) {}
}
