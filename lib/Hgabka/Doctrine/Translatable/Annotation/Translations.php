<?php

namespace Hgabka\Doctrine\Translatable\Annotation;

/**
 * Translations annotation
 *
 * This annotation indicates the one-to-many relation to the translations.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Translations
{
    /**
     * @var string
     */
    public $targetEntity;
}
