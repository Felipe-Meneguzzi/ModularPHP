<?php

use Phinx\Seed\AbstractSeed;

class UsersSeeder extends AbstractSeed
{   
    public function getDependencies(): array
    {
        return [
            'UserTypeSeeder',
            'CompaniesSeeder',
            'BuildingsSeeder'
        ];
    }
    
    public function run(): void
    {   
        $data = [
            [
                'uuid' => 'c255f364-50f7-11f0-92f8-4af298741893',
                'name' => 'Administrador',
                'login' => 'admin',
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'email' => 'admin@gmail.com',
                'phone' => null,
                'user_type_uuid' => 'c255f364-50f7-11f0-92f8-4af298741892',
                'cpf' => '04739633027'
            ],
            [
                'uuid' => '8e7b201e-3d53-42fc-ba28-ccd75f0072a6',
                'name' => 'Joao',
                'login' => 'jvm', 
                'password' => password_hash('joao123', PASSWORD_DEFAULT),
                'email' => 'joao@bing.com',
                'phone' => '5511991530983',
                'user_type_uuid' => '8e7b201e-3d53-42fc-ba28-ccd75f0072a6',
                'cpf' => '73904317091'
            ],
            [
                'uuid' => '8e7b201e-3d53-42fc-ba28-ccd75f0072a7',
                'name' => 'Encanador José',
                'login' => 'jose', 
                'password' => password_hash('encanador123', PASSWORD_DEFAULT),
                'email' => 'joseencanador@bol.com',
                'phone' => '5511996334733',
                'user_type_uuid' => '8e7b201e-3d53-42fc-ba28-ccd75f0072a6',
                'cpf' => '83629484000'
            ]
        ];

        $this->table('users')->insert($data)->saveData();
    }
}