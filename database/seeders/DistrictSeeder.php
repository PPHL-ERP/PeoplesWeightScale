<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = file_get_contents(public_path('location/bd-districts.json'));
        $data = json_decode($json, true);

        foreach ($data['districts'] as $district) {
            DB::table('districts')->insert([
                'division_id' => $district['division_id'],
                'name'        => $district['name'],
                'bn_name'     => $district['bn_name'],
                'lat'         => $district['lat'],
                'long'        => $district['long'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
