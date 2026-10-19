<?php

namespace Hgabka\Doctrine\Translatable\Tests\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Hgabka\Doctrine\Translatable\EventListener\TranslatableListener;
use Hgabka\Doctrine\Translatable\Mapping\Driver\AttributeDriver;
use Hgabka\Doctrine\Translatable\Tests\Fixtures\Article;
use Hgabka\Doctrine\Translatable\Tests\Fixtures\ArticleTranslation;
use Hgabka\Doctrine\Translatable\Tests\Fixtures\Plain;
use Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;

class TranslatableListenerTest extends TestCase
{
    private TranslatableListener $listener;

    protected function setUp(): void
    {
        $this->listener = new TranslatableListener(new MetadataFactory(new AttributeDriver()));
    }

    private function classMetadata(string $class): ClassMetadata
    {
        $metadata = new ClassMetadata($class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);

        return $metadata;
    }

    private function loadEvent(ClassMetadata $metadata): LoadClassMetadataEventArgs
    {
        return new LoadClassMetadataEventArgs($metadata, $this->createStub(EntityManagerInterface::class));
    }

    public function testLocalesHaveDefaultsAndAreFluent(): void
    {
        $this->assertSame('en', $this->listener->getCurrentLocale());
        $this->assertSame('en', $this->listener->getFallbackLocale());

        $this->assertSame($this->listener, $this->listener->setCurrentLocale('hu'));
        $this->assertSame($this->listener, $this->listener->setFallbackLocale('de'));
        $this->assertSame('hu', $this->listener->getCurrentLocale());
        $this->assertSame('de', $this->listener->getFallbackLocale());
    }

    public function testTranslatableGetsOneToManyAssociation(): void
    {
        $metadata = $this->classMetadata(Article::class);

        $this->listener->loadClassMetadata($this->loadEvent($metadata));

        $this->assertTrue($metadata->hasAssociation('translations'));
        $mapping = $metadata->getAssociationMapping('translations');
        $this->assertSame(ArticleTranslation::class, $mapping['targetEntity']);
        $this->assertSame('translatable', $mapping['mappedBy']);
        $this->assertSame('locale', $mapping['indexBy']);
        $this->assertTrue($mapping['orphanRemoval']);
    }

    public function testTranslationGetsManyToOneLocaleAndUniqueConstraint(): void
    {
        $metadata = $this->classMetadata(ArticleTranslation::class);

        $this->listener->loadClassMetadata($this->loadEvent($metadata));

        $this->assertTrue($metadata->hasAssociation('translatable'));
        $mapping = $metadata->getAssociationMapping('translatable');
        $this->assertSame(Article::class, $mapping['targetEntity']);
        $this->assertSame('translations', $mapping['inversedBy']);

        $this->assertTrue($metadata->hasField('locale'));
        $constraints = $metadata->table['uniqueConstraints'];
        $this->assertCount(1, $constraints);
        $constraint = reset($constraints);
        $this->assertSame(['translatable_id', 'locale'], $constraint['columns']);
    }

    public function testUnrelatedEntitiesAreLeftAlone(): void
    {
        $metadata = $this->classMetadata(Plain::class);

        $this->listener->loadClassMetadata($this->loadEvent($metadata));

        $this->assertSame([], $metadata->associationMappings);
    }

    public function testPostLoadInjectsLocales(): void
    {
        $this->listener->setCurrentLocale('hu')->setFallbackLocale('de');
        $article = new Article();

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getClassMetadata')->willReturn($this->classMetadata(Article::class));

        $this->listener->postLoad(new PostLoadEventArgs($article, $em));

        $this->assertSame('hu', $article->getCurrentLocale());
        $this->assertSame('de', $article->getFallbackLocale());
    }

    public function testTranslatableMetadataIsCached(): void
    {
        $first = $this->listener->getTranslatableMetadata(Article::class);

        $this->assertSame($first, $this->listener->getTranslatableMetadata(Article::class));
    }
}
