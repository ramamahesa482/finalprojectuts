<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PatientsStatus;

class PatientsStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'status' => 1,
                'name' => 'positif',
                'created_at' => date('Y-m-d H:i:s.Z', time()),
                'updated_at' => date('Y-m-d H:i:s.Z', time()),
            ],
            [
                'status' => 2,
                'name' => 'sembuh',
                'created_at' => date('Y-m-d H:i:s.Z', time()),
                'updated_at' => date('Y-m-d H:i:s.Z', time()),
            ],
            [
                'status' => 3,
                'name' => 'meninggal',
                'created_at' => date('Y-m-d H:i:s.Z', time()),
                'updated_at' => date('Y-m-d H:i:s.Z', time()),
            ],
        ];
        PatientsStatus::insert($data);
    }
}
