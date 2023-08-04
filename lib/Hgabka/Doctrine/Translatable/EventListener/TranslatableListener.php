<?php

namespace Hgabka\Doctrine\Translatable\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Hgabka\Doctrine\Translatable\Mapping\TranslatableMetadata;
use Hgabka\Doctrine\Translatable\Mapping\TranslationMetadata;
use Hgabka\Doctrine\Translatable\TranslatableInterface;
use Hgabka\Doctrine\Translatable\TranslationInterface;
use Metadata\MetadataFactory;
use ReflectionClass;
use ReflectionProperty;


#[AsDoctrineListener(event: Events::loadClassMetadata, priority: 255)]
#[AsDoctrineListener(event: Events::postLoad, priority: 255)]
class TranslatableListener
{
    /**
     * @var string Locale to use for translations
     */
    private string $currentLocale = 'en';

    /**
     * @var string Locale to use when the current locale is not available
     */
    private string $fallbackLocale = 'en';

    /**
     * @var array
     */
    private array $cache = [];

    /**
     * Constructor
     *
     * @param MetadataFactory $factory
     */
    public function __construct(private readonly MetadataFactory $metadataFactory) {}

    /**
     * Get the current locale
     *
     * @return string
     */
    public function getCurrentLocale(): string
    {
        return $this->currentLocale;
    }

    /**
     * Set the current locale
     *
     * @param string $currentLocale
     *
     * @return self
     */
    public function setCurrentLocale(string $currentLocale): self
    {
        $this->currentLocale = $currentLocale;

        return $this;
    }

    /**
     * Get the fallback locale
     *
     * @return string
     */
    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale;
    }

    /**
     * Set the fallback locale
     *
     * @param string $fallbackLocale
     *
     * @return self
     */
    public function setFallbackLocale(string $fallbackLocale): self
    {
        $this->fallbackLocale = $fallbackLocale;

        return $this;
    }

    /**
     * Getter for metadataFactory
     *
     * @return MetadataFactory
     */
    public function getMetadataFactory(): MetadataFactory
    {
        return $this->metadataFactory;
    }

    /**
     * Add mapping to translatable entities
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     *
     * @return void
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $reflClass = $classMetadata->reflClass;

        if (!$reflClass) {
            return;
        }

        if ($reflClass->implementsInterface(TranslatableInterface::class)) {
            $this->mapTranslatable($classMetadata);
        }

        if ($reflClass->implementsInterface(TranslationInterface::class)) {
            $this->mapTranslation($classMetadata);
        }
    }

    /**
     * Get translatable metadata
     *
     * @param string $className
     *
     * @return TranslatableMetadata|TranslationMetadata
     */
    public function getTranslatableMetadata(string $className): mixed
    {
        if (array_key_exists($className, $this->cache)) {
            return $this->cache[$className];
        }

        if ($metadata = $this->metadataFactory->getMetadataForClass($className)) {
            $reflection = new ReflectionClass($className);

            if (!$reflection->isAbstract()) {
                $metadata->validate();
            }
        }

        $this->cache[$className] = $metadata;

        return $metadata;
    }

    /**
     * Load translations
     *
     * @param LifecycleEventArgs $args
     *
     * @return void
     */
    public function postLoad(PostLoadEventArgs $args): void
    {
        $entity = $args->getObject();

        $class = $args->getObjectManager()->getClassMetadata($entity::class)->getName(); // Resolve proxy class
        $metadata = $this->getTranslatableMetadata($class);

        if ($metadata instanceof TranslatableMetadata) {
            if ($metadata->fallbackLocale) {
                $this->setReflectionPropertyValue($entity, $class, 'fallbackLocale', $this->getFallbackLocale());
            }

            if ($metadata->currentLocale) {
                $this->setReflectionPropertyValue($entity, $class, 'currentLocale', $this->getCurrentLocale());
            }
        }
    }

    /**
     * Add mapping data to a translatable entity
     *
     * @param ClassMetadata $mapping
     *
     * @return void
     */
    private function mapTranslatable(ClassMetadata $mapping): void
    {
        $metadata = $this->getTranslatableMetadata($mapping->name);

        if ($metadata->targetEntity
            && $metadata->translations
            && !$mapping->hasAssociation($metadata->translations->name)
        ) {
            $targetMetadata = $this->getTranslatableMetadata($metadata->targetEntity);

            $mapping->mapOneToMany([
                'fieldName'     => $metadata->translations->name,
                'targetEntity'  => $metadata->targetEntity,
                'mappedBy'      => $targetMetadata->translatable->name,
                'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'indexBy'       => $targetMetadata->locale->name,
                'cascade'       => ['persist', 'merge', 'remove'],
                'orphanRemoval' => true,
            ]);
        }
    }

    /**
     * Add mapping data to a translation entity
     *
     * @param ClassMetadata $mapping
     *
     * @return void
     */
    private function mapTranslation(ClassMetadata $mapping): void
    {
        $metadata = $this->getTranslatableMetadata($mapping->name);

        // Map translatable relation
        if ($metadata->targetEntity
            && $metadata->translatable
            && !$mapping->hasAssociation($metadata->translatable->name)
        ) {
            $targetMetadata = $this->getTranslatableMetadata($metadata->targetEntity);

            $mapping->mapManyToOne([
                'fieldName'    => $metadata->translatable->name,
                'targetEntity' => $metadata->targetEntity,
                'inversedBy'   => $targetMetadata->translations->name,
                'joinColumns'  => [[
                    'name'                 => 'translatable_id',
                    'referencedColumnName' => $metadata->referencedColumnName,
                    'onDelete'             => 'CASCADE',
                    'nullable'             => false,
                ]],
            ]);
        }

        if (!$metadata->translatable) {
            return;
        }

        // Map locale field
        if (!$mapping->hasField($metadata->locale->name)) {
            $mapping->mapField([
                'fieldName' => $metadata->locale->name,
                'type' => 'string',
                'length' => 5,
            ]);
        }

        // Map unique index
        $columns = [
            $mapping->getSingleAssociationJoinColumnName($metadata->translatable->name),
            $metadata->locale->name,
        ];

        if (!$this->hasUniqueConstraint($mapping, $columns)) {
            $constraints = $mapping->table['uniqueConstraints'] ?? [];
            $constraints[$mapping->getTableName() . '_uniq_trans'] = [
                'columns' => $columns,
            ];

            $mapping->setPrimaryTable([
                'uniqueConstraints' => $constraints,
            ]);
        }
    }

    /**
     * Check if an unique constraint has been defined
     *
     * @param ClassMetadata $mapping
     * @param array         $columns
     *
     * @return bool
     */
    private function hasUniqueConstraint(ClassMetadata $mapping, array $columns): bool
    {
        if (!array_diff($mapping->getIdentifierColumnNames(), $columns)) {
            return true;
        }

        if (!isset($mapping->table['uniqueConstraints'])) {
            return false;
        }

        foreach ($mapping->table['uniqueConstraints'] as $constraint) {
            if (!array_diff($constraint['columns'], $columns)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param object $object
     * @param string $property
     * @param mixed  $value
     * @param mixed  $class
     */
    private function setReflectionPropertyValue($object, $class, string $property, $value): void
    {
        $reflection = new ReflectionProperty($class, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }
}
