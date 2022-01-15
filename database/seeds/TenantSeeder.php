<?php

use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'name' => 'Tenant 1',
                'email' => 'abc@abc.com',
                'slug' => 'tenant-1',
                'host' => '127.0.0.1',
                'password'=> 'password',
                'username' => 'root',
                'database' => 'tenant_1'
            ],
            
        ];
        foreach ($items as $index => $item) {
            App\Models\V1\Tenant\Tenant::updateOrCreate([
                'id' => $index
            ], $item);
        }
    }
}
