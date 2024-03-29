<?php

namespace Hgabka\Doctrine\Translatable\Annotation;

/**
 * CurrentTranslation annotation
 *
 * This annotation indicates the property where the current translation object
 * must be loaded.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Locale
{
}
