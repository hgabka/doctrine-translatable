<?php

namespace Hgabka\Doctrine\Translatable\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hgabka\Doctrine\Translatable\Annotation as Hgabka;
use Hgabka\Doctrine\Translatable\TranslatableInterface;
use Hgabka\Doctrine\Translatable\TranslationInterface;

/**
 * @ORM\MappedSuperclass
 */
#[ORM\MappedSuperclass]
abstract class AbstractTranslation implements TranslationInterface
{
    /**
     * ID
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    /**
     * Translatable model
     *
     * Mapping provided by implementation
     */
    protected ?TranslatableInterface $translatable = null;

    /**
     * Locale
     *
     * @ORM\Column(name="locale", type="string")
     * @Hgabka\Locale
     */
    #[Hgabka\Locale]
    #[ORM\Column(name: 'locale', type: 'string')]
    protected ?string $locale = null;

    /**
     * Get the ID
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the translatable object
     *
     * @return TranslatableInterface
     */
    public function getTranslatable(): ?TranslatableInterface
    {
        return $this->translatable;
    }

    /**
     * Set the translatable object
     *
     * @param TranslatableInterface $translatable
     *
     * @return self
     */
    public function setTranslatable(?TranslatableInterface $translatable = null): self
    {
        if ($this->translatable === $translatable) {
            return $this;
        }

        $old = $this->translatable;
        $this->translatable = $translatable;

        if (null !== $old) {
            $old->removeTranslation($this);
        }

        if (null !== $translatable) {
            $translatable->addTranslation($this);
        }

        return $this;
    }

    /**
     * Get the locale
     *
     * @return string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Set the locale
     *
     * @param string $locale
     *
     * @return self
     */
    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }
}
