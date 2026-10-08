<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use Illuminate\View\View;

class LoginAuditController extends Controller
{
    public function index(): View
    {
        $audits = LoginAudit::with('user')
            ->latest('login_at')
            ->paginate(15);

        return view('admin.audits.index', compact('audits'));
    }
}
