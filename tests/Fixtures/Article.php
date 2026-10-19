<?php

namespace Hgabka\Doctrine\Translatable\Tests\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hgabka\Doctrine\Translatable\Annotation as Hgabka;
use Hgabka\Doctrine\Translatable\Entity\TranslatableTrait;
use Hgabka\Doctrine\Translatable\TranslatableInterface;

#[ORM\Entity]
class Article implements TranslatableInterface
{
    use TranslatableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[Hgabka\Translations(targetEntity: ArticleTranslation::class)]
    private Collection|array|null $translations = null;

    #[Hgabka\CurrentLocale]
    private ?string $currentLocale = null;

    #[Hgabka\FallbackLocale]
    private ?string $fallbackLocale = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getCurrentLocale(): ?string
    {
        return $this->currentLocale;
    }

    public function getFallbackLocale(): ?string
    {
        return $this->fallbackLocale;
    }
}
