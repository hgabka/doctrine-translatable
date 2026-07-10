<?php

namespace Hgabka\Doctrine\Translatable\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hgabka\Doctrine\Translatable\Annotation as Hgabka;
use Hgabka\Doctrine\Translatable\TranslatableInterface;

trait TranslationTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    /**
     * @ORM\Column(name="locale", type="string")
     * @Hgabka\Locale
     */
    #[ORM\Column(name: 'locale', type: 'string')]
    #[Hgabka\Locale]
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

    /**
     * Whether this translation has no actual content filled in, besides
     * the id/locale/translatable bookkeeping fields. Used by a2lix's
     * TranslationsType to discard empty, non-required locale translations.
     */
    public function isEmpty(): bool
    {
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties() as $property) {
            if (\in_array($property->getName(), ['id', 'locale', 'translatable'], true)) {
                continue;
            }

            $property->setAccessible(true);

            if (!$property->isInitialized($this)) {
                continue;
            }

            $value = $property->getValue($this);

            if (null === $value || '' === $value) {
                continue;
            }

            if (\is_array($value) && [] === $value) {
                continue;
            }

            if ($value instanceof \Countable && 0 === \count($value)) {
                continue;
            }

            return false;
        }

        return true;
    }
}
