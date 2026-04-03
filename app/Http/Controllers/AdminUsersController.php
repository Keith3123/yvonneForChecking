<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Services\UserManagementServiceInterface;
use App\Models\User;

class AdminUsersController extends AdminBaseController
{
    protected UserManagementServiceInterface $userService;

    public function __construct(UserManagementServiceInterface $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $user = session('admin_user');

        // Allow only master admin or users admin (roleID = 7)
        if (!$user || ($user['username'] !== 'masteradmin' && $user['roleID'] != 1)) {
            abort(403, 'Unauthorized');
        }

        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            $query->where('username', 'like', "%{$search}%")
                  ->orWhere('userID', 'like', "%{$search}%");
        })
        ->orderBy('userID', 'desc')
        ->paginate(10);

        $roles = \App\Models\Role::all();

        return view('admin.Users', compact('users', 'search', 'roles'));
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('user', 'username'),
            ],
            'password' => 'required|string|min:6|confirmed',
            'roleID'   => 'required|integer',
        ]);

        try {
            // Always hash the password before storing
            $hashedPassword = Hash::make($request->password);

            $user = $this->userService->createUser([
                'username' => $request->username,
                'password' => $hashedPassword,  // ✅ store hashed password
                'roleID'   => $request->roleID,
                'status'   => 1,                // default active
            ]);

            return redirect()->route('admin.users')
                ->with('success', 'Admin account created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create admin user: ' . $e->getMessage());
            return back()->withErrors(['msg' => 'Failed to create admin account.']);
        }
    }

    public function toggleStatus($userID)
    {
        $user = User::findOrFail($userID);


        if ($user->username === 'masteradmin') {
            return response()->json([
                'error' => 'Cannot deactivate master admin'
            ], 403);
        }

        $user->status = $user->status == 1 ? 0 : 1;
        $user->save();
    
        return response()->json(['status' => $user->status]);
    }
    
}