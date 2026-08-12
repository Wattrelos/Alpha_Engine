<?php
namespace Alpha\Model\Domain\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToMany {
    public function __construct(public string $targetEntity) {}
}
