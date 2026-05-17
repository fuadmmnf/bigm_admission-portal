<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BangladeshLocationCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = require database_path('seeders/data/bangladesh_divisions.php');
        $districts = require database_path('seeders/data/bangladesh_districts.php');
        $upazilas = require database_path('seeders/data/bangladesh_upazilas.php');
        $thanas = require database_path('seeders/data/bangladesh_thanas.php');

        $districtsByDivision = [];
        foreach ($districts as $district) {
            $districtsByDivision[$district['division_source_id']][] = $district;
        }

        $upazilasByDistrict = [];
        foreach ($upazilas as $upazila) {
            $upazilasByDistrict[$upazila['district_source_id']][] = $upazila;
        }

        $thanasByDistrict = [];
        foreach ($thanas as $thana) {
            $thanasByDistrict[$thana['district_source_id']][] = $thana;
        }

        $bounds = [
            'division' => [],
            'district' => [],
            'upazila' => [],
            'thana' => [],
        ];

        $cursor = 1;
        foreach ($divisions as $division) {
            $divisionLeft = $cursor++;

            foreach ($districtsByDivision[$division['source_id']] ?? [] as $district) {
                $districtLeft = $cursor++;

                foreach ($upazilasByDistrict[$district['source_id']] ?? [] as $upazila) {
                    $upazilaLeft = $cursor++;
                    $bounds['upazila'][$upazila['source_id']] = [
                        'left' => $upazilaLeft,
                        'right' => $cursor++,
                    ];
                }

                foreach ($thanasByDistrict[$district['source_id']] ?? [] as $thana) {
                    $thanaLeft = $cursor++;
                    $bounds['thana'][$thana['source_id']] = [
                        'left' => $thanaLeft,
                        'right' => $cursor++,
                    ];
                }

                $bounds['district'][$district['source_id']] = [
                    'left' => $districtLeft,
                    'right' => $cursor++,
                ];
            }

            $bounds['division'][$division['source_id']] = [
                'left' => $divisionLeft,
                'right' => $cursor++,
            ];
        }

        DB::transaction(function () use ($divisions, $districts, $upazilas, $thanas, $bounds): void {
            $now = now();

            DB::table('categories')
                ->whereIn('type', ['division', 'district', 'upazila', 'thana'])
                ->delete();

            $divisionIds = [];
            foreach ($divisions as $division) {
                $divisionIds[$division['source_id']] = DB::table('categories')->insertGetId([
                    'ulid' => (string) Str::ulid(),
                    'name' => $division['name'],
                    'type' => 'division',
                    'additional_info' => json_encode([
                        'source_id' => $division['source_id'],
                        'bn_name' => $division['bn_name'],
                        'legacy_name' => $division['legacy_name'],
                        'url' => $division['url'],
                    ], JSON_UNESCAPED_UNICODE),
                    '_lft' => $bounds['division'][$division['source_id']]['left'],
                    '_rgt' => $bounds['division'][$division['source_id']]['right'],
                    'parent_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $districtIds = [];
            foreach ($districts as $district) {
                $districtIds[$district['source_id']] = DB::table('categories')->insertGetId([
                    'ulid' => (string) Str::ulid(),
                    'name' => $district['name'],
                    'type' => 'district',
                    'additional_info' => json_encode([
                        'source_id' => $district['source_id'],
                        'division_source_id' => $district['division_source_id'],
                        'bn_name' => $district['bn_name'],
                        'legacy_name' => $district['legacy_name'],
                        'latitude' => $district['latitude'],
                        'longitude' => $district['longitude'],
                        'url' => $district['url'],
                    ], JSON_UNESCAPED_UNICODE),
                    '_lft' => $bounds['district'][$district['source_id']]['left'],
                    '_rgt' => $bounds['district'][$district['source_id']]['right'],
                    'parent_id' => $divisionIds[$district['division_source_id']],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($upazilas as $upazila) {
                DB::table('categories')->insert([
                    'ulid' => (string) Str::ulid(),
                    'name' => $upazila['name'],
                    'type' => 'upazila',
                    'additional_info' => json_encode([
                        'source_id' => $upazila['source_id'],
                        'district_source_id' => $upazila['district_source_id'],
                        'bn_name' => $upazila['bn_name'],
                        'legacy_name' => $upazila['legacy_name'],
                        'url' => $upazila['url'],
                    ], JSON_UNESCAPED_UNICODE),
                    '_lft' => $bounds['upazila'][$upazila['source_id']]['left'],
                    '_rgt' => $bounds['upazila'][$upazila['source_id']]['right'],
                    'parent_id' => $districtIds[$upazila['district_source_id']],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($thanas as $thana) {
                DB::table('categories')->insert([
                    'ulid' => (string) Str::ulid(),
                    'name' => $thana['name'],
                    'type' => 'thana',
                    'additional_info' => json_encode([
                        'source_id' => $thana['source_id'],
                        'district_source_id' => $thana['district_source_id'],
                        'bn_name' => $thana['bn_name'],
                        'legacy_name' => $thana['legacy_name'],
                        'url' => $thana['url'],
                    ], JSON_UNESCAPED_UNICODE),
                    '_lft' => $bounds['thana'][$thana['source_id']]['left'],
                    '_rgt' => $bounds['thana'][$thana['source_id']]['right'],
                    'parent_id' => $districtIds[$thana['district_source_id']],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }
}
