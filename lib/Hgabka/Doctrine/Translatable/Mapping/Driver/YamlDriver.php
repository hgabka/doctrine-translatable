<?php

namespace Hgabka\Doctrine\Translatable\Mapping\Driver;

use Hgabka\Doctrine\Translatable\Mapping\PropertyMetadata;
use Hgabka\Doctrine\Translatable\Mapping\TranslatableMetadata;
use Hgabka\Doctrine\Translatable\Mapping\TranslationMetadata;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads translatable metadata from Yaml mapping files.
 *
 * @author Joris van de Sande <joris.van.de.sande@freshheads.com>
 */
class YamlDriver extends FileDriver
{
    /**
     * Load metadata for a translatable class
     *
     * @param string $className
     * @param mixed  $config
     *
     * @return null|TranslatableMetadata
     */
    protected function loadTranslatableMetadata($className, $config)
    {
        if (!isset($config[$className])
            || !isset($config[$className]['hgabka'])
            || !array_key_exists('translatable', $config[$className]['hgabka'])
        ) {
            return;
        }

        $classMetadata = new TranslatableMetadata($className);

        $translatable = $config[$className]['hgabka']['translatable'] ?: [];

        $propertyMetadata = new PropertyMetadata(
            $className,
            // defaults to translatable
            $translatable['field'] ?? 'translations'
        );

        // default targetEntity
        $targetEntity = $className . 'Translation';

        $classMetadata->targetEntity = $translatable['targetEntity'] ?? $targetEntity;
        $classMetadata->translations = $propertyMetadata;
        $classMetadata->addPropertyMetadata($propertyMetadata);

        if (isset($translatable['currentLocale'])) {
            $propertyMetadata = new PropertyMetadata($className, $translatable['currentLocale']);

            $classMetadata->currentLocale = $propertyMetadata;
            $classMetadata->addPropertyMetadata($propertyMetadata);
        }

        if (isset($translatable['fallbackLocale'])) {
            $propertyMetadata = new PropertyMetadata($className, $translatable['fallbackLocale']);

            $classMetadata->fallbackLocale = $propertyMetadata;
            $classMetadata->addPropertyMetadata($propertyMetadata);
        }

        return $classMetadata;
    }

    /**
     * Load metadata for a translation class
     *
     * @param string $className
     * @param mixed  $config
     *
     * @return null|TranslationMetadata
     */
    protected function loadTranslationMetadata($className, $config)
    {
        if (!isset($config[$className])
            || !isset($config[$className]['hgabka'])
            || !array_key_exists('translatable', $config[$className]['hgabka'])
        ) {
            return;
        }

        $classMetadata = new TranslationMetadata($className);

        $translatable = $config[$className]['hgabka']['translatable'] ?: [];

        $propertyMetadata = new PropertyMetadata(
            $className,
            // defaults to translatable
            $translatable['field'] ?? 'translatable'
        );

        $targetEntity = 'Translation' === substr($className, -11) ? substr($className, 0, -11) : null;

        $classMetadata->targetEntity = $translatable['targetEntity'] ?? $targetEntity;
        $classMetadata->referencedColumnName = $translatable['referencedColumnName'] ?? 'id';
        $classMetadata->translatable = $propertyMetadata;
        $classMetadata->addPropertyMetadata($propertyMetadata);

        $locale = $translatable['locale'] ?? 'locale';
        $propertyMetadata = new PropertyMetadata($className, $locale);
        $classMetadata->locale = $propertyMetadata;
        $classMetadata->addPropertyMetadata($propertyMetadata);

        return $classMetadata;
    }

    /**
     * Parses the given mapping file.
     *
     * @param string $file
     *
     * @return mixed
     */
    protected function parse($file)
    {
        return Yaml::parse(file_get_contents($file));
    }
}
