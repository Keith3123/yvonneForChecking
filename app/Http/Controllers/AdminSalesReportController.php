<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminSalesReportController extends AdminBaseController
{
    public function index()
    {   
        parent::__construct();
        // 🔒 Role-based access
        $user = session('admin_user');
        if (!$user || ($user['username'] !== 'masteradmin' && $user['roleID'] != 5)) {
            abort(403, 'Unauthorized');
        }

        return view('admin.salesreport');
    }
}
