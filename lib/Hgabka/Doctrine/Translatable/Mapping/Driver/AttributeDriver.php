<?php

namespace Hgabka\Doctrine\Translatable\Mapping\Driver;

use Hgabka\Doctrine\Translatable\Annotation\CurrentLocale;
use Hgabka\Doctrine\Translatable\Annotation\FallbackLocale;
use Hgabka\Doctrine\Translatable\Annotation\Locale;
use Hgabka\Doctrine\Translatable\Annotation\Translatable;
use Hgabka\Doctrine\Translatable\Annotation\Translations;
use Hgabka\Doctrine\Translatable\Mapping\PropertyMetadata;
use Hgabka\Doctrine\Translatable\Mapping\TranslatableMetadata;
use Hgabka\Doctrine\Translatable\Mapping\TranslationMetadata;
use Hgabka\Doctrine\Translatable\TranslatableInterface;
use Hgabka\Doctrine\Translatable\TranslationInterface;
use Metadata\ClassMetadata;
use Metadata\Driver\DriverInterface;

class AttributeDriver implements DriverInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class): ?ClassMetadata
    {
        if ($class->implementsInterface(TranslatableInterface::class)) {
            return $this->loadTranslatableMetadata($class);
        }

        if ($class->implementsInterface(TranslationInterface::class)) {
            return $this->loadTranslationMetadata($class);
        }

        return null;
    }

    private function getPropertyAttribute(\ReflectionProperty $property, string $name): object|null
    {
        $attributes = $property->getAttributes($name);

        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                if ($attribute->getName() === $name) {
                    return new $name(...$attribute->getArguments());
                }
            }
        }

        return null;
    }

    /**
     * Load metadata for a translatable class
     *
     * @param \ReflectionClass $class
     *
     * @return TranslatableMetadata
     */
    private function loadTranslatableMetadata(\ReflectionClass $class)
    {
        $classMetadata = new TranslatableMetadata($class->name);

        foreach ($class->getProperties() as $property) {
            if ($property->class !== $class->name) {
                continue;
            }

            $propertyMetadata = new PropertyMetadata($class->name, $property->getName());
            $targetEntity = $class->name . 'Translation';

            if ($this->getPropertyAttribute($property, CurrentLocale::class)) {
                $classMetadata->currentLocale = $propertyMetadata;
                $classMetadata->addPropertyMetadata($propertyMetadata);
            }

            if ($this->getPropertyAttribute($property, FallbackLocale::class)) {
                $classMetadata->fallbackLocale = $propertyMetadata;
                $classMetadata->addPropertyMetadata($propertyMetadata);
            }

            if ($annot = $this->getPropertyAttribute($property, Translations::class)) {
                $classMetadata->targetEntity = $annot->targetEntity ?? $targetEntity;
                $classMetadata->translations = $propertyMetadata;
                $classMetadata->addPropertyMetadata($propertyMetadata);
            }
        }

        return $classMetadata;
    }

    /**
     * Load metadata for a translation class
     *
     * @param \ReflectionClass $class
     *
     * @return TranslationMetadata
     */
    private function loadTranslationMetadata(\ReflectionClass $class)
    {
        $classMetadata = new TranslationMetadata($class->name);

        foreach ($class->getProperties() as $property) {
            if ($property->class !== $class->name) {
                continue;
            }

            $propertyMetadata = new PropertyMetadata($class->name, $property->getName());
            $targetEntity = 'Translation' === substr($class->name, -11) ? substr($class->name, 0, -11) : null;

            if ($annot = $this->getPropertyAttribute($property, Translatable::class)) {
                $classMetadata->targetEntity = $annot->targetEntity ?? $targetEntity;
                $classMetadata->referencedColumnName = $annot->referencedColumnName;
                $classMetadata->translatable = $propertyMetadata;
                $classMetadata->addPropertyMetadata($propertyMetadata);
            }

            if ($this->getPropertyAttribute($property, Locale::class)) {
                $classMetadata->locale = $propertyMetadata;
                $classMetadata->addPropertyMetadata($propertyMetadata);
            }
        }

        return $classMetadata;
    }
}
