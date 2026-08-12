<?php

namespace Alpha\Model\DataTransferObject\Attributes;

use Attribute;

/**
 * AllowHtml - Atributo para definir que uma propriedade pode receber HTML rico sem ser sanitizada.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class AllowHtml
{
}