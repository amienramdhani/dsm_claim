<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClaimFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_pin(): void
    {
        User::create(['name' => 'Demo', 'email' => 'demo@test', 'username' => 'demo', 'pin_hash' => Hash::make('123456'), 'password' => Hash::make('unused'), 'claim_role' => 'Pemohon', 'is_active' => true]);
        $response = $this->post('/login', ['username' => 'demo', 'pin' => '123456']);
        $response->assertRedirect('/claims');
        $this->assertAuthenticated();
    }

    public function test_claim_submission_validates_and_writes_audit_entry(): void
    {
        Storage::fake('local');
        $user = User::create(['name' => 'Demo', 'email' => 'demo@test', 'username' => 'demo', 'pin_hash' => Hash::make('123456'), 'password' => Hash::make('unused'), 'claim_role' => 'Pemohon', 'is_active' => true]);
        $this->actingAs($user);
        $response = $this->post('/claims', ['category' => 'Transportasi', 'type' => 'BBM', 'receipt_date' => now()->toDateString(), 'total_amount' => 125000, 'note' => 'Kunjungan pelanggan', 'proof' => UploadedFile::fake()->create('nota.pdf', 100, 'application/pdf')]);
        $response->assertRedirect();
        $this->assertDatabaseHas('claims', ['applicant_id' => $user->id, 'status' => 'Menunggu Admin', 'total_amount' => 125000]);
        $this->assertDatabaseHas('claim_audit_entries', ['actor_id' => $user->id, 'action' => 'submitted']);
        $claimId = DB::table('claims')->where('applicant_id', $user->id)->value('id');
        $storedPath = DB::table('claim_receipts')->where('claim_id', $claimId)->value('stored_path');
        Storage::disk('local')->assertExists($storedPath);
    }
}
