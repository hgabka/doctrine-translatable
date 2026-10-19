# hgabka/doctrine-translatable

Doctrine ORM kiegészítés fordítható entitásokhoz. Az elv: egy ún. *translatable* entitás tárolja a nyelvfüggetlen adatokat, a nyelvfüggő mezők pedig egy külön *translation* entitásba kerülnek (soronként egy nyelv). A kapcsolatokat (OneToMany / ManyToOne), a `locale` mezőt és az egyedi indexet (`translatable_id` + `locale`) a könyvtár automatikusan felmapeli a `loadClassMetadata` eseményen, így az entitásokban nem kell kézzel megírni őket.

Symfony integrációhoz használd a [`hgabka/doctrine-translatable-bundle`](../doctrine-translatable-bundle) csomagot.

## Telepítés

```bash
composer require hgabka/doctrine-translatable
```

Követelmények: PHP ≥ 8.0.3, `doctrine/persistence` ^4, `doctrine/collections` ^2, `doctrine/common` ^3, `jms/metadata`. Namespace: `Hgabka\Doctrine\Translatable` (PSR-0, `lib/`).

## Használat

### Translatable entitás

```php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hgabka\Doctrine\Translatable\Annotation as Hgabka;
use Hgabka\Doctrine\Translatable\Entity\TranslatableTrait;
use Hgabka\Doctrine\Translatable\TranslatableInterface;

#[ORM\Entity]
class Category implements TranslatableInterface
{
    use TranslatableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[Hgabka\Translations(targetEntity: CategoryTranslation::class)]
    private Collection|array|null $translations = null;

    #[Hgabka\CurrentLocale]
    private ?string $currentLocale = null;

    #[Hgabka\FallbackLocale]
    private ?string $fallbackLocale = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }
}
```

Az `AbstractTranslatable` ősosztály ugyanezt a `getTranslations()/addTranslation()/removeTranslation()` készletet adja trait helyett.

### Translation entitás

```php
use Hgabka\Doctrine\Translatable\Entity\TranslationTrait;
use Hgabka\Doctrine\Translatable\TranslationInterface;

#[ORM\Entity]
class CategoryTranslation implements TranslationInterface
{
    use TranslationTrait;   // id, locale, translatable getter/setter + isEmpty()

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $title = null;

    #[Hgabka\Translatable(targetEntity: Category::class)]
    private ?TranslatableInterface $translatable = null;
}
```

Az `AbstractTranslation` ősosztály a trait megfelelője. A `TranslationTrait::isEmpty()` igaz, ha az `id`/`locale`/`translatable` mezőkön kívül minden mező üres – ezt használják az űrlapkezelők (pl. a2lix) az üres, nem kötelező nyelvek eldobására.

### Attribútumok

| Attribútum | Hol | Szerep |
|---|---|---|
| `#[Translations(targetEntity)]` | translatable entitás | a fordításokat tartalmazó gyűjtemény |
| `#[Translatable(targetEntity, referencedColumnName='id')]` | translation entitás | vissza a translatable entitásra (ManyToOne, `onDelete: CASCADE`) |
| `#[Locale]` | translation entitás | a nyelvkód mező (alapból `string(5)`) |
| `#[CurrentLocale]` | translatable entitás | betöltéskor ide kerül az aktuális nyelv |
| `#[FallbackLocale]` | translatable entitás | betöltéskor ide kerül a tartalék nyelv |

Annotation (docblock) és XML/YAML mapping driver is van (`Mapping\Driver\*`), de az attribútum (`AttributeDriver`) az elsődleges.

### TranslatableListener

`EventListener\TranslatableListener` (`#[AsDoctrineListener]` a `loadClassMetadata` és `postLoad` eseményre):

- `loadClassMetadata` – a fenti mapping kiegészítések (OneToMany `FETCH_EXTRA_LAZY`, `indexBy` a locale mezőre, `cascade: persist, remove`, `orphanRemoval`).
- `postLoad` – beállítja az entitás `currentLocale` / `fallbackLocale` mezőjét.
- `setCurrentLocale()` / `setFallbackLocale()` (alapérték mindkettőnél `en`) – Symfonyban a bundle állítja a requestből.
- `getTranslatableMetadata($class)` – a `jms/metadata` alapú metaadat (cache-elve).
