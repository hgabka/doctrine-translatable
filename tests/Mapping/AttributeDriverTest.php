<?php

namespace Hgabka\Doctrine\Translatable\Tests\Mapping;

use Hgabka\Doctrine\Translatable\Mapping\Driver\AttributeDriver;
use Hgabka\Doctrine\Translatable\Mapping\MappingException;
use Hgabka\Doctrine\Translatable\Mapping\TranslatableMetadata;
use Hgabka\Doctrine\Translatable\Mapping\TranslationMetadata;
use Hgabka\Doctrine\Translatable\Tests\Fixtures\Article;
use Hgabka\Doctrine\Translatable\Tests\Fixtures\ArticleTranslation;
use Hgabka\Doctrine\Translatable\Tests\Fixtures\BrokenTranslatable;
use Hgabka\Doctrine\Translatable\Tests\Fixtures\Plain;
use PHPUnit\Framework\TestCase;

class AttributeDriverTest extends TestCase
{
    private AttributeDriver $driver;

    protected function setUp(): void
    {
        $this->driver = new AttributeDriver();
    }

    public function testNonTranslatableClassHasNoMetadata(): void
    {
        $this->assertNull($this->driver->loadMetadataForClass(new \ReflectionClass(Plain::class)));
    }

    public function testTranslatableMetadata(): void
    {
        $metadata = $this->driver->loadMetadataForClass(new \ReflectionClass(Article::class));

        $this->assertInstanceOf(TranslatableMetadata::class, $metadata);
        $this->assertSame(ArticleTranslation::class, $metadata->targetEntity);
        $this->assertSame('translations', $metadata->translations->name);
        $this->assertSame('currentLocale', $metadata->currentLocale->name);
        $this->assertSame('fallbackLocale', $metadata->fallbackLocale->name);
        $this->assertNull($metadata->validate());
    }

    public function testTranslationMetadata(): void
    {
        $metadata = $this->driver->loadMetadataForClass(new \ReflectionClass(ArticleTranslation::class));

        $this->assertInstanceOf(TranslationMetadata::class, $metadata);
        $this->assertSame(Article::class, $metadata->targetEntity);
        $this->assertSame('id', $metadata->referencedColumnName);
        $this->assertSame('translatable', $metadata->translatable->name);
        $this->assertNull($metadata->validate());
    }

    public function testTranslatableWithoutTranslationsIsInvalid(): void
    {
        $metadata = $this->driver->loadMetadataForClass(new \ReflectionClass(BrokenTranslatable::class));

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage('No translations specified for ' . BrokenTranslatable::class);
        $metadata->validate();
    }
}
