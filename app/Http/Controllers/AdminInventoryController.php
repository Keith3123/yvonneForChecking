<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingredient;
use App\Models\Supplier;
use App\Models\DeliveryReceipt;
use App\Models\DeliveryReceiptDetail;
use App\Models\PullOut;
use App\Models\PullOutDetail;

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
         $ingredients = Ingredient::all()->map(function ($ingredient) {
            $ingredient->totalIn  = DeliveryReceiptDetail::where('ingredientID', $ingredient->ingredientID)->sum('qtyDelivered');
            $ingredient->totalOut = PullOutDetail::where('ingredientID', $ingredient->ingredientID)->sum('qtyPulled');
            return $ingredient;
        });

         $stockIn = DeliveryReceipt::with(['details.ingredient', 'supplier'])->get()->flatMap(function ($dr) {
            // Look up username from user table
            $receivedByUser = \DB::table('user')->where('userID', $dr->receivedBy)->first();
            $receivedByName = $receivedByUser ? $receivedByUser->username : 'Unknown';

            return $dr->details->map(function ($detail) use ($dr, $receivedByName) {
                return [
                    'date'       => $dr->drDate,
                    'type'       => 'in',
                    'ingredient' => $detail->ingredient->name ?? '—',
                    'qty'        => $detail->qtyDelivered,
                    'by'         => $receivedByName,
                    'remarks'    => $dr->remarks ?? 'Supplier delivery',
                ];
            });
        });

        $stockOut = PullOut::with(['details.ingredient'])->get()->flatMap(function ($po) {
            // Look up username from user table
            $pulledByUser = \DB::table('user')->where('userID', $po->pullOutBy)->first();
            $pulledByName = $pulledByUser ? $pulledByUser->username : 'Unknown';

            return $po->details->map(function ($detail) use ($po, $pulledByName) {
                return [
                    'date'       => $po->pullDate,
                    'type'       => 'out',
                    'ingredient' => $detail->ingredient->name ?? '—',
                    'qty'        => $detail->qtyPulled,
                    'by'         => $pulledByName,
                    'remarks'    => $po->remarks ?? '—',
                ];
            });
        });

        $transactions  = $stockIn->concat($stockOut)->sortByDesc('date')->values();
        $totalStockIn  = DeliveryReceiptDetail::sum('qtyDelivered');
        $totalStockOut = PullOutDetail::sum('qtyPulled');
        $suppliers     = Supplier::all();

        return view('admin.inventory', compact(
            'ingredients',
            'transactions',
            'totalStockIn',
            'totalStockOut',
            'suppliers'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required',
            'description' => 'required',
            'unit'        => 'required',
            'min_stock'   => 'required|integer',
        ]);

        Ingredient::create([
            'name'          => $request->name,
            'description'   => $request->description,
            'unit'          => $request->unit,
            'minStockLevel' => $request->min_stock,
            'currentStock'  => 0,
        ]);

        return redirect()->route('admin.inventory')->with('success', 'Ingredient added!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required',
            'description' => 'required',
            'unit'        => 'required',
            'min_stock'   => 'required|numeric',
        ]);

        Ingredient::where('ingredientID', $id)->update([
            'name'          => $request->name,
            'description'   => $request->description,
            'unit'          => $request->unit,
            'minStockLevel' => (int) $request->min_stock,
        ]);

        return redirect()->route('admin.inventory')->with('success', 'Ingredient updated!');
    }

    public function destroy($id)
    {
        $ingredient = Ingredient::where('ingredientID', $id)->firstOrFail();

        // Check if ingredient has transaction history
        $hasTransactions = DeliveryReceiptDetail::where('ingredientID', $id)->exists() ||
                        PullOutDetail::where('ingredientID', $id)->exists();

        if ($hasTransactions) {
            return redirect()->route('admin.inventory')->with('error', 'Cannot delete ingredient with existing transaction history.');
        }

        $ingredient->delete();

        return redirect()->route('admin.inventory')->with('success', 'Ingredient deleted.');
    }

    public function receive(Request $request)
    {
        try {
            $request->validate([
                'supplier'              => 'required',
                'items'                 => 'required|array|min:1',
                'items.*.ingredient_id' => 'required|exists:ingredient,ingredientID',
                'items.*.qty'           => 'required|numeric|min:0.01',
                'items.*.unit_cost'     => 'required|numeric|min:0',
                'items.*.expiry_date'   => 'required|date',
            ]);

            // Look up userID from the user table using session username
            $adminUser = \DB::table('user')
                ->where('username', session('admin_user')['username'])
                ->first();
            $receivedBy = $adminUser ? $adminUser->userID : 1;

            $dr = DeliveryReceipt::create([
                'supplierID' => $request->supplier,
                'receivedBy' => $receivedBy,
                'drDate'     => now(),
                'remarks'    => $request->remarks ?? 'Supplier delivery',
            ]);

            foreach ($request->items as $item) {
                DeliveryReceiptDetail::create([
                    'drID'         => $dr->drID,
                    'ingredientID' => $item['ingredient_id'],
                    'qtyDelivered' => $item['qty'],
                    'unitCost'     => $item['unit_cost'],
                    'expiryDate'   => $item['expiry_date'],
                ]);

                Ingredient::where('ingredientID', $item['ingredient_id'])
                    ->increment('currentStock', $item['qty']);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function pullout(Request $request)
    {
        try {
            $request->validate([
                'items'                 => 'required|array|min:1',
                'items.*.ingredient_id' => 'required|exists:ingredient,ingredientID',
                'items.*.qty'           => 'required|numeric|min:0.01',
            ]);

            // Look up userID from the user table using session username
            $adminUser = \DB::table('user')
                ->where('username', session('admin_user')['username'])
                ->first();
            $pullOutBy = $adminUser ? $adminUser->userID : 1;

            $po = PullOut::create([
                'prepID'    => null,
                'pullOutBy' => $pullOutBy,
                'pullDate'  => now(),
                'pullType'  => $request->pull_type,
                'remarks'   => $request->remarks,
            ]);

            foreach ($request->items as $item) {
                $ingredient = Ingredient::where('ingredientID', $item['ingredient_id'])->firstOrFail();

                if ($ingredient->currentStock < $item['qty']) {
                    $po->delete();
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$ingredient->name}. Available: {$ingredient->currentStock} {$ingredient->unit}"
                    ]);
                }

                PullOutDetail::create([
                    'pullOutID'    => $po->pullOutID,
                    'ingredientID' => $item['ingredient_id'],
                    'qtyPulled'    => $item['qty'],
                ]);

                $ingredient->decrement('currentStock', $item['qty']);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeSupplier(Request $request)
    {
        $request->validate([
            'supplierName' => 'required|string',
            'phone'        => 'required|string',
        ]);

        Supplier::create([
            'supplierName' => $request->supplierName,
            'phone'        => $request->phone,
        ]);

        return redirect()->route('admin.inventory')->with('success', 'Supplier added!');
    }

    public function updateSupplier(Request $request, $id)
    {
        $request->validate([
            'supplierName' => 'required|string',
            'phone'        => 'required|string',
        ]);

        Supplier::where('supplierID', $id)->update([
            'supplierName' => $request->supplierName,
            'phone'        => $request->phone,
        ]);

        return redirect()->route('admin.inventory')->with('success', 'Supplier updated!');
    }
}