<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingredient;

class AdminInventoryController extends AdminBaseController
{
    public function index()
    {   
        parent::__construct();
        // 🔒 Role-based access
        $user = session('admin_user');
        if (!$user || ($user['username'] !== 'masteradmin' && $user['roleID'] != 2)) {
            abort(403, 'Unauthorized');
        }

        // Fetch all ingredients to display
        $ingredients = Ingredient::all();

        return view('admin.inventory', compact('ingredients'));
    }

public function store(Request $request)
{
    $request->validate([
        'name'          => 'required',
        'description'   => 'required',
        'min_stock'     => 'required|integer',
        'current_stock' => 'required|integer'
    ]);

    Ingredient::create([
        'name'          => $request->name,
        'description'   => $request->description,
        'minStockLevel' => $request->min_stock,
        'currentStock'  => $request->current_stock,
    ]);

    return redirect()->route('admin.inventory')->with('success', 'Ingredient Added!');
}

public function update(Request $request, Ingredient $ingredient)
{
    $request->validate([
        'name'          => 'required',
        'description'   => 'required',
        'min_stock'     => 'required|integer',
        'current_stock' => 'required|integer'
    ]);

    $ingredient->update([
        'name'          => $request->name,
        'description'   => $request->description,
        'minStockLevel' => $request->min_stock,
        'currentStock'  => $request->current_stock,
    ]);

    return redirect()->route('admin.inventory')->with('success', 'Ingredient Updated!');
}

}