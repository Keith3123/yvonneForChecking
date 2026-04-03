<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\UserManagementServiceInterface;

class AdminBaseController extends Controller
{
    public function __construct()
{
    $userSession = session('admin_user');

    if (!$userSession) {
        redirect('/login')->send();
        exit;
    }

    // ✅ Skip DB check for masteradmin
    if ($userSession['username'] === 'masteradmin') {
        return;
    }

    // ✅ Now safe to check userID
    if (!isset($userSession['userID'])) {
        session()->forget('admin_user');
        redirect('/login')->send();
        exit;
    }

    $dbUser = User::find($userSession['userID']);

    if (!$dbUser || $dbUser->status == 0) {
        session()->forget('admin_user');
        redirect('/login')->send();
        exit;
    }
}
}
