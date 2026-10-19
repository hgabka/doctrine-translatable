<?php

namespace Hgabka\Doctrine\Translatable\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use Hgabka\Doctrine\Translatable\Annotation as Hgabka;
use Hgabka\Doctrine\Translatable\Entity\TranslationTrait;
use Hgabka\Doctrine\Translatable\TranslatableInterface;
use Hgabka\Doctrine\Translatable\TranslationInterface;

#[ORM\Entity]
class ArticleTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(type: 'json', nullable: true)]
    protected array $tags = [];

    #[Hgabka\Translatable(targetEntity: Article::class)]
    private ?TranslatableInterface $translatable = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }
}
