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
    protected $id;

    /**
     * @ORM\Column(name="locale", type="string")
     * @Hgabka\Locale
     */
    protected $locale;

    /**
     * Get the ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the translatable object
     *
     * @return TranslatableInterface
     */
    public function getTranslatable()
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
    public function setTranslatable(TranslatableInterface $translatable = null)
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
    public function getLocale()
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
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
