<?php

use Illuminate\Database\Seeder;
use App\Models\V1\Masters\LeaveType;

class TenantDatabaseSeeder extends Seeder
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
                'name' => 'Health Issues'
            ],
            [
                'name' => 'Family Function'
            ],
            [
                'name' => 'Birthday'
            ]
        ];
        foreach ($items as $index => $item) {
            LeaveType::updateOrCreate($item);
        }
    }
}
