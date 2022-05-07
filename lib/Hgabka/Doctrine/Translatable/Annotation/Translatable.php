<?php

namespace Hgabka\Doctrine\Translatable\Annotation;

use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;

/**
 * Translatable annotation
 *
 * This annotation indicates the many-to-one relation to the translatable.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Translatable implements NamedArgumentConstructorAnnotation
{
    public function __construct(public ?string $targetEntity = null, public ?string $referencedColumnName = 'id')
    {
    }
}
