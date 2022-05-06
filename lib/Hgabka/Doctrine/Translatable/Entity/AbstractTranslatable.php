<?php

namespace Hgabka\Doctrine\Translatable\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hgabka\Doctrine\Translatable\TranslatableInterface;
use Hgabka\Doctrine\Translatable\TranslationInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractTranslatable implements TranslatableInterface
{
    /**
     * ID
     *
     * Mapping provided by implementation
     */
    protected $id;

    /**
     * Translations
     *
     * Mapping provided by implementation
     */
    protected $translations;

    /**
     * Get the translations
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Add a translation
     *
     * @param TranslationInterface $translation
     *
     * @return self
     */
    public function addTranslation(TranslationInterface $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[$translation->getLocale()] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    /**
     * Remove a translation
     *
     * @param TranslationInterface $translation
     *
     * @return self
     */
    public function removeTranslation(TranslationInterface $translation)
    {
        if ($this->translations->removeElement($translation)) {
            $translation->setTranslatable(null);
        }

        return $this;
    }
}
