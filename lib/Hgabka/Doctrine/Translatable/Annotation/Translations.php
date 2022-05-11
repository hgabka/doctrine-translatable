<?php

namespace Hgabka\Doctrine\Translatable\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * Translations annotation
 *
 * This annotation indicates the one-to-many relation to the translations.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("PROPERTY")
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Translations
{
    public function __construct(public ?string $targetEntity)
    {
    }
}
