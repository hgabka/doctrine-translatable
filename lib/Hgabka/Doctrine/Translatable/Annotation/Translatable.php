<?php

namespace Hgabka\Doctrine\Translatable\Annotation;

/**
 * Translatable annotation
 *
 * This annotation indicates the many-to-one relation to the translatable.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Translatable
{
    /**
     * @var string
     */
    public $targetEntity;

    /**
     * @var string
     */
    public $referencedColumnName = 'id';
}
