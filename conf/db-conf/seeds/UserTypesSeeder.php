<?php

declare(strict_types = 1);

use Phinx\Seed\AbstractSeed;

class UserTypesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'uuid' => 'c255f364-50f7-11f0-92f8-4af298741892',
                'name' => 'Admin'
            ],
            [
                'uuid' => '8e7b201e-3d53-42fc-ba28-ccd75f0072a6',
                'name' => 'User'
            ]
        ];

        $this->table('user_types')->insert($data)->saveData();
    }
}
