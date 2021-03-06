<?php

namespace Hgabka\Doctrine\Translatable\Mapping\Driver;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Metadata\ClassMetadata;
use Metadata\Driver\DriverInterface;
use Hgabka\Doctrine\Translatable\Annotation\CurrentTranslation;
use Hgabka\Doctrine\Translatable\Annotation\FallbackTranslation;
use Hgabka\Doctrine\Translatable\Annotation\Translations;
use Hgabka\Doctrine\Translatable\Mapping\PropertyMetadata;
use Hgabka\Doctrine\Translatable\Mapping\TranslatableMetadata;
use Hgabka\Doctrine\Translatable\Mapping\TranslationMetadata;

/**
 * Load translation metadata from annotations
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * Constructor
     *
     * @param Reader $reader
     * @param ClassMetadataFactory $factory Doctrine's metadata factory
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class): ?ClassMetadata
    {
        if ($class->implementsInterface('Hgabka\\Doctrine\\Translatable\\TranslatableInterface')) {
            return $this->loadTranslatableMetadata($class);
        }

        if ($class->implementsInterface('Hgabka\\Doctrine\\Translatable\\TranslationInterface')) {
            return $this->loadTranslationMetadata($class);
        }

        return null;
    }

    /**
     * Load metadata for a translatable class
     *
     * @param \ReflectionClass $class
     * @return TranslatableMetadata
     */
    private function loadTranslatableMetadata(\ReflectionClass $class) {
        $classMetadata = new TranslatableMetadata($class->name);

        foreach ($class->getProperties() as $property) {
            if ($property->class !== $class->name) {
                continue;
            }

            $propertyMetadata = new PropertyMetadata($class->name, $property->getName());
            $targetEntity = $class->name . 'Translation';

            if ($this->reader->getPropertyAnnotation($property, 'Hgabka\\Doctrine\\Translatable\\Annotation\\CurrentLocale')) {
                $classMetadata->currentLocale = $propertyMetadata;
                $classMetadata->addPropertyMetadata($propertyMetadata);
            }

            if ($this->reader->getPropertyAnnotation($property, 'Hgabka\\Doctrine\\Translatable\\Annotation\\FallbackLocale')) {
                $classMetadata->fallbackLocale = $propertyMetadata;
                $classMetadata->addPropertyMetadata($propertyMetadata);
            }

            if ($annot = $this->reader->getPropertyAnnotation($property, 'Hgabka\\Doctrine\\Translatable\\Annotation\\Translations')) {
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

            if ($annot = $this->reader->getPropertyAnnotation($property, 'Hgabka\\Doctrine\\Translatable\\Annotation\\Translatable')) {
                $classMetadata->targetEntity = $annot->targetEntity ?? $targetEntity;
                $classMetadata->referencedColumnName = $annot->referencedColumnName;
                $classMetadata->translatable = $propertyMetadata;
                $classMetadata->addPropertyMetadata($propertyMetadata);
            }

            if ($this->reader->getPropertyAnnotation($property, 'Hgabka\\Doctrine\\Translatable\\Annotation\\Locale')) {
                $classMetadata->locale = $propertyMetadata;
                $classMetadata->addPropertyMetadata($propertyMetadata);
            }
        }

        return $classMetadata;
    }
}
