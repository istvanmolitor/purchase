<?php

namespace Molitor\Purchase\database\seeders;

use Illuminate\Database\Seeder;
use Molitor\Purchase\Models\PurchaseExtraItemType;

class PurchaseExtraItemTypeSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $itemTypes = [
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

        foreach ($itemTypes as $itemType) {
            PurchaseExtraItemType::query()->updateOrCreate(
                ['name' => $itemType['name']],
                ['description' => $itemType['description']]
            );
        }
    }
}


