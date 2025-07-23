<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class UpazilaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = file_get_contents(public_path('location/bd-upazilas.json'));
        $data = json_decode($json, true);

        foreach ($data['upazilas'] as $upazila) {
            DB::table('upazilas')->insert([
                'district_id' => $upazila['district_id'],
                'name'        => $upazila['name'],
                'bn_name'     => $upazila['bn_name'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
