<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = file_get_contents(public_path('location/bd-divisions.json'));
        $data = json_decode($json, true);

        foreach ($data['divisions'] as $division) {
            DB::table('divisions')->insert([
                'name'       => $division['name'],
                'bn_name'    => $division['bn_name'],
                'lat'        => $division['lat'],
                'long'       => $division['long'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
