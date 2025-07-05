<?php

namespace Database\Seeders;

use App\Models\FeatureToggle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeatureToggleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'key' => 'contacts.can_create',
                'description' => 'Permite a criação de novos contatos',
                'message' => 'A criação de contatos está temporariamente desabilitada para manutenção.',
                'enabled' => true,
            ],
            [
                'key' => 'contacts.can_update',
                'description' => 'Permite a atualização de contatos existentes',
                'message' => 'A atualização de contatos está temporariamente desabilitada para manutenção.',
                'enabled' => true,
            ],
            [
                'key' => 'contacts.can_delete',
                'description' => 'Permite a exclusão de contatos',
                'message' => 'A exclusão de contatos está temporariamente desabilitada para manutenção.',
                'enabled' => true,
            ],
        ];

        foreach ($features as $feature) {
            FeatureToggle::updateOrCreate(
                ['key' => $feature['key']],
                $feature
            );
        }
    }
}
