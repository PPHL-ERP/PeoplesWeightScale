<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = file_get_contents(public_path('location/country.json'));
        $data = json_decode($json, true);

        foreach ($data['countries'] as $country) {
            DB::table('countries')->insert([
                'name'       => $country['name'],
                'code'       => $country['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
