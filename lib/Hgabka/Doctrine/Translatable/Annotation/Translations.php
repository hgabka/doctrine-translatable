<?php

namespace Hgabka\Doctrine\Translatable\Annotation;

use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;

/**
 * Translations annotation
 *
 * This annotation indicates the one-to-many relation to the translations.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Translations implements NamedArgumentConstructorAnnotation
{
    public function __construct(public ?string $targetEntity)
    {
    }
}
