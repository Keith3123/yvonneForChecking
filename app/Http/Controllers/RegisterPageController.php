<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DTO\CustomerDTO;
use App\Services\CustomerService;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;

class RegisterPageController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function show()
    {
        return view('user.RegisterPage');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName'  => 'required|string|max:255',
            'mi'        => 'nullable|string|max:1',
            'phone'     => 'required|digits:11',
            'email'     => 'required|email|unique:customer,email',
            'address'   => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:customer,username',
            'password'  => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $customerDTO = new CustomerDTO($request->all());
        $this->customerService->register($customerDTO);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account created successfully!'
            ]);
        }
        
        return redirect()->route('login')->with('success', 'Account created successfully!');
    }

    public function checkUsername(Request $request)
    {
        $exists = Customer::where('username', $request->username)->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }

    public function checkEmail(Request $request)
    {
        $exists = Customer::where('email', $request->email)->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }
}