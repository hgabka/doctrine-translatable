<?php

namespace Hgabka\Doctrine\Translatable\Tests\Fixtures;

use Hgabka\Doctrine\Translatable\TranslatableInterface;

/** Translatable without a #[Translations] property. */
class BrokenTranslatable implements TranslatableInterface
{
    private ?string $currentLocale = null;
}
