PHP Addition Repository - Enum
==============================

This package offers strongly types enums in PHP. We don't use a simple "value" representation, so you're always working with the enum object. This allows for proper autocompletion and refactoring in IDEs.

Here's how enums are created with this package

```php

use Par\Enum\Enum;

/**
 * @method static self draft()
 * @method static self published()
 * @method static self archived() 
 */
abstract class PageStatus extends Enum 
{
    
}
```
And this is how they are used:

```php
public function setStatus(PageStatus $status): void
{
    $this->status = $status;
}

// ...

$class->setStatus(PageStatus::draft());
```

Installation
------------

```bash
composer require par/enum
```

Usage
-----

### Creating an enum from a value

```php
$status = PageStatus::valueOf('draft');
```


### Comparing enums

Enums can be compared using the `equals` method:

```php
$status->equals($otherStatus);
```

### Retrieve a list of possible values

```php
$states = array_map(
    static function (PageStatus $status) { 
        return $status->name(); 
    }, 
    PageStatus::values()
);
// ['pending', 'published', 'archived'];
```

### Enum values

```php
$status = PageStatus::published();
$name = $status->name(); // Name as declared, 'published'
$ordinal = $status->ordinal(); // Position as declared, 1
```

### Custom logic

Adding custom logic to your enum can be easily accomplished.

```php
/**
 * @method static self draft()
 * @method static self published()
 * @method static self archived() 
 */
abstract class PageStatus extends Enum 
{
    public function translationKey(): string
    {
        switch($this->name()) {
            case 'draft':
                $key = 'page_draft';
                break;
            case 'published':
                $key = 'page_published';
                break;
            default:
                $key = 'page_archived';
                break;
        }    
        
        return $key;
    }
}