<?php

namespace Molitor\Purchase\database\seeders;

use Illuminate\Database\Seeder;
use Molitor\Purchase\Models\PurchaseStatus;
use Molitor\User\Exceptions\PermissionException;
use Molitor\User\Services\AclManagementService;

class PurchaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        try {
            /** @var AclManagementService $aclService */
            $aclService = app(AclManagementService::class);
            $aclService->createPermission('purchase', 'Beszerzések kezelése', 'admin');
            $aclService->createPermission('purchase_status', 'Beszerzési állapotok kezelése', 'admin');
        } catch (PermissionException $e) {
            $this->command->error($e->getMessage());
        }

        PurchaseStatus::create([
            'name' => 'Tervezett',
            'state' => 0,
            'description' => 'A beszerzés tervezett állapotban van.',
        ]);

        PurchaseStatus::create([
            'name' => 'Folyamatban',
            'state' => 1,
            'description' => 'A beszerzés folyamatban van.',
        ]);

        PurchaseStatus::create([
            'name' => 'Késik',
            'state' => 1,
            'description' => 'A beszerzés késik a tervezett szállítási időponthoz képest.',
        ]);

        PurchaseStatus::create([
            'name' => 'Vámkezelés alatt',
            'state' => 1,
            'description' => 'A megrendelés vámkezelés alatt van.',
        ]);

        PurchaseStatus::create([
            'name' => 'Teljesített',
            'state' => 2,
            'description' => 'A beszerzés teljesítve lett.',
        ]);

        PurchaseStatus::create([
            'name' => 'Visszamondott',
            'state' => 3,
            'description' => 'A beszerzés vissza lett mondva mielőtt megérkezett volna.',
        ]);

        PurchaseStatus::create([
            'name' => 'Sérült',
            'state' => 3,
            'description' => 'A beszerzés sérült állapotban érkezett meg.',
        ]);
    }
}
