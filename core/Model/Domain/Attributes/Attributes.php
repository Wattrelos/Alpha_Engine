<?php
namespace Alpha\Model\Domain\Attributes;

use Attribute;



#[Attribute(Attribute::TARGET_PROPERTY)]
class HasOne {
    public function __construct(public string $className, public string $foreignKey) {}
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToMany {
    public function __construct(public string $targetEntity, public ?string $foreignKey = null) {}
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToMany {
    public function __construct(public string $targetEntity) {}
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToOne {
    public function __construct(public string $targetEntity, public ?string $foreignKey = null) {}
}