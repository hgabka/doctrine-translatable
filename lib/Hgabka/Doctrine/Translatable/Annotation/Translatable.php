<?php

namespace Hgabka\Doctrine\Translatable\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * Translatable annotation
 *
 * This annotation indicates the many-to-one relation to the translatable.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("PROPERTY")
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Translatable
{
    public function __construct(public ?string $targetEntity = null, public ?string $referencedColumnName = 'id')
    {
    }
}
