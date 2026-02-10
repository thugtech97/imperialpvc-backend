<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use OwenIt\Auditing\Models\Audit;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $audits = Audit::with('user')
            ->when($search, function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                  ->orWhere('auditable_type', 'like', "%{$search}%")
                  ->orWhere('auditable_id', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 10));

        return response()->json($audits);
    }
}
