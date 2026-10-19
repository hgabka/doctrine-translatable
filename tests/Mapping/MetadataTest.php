<?php

namespace Hgabka\Doctrine\Translatable\Tests\Mapping;

use Hgabka\Doctrine\Translatable\Mapping\Driver\AttributeDriver;
use Hgabka\Doctrine\Translatable\Mapping\MappingException;
use Hgabka\Doctrine\Translatable\Mapping\TranslatableMetadata;
use Hgabka\Doctrine\Translatable\Mapping\TranslationMetadata;
use Hgabka\Doctrine\Translatable\Tests\Fixtures\Article;
use Hgabka\Doctrine\Translatable\Tests\Fixtures\ArticleTranslation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MetadataTest extends TestCase
{
    private function load(string $class)
    {
        return (new AttributeDriver())->loadMetadataForClass(new \ReflectionClass($class));
    }

    public function testTranslatableMetadataRequiresTargetEntity(): void
    {
        $metadata = $this->load(Article::class);
        $metadata->targetEntity = null;

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage('No translations targetEntity specified');
        $metadata->validate();
    }

    #[DataProvider('incompleteTranslationMetadata')]
    public function testTranslationMetadataValidation(string $field, string $message): void
    {
        $metadata = $this->load(ArticleTranslation::class);
        $metadata->locale = $metadata->translatable; // validate() only checks truthiness
        $metadata->$field = null;

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage($message);
        $metadata->validate();
    }

    public static function incompleteTranslationMetadata(): array
    {
        return [
            'translatable' => ['translatable', 'No translatable specified'],
            'targetEntity' => ['targetEntity', 'No translatable targetEntity specified'],
            'referencedColumnName' => ['referencedColumnName', 'No translatable referencedColumnName specified'],
            'locale' => ['locale', 'No locale specified'],
        ];
    }

    public function testTranslatableMetadataSerializationRoundTrip(): void
    {
        $metadata = $this->load(Article::class);

        $copy = new TranslatableMetadata(Article::class);
        $copy->unserialize($metadata->serialize());

        $this->assertSame(ArticleTranslation::class, $copy->targetEntity);
        $this->assertSame('translations', $copy->translations->name);
        $this->assertSame('currentLocale', $copy->currentLocale->name);
        $this->assertSame('fallbackLocale', $copy->fallbackLocale->name);
    }

    public function testTranslatableMetadataMerge(): void
    {
        $base = new TranslatableMetadata(Article::class);
        $other = $this->load(Article::class);

        $base->merge($other);

        $this->assertSame(ArticleTranslation::class, $base->targetEntity);
        $this->assertSame('translations', $base->translations->name);
    }

    public function testMergeRejectsOtherMetadataTypes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new TranslatableMetadata(Article::class))->merge(new TranslationMetadata(ArticleTranslation::class));
    }

    public function testTranslationMetadataSerializationRoundTrip(): void
    {
        $metadata = $this->load(ArticleTranslation::class);

        $copy = new TranslationMetadata(ArticleTranslation::class);
        $copy->unserialize($metadata->serialize());

        $this->assertSame(Article::class, $copy->targetEntity);
        $this->assertSame('id', $copy->referencedColumnName);
        $this->assertSame('translatable', $copy->translatable->name);
    }
}
