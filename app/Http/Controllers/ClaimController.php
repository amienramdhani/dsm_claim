<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClaimController extends Controller
{
    public function index(Request $request)
    {
        $claims = DB::table('claims')->where('applicant_id', $request->user()->id)->latest()->paginate(10);
        $budgets = DB::table('claim_budgets')->where('user_id', $request->user()->id)->where('period_year', now()->year)->where('period_month', now()->month)->get();
        return view('claims.index', compact('claims', 'budgets'));
    }
    public function create() { return view('claims.create'); }
    public function store(Request $request)
    {
        $data = $request->validate(['category' => ['required', 'in:Transportasi,Entertain,Perdin,Operasional'], 'type' => ['required', 'string', 'max:60'], 'receipt_date' => ['required', 'date', 'before_or_equal:today'], 'total_amount' => ['required', 'integer', 'min:1'], 'note' => ['required', 'string', 'max:5000'], 'details' => ['nullable', 'array'], 'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120']]);
        $number = 'KLM-' . now()->format('ym') . '-' . str_pad((string) (DB::table('claims')->count() + 1), 5, '0', STR_PAD_LEFT);
        $claimId = DB::transaction(function () use ($request, $data, $number) {
            $claimId = DB::table('claims')->insertGetId(['number' => $number, 'applicant_id' => $request->user()->id, 'region' => $request->user()->region, 'business_entity' => $request->user()->business_entity, 'category' => $data['category'], 'type' => $data['type'], 'total_amount' => $data['total_amount'], 'status' => 'Menunggu Admin', 'note' => $data['note'], 'details' => json_encode($data['details'] ?? []), 'created_at' => now(), 'updated_at' => now()]);
            $file = $request->file('proof'); $path = $file->store('claims/' . $claimId, 'local');
            DB::table('claim_receipts')->insert(['claim_id' => $claimId, 'amount' => $data['total_amount'], 'receipt_date' => $data['receipt_date'], 'stored_path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(), 'size_bytes' => $file->getSize(), 'created_at' => now(), 'updated_at' => now()]);
            DB::table('claim_audit_entries')->insert(['claim_id' => $claimId, 'actor_id' => $request->user()->id, 'actor_role' => $request->user()->claim_role, 'action' => 'submitted', 'note' => 'Klaim dikirim untuk pemeriksaan Admin.', 'after_values' => json_encode(['status' => 'Menunggu Admin', 'number' => $number]), 'created_at' => now()]);
            return $claimId;
        });
        return redirect()->route('claims.show', $claimId)->with('success', 'Klaim berhasil dikirim untuk pemeriksaan Admin.');
    }
    public function show(Request $request, int $claim)
    {
        $claim = DB::table('claims')->where('id', $claim)->where('applicant_id', $request->user()->id)->firstOrFail(); $receipts = DB::table('claim_receipts')->where('claim_id', $claim->id)->get(); $audit = DB::table('claim_audit_entries')->where('claim_id', $claim->id)->orderBy('created_at')->get(); return view('claims.show', compact('claim', 'receipts', 'audit'));
    }
}
