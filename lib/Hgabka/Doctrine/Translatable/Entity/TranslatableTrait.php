<?php

namespace Hgabka\Doctrine\Translatable\Entity;

use Doctrine\Common\Collections\Collection;
use Hgabka\Doctrine\Translatable\TranslationInterface;

trait TranslatableTrait
{
    /**
     * Get the translations
     *
     * @return ArrayCollection
     */
    public function getTranslations(): Collection|array|null
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
    public function addTranslation(TranslationInterface $translation): self
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
    public function removeTranslation(TranslationInterface $translation): self
    {
        if ($this->translations->removeElement($translation)) {
            $translation->setTranslatable(null);
        }

        return $this;
    }
}
