<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CmsAdminSeeder::class,
            P3AdminSeeder::class,
            P4AdminSeeder::class,
            FeatureMenusSeeder::class,
            TestAdminSeeder::class,
            ProductionGapSeeder::class,
            EnterprisePortalSeeder::class,
            EnterprisePortalP1Seeder::class,
            EnterprisePortalP2Seeder::class,
            EnterprisePortalP3Seeder::class,
            EnterprisePortalP4Seeder::class,
            CmsApixmlDemoSeeder::class,
            CmsSiteSettingsMenuSeeder::class,
        ]);
    }
}
