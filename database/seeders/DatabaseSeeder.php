<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(['username' => 'demo.sales'], ['name' => 'Rina Pratama', 'email' => 'demo.sales@example.test', 'pin_hash' => Hash::make('123456'), 'password' => Hash::make('not-used'), 'claim_role' => 'Pemohon', 'region' => 'Jakarta Selatan', 'business_entity' => 'DSM Example', 'is_active' => true]);
        foreach (['Transportasi' => 4500000, 'Entertain' => 2500000, 'Perdin' => 6000000, 'Operasional' => 1800000] as $category => $amount) {
            DB::table('claim_budgets')->updateOrInsert(['user_id' => $user->id, 'category' => $category, 'period_year' => now()->year, 'period_month' => now()->month], ['amount' => $amount, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
