<?php

namespace Molitor\Purchase\database\seeders;

use Illuminate\Database\Seeder;
use Molitor\Purchase\Models\PurchaseExtraItem;

class PurchaseExtraItemSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'name' => 'Szállítási költség',
                'description' => 'A beszállító által felszámított szállítás díja.',
            ],
            [
                'name' => 'Vám',
                'description' => 'Import beszerzésekhez kapcsolódó vámteher.',
            ],
            [
                'name' => 'Vámkezelési díj',
                'description' => 'A vámeljárás adminisztrációs és ügyintézési díja.',
            ],
            [
                'name' => 'Csomagolási díj',
                'description' => 'Külön felszámított csomagolási költség.',
            ],
            [
                'name' => 'Biztosítás',
                'description' => 'Szállítmány biztosításának költsége.',
            ],
        ];

        foreach ($items as $item) {
            PurchaseExtraItem::query()->updateOrCreate(
                ['name' => $item['name']],
                ['description' => $item['description']]
            );
        }
    }
}

