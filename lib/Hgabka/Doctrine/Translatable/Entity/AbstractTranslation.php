<?php

namespace Hgabka\Doctrine\Translatable\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hgabka\Doctrine\Translatable\Annotation as Hgabka;
use Hgabka\Doctrine\Translatable\TranslatableInterface;
use Hgabka\Doctrine\Translatable\TranslationInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractTranslation implements TranslationInterface
{
    /**
     * ID
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    protected $id;

    /**
     * Translatable model
     *
     * Mapping provided by implementation
     */
    protected $translatable;

    /**
     * Locale
     *
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
     * @return self
     */
    public function setTranslatable(TranslatableInterface $translatable = null)
    {
        if ($this->translatable == $translatable) {
            return $this;
        }
    
        $old = $this->translatable;
        $this->translatable = $translatable;
    
        if ($old !== null) {
            $old->removeTranslation($this);
        }
    
        if ($translatable !== null) {
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
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }
}
