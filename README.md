# Purchase

## Seeder regisztrálása

A jogosultságok és kezdeti adatok beállításához regisztráld a seedert a `database/seeders/DatabaseSeeder.php` fájlban:

```php
use Molitor\Purchase\database\seeders\PurchaseSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PurchaseSeeder::class,
        ]);
    }
}
```
