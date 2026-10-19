<?php

namespace Hgabka\Doctrine\Translatable\Tests\Entity;

use Hgabka\Doctrine\Translatable\Tests\Fixtures\Article;
use Hgabka\Doctrine\Translatable\Tests\Fixtures\ArticleTranslation;
use PHPUnit\Framework\TestCase;

class TranslationTest extends TestCase
{
    private function translation(string $locale): ArticleTranslation
    {
        return (new ArticleTranslation())->setLocale($locale);
    }

    public function testAddTranslationIndexesByLocaleAndSetsOwner(): void
    {
        $article = new Article();
        $hu = $this->translation('hu');

        $article->addTranslation($hu);

        $this->assertSame($hu, $article->getTranslations()->get('hu'));
        $this->assertSame($article, $hu->getTranslatable());
    }

    public function testAddingTheSameTranslationTwiceKeepsOneEntry(): void
    {
        $article = new Article();
        $hu = $this->translation('hu');

        $article->addTranslation($hu)->addTranslation($hu);

        $this->assertCount(1, $article->getTranslations());
    }

    public function testRemoveTranslationDetachesOwner(): void
    {
        $article = new Article();
        $hu = $this->translation('hu');
        $article->addTranslation($hu);

        $article->removeTranslation($hu);

        $this->assertCount(0, $article->getTranslations());
        $this->assertNull($hu->getTranslatable());
    }

    public function testSetTranslatableMovesTranslationBetweenOwners(): void
    {
        $first = new Article();
        $second = new Article();
        $en = $this->translation('en');

        $en->setTranslatable($first);
        $en->setTranslatable($second);

        $this->assertCount(0, $first->getTranslations());
        $this->assertSame($en, $second->getTranslations()->get('en'));
        $this->assertSame($second, $en->getTranslatable());
    }

    public function testSettersAreFluent(): void
    {
        $translation = new ArticleTranslation();

        $this->assertSame($translation, $translation->setLocale('hu'));
        $this->assertSame('hu', $translation->getLocale());
        $this->assertNull($translation->getId());
    }

    public function testFreshTranslationIsEmpty(): void
    {
        $this->assertTrue($this->translation('hu')->isEmpty());
    }

    public function testTranslationWithContentIsNotEmpty(): void
    {
        $this->assertFalse($this->translation('hu')->setTitle('Cím')->isEmpty());
        $this->assertFalse($this->translation('hu')->setTags(['a'])->isEmpty());
    }

    public function testBlankValuesCountAsEmpty(): void
    {
        $translation = $this->translation('hu')->setTitle('')->setTags([]);

        $this->assertTrue($translation->isEmpty());
    }
}
