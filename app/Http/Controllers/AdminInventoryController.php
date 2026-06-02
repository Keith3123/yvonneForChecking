<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    $user = session('admin_user');
    if (!$user || ($user['username'] !== 'masteradmin' && $user['roleID'] != 2)) {
        abort(403, 'Unauthorized');
    }

    $ingredients = Ingredient::all()->map(function ($ingredient) {
        $totalIn  = DeliveryReceiptDetail::where('ingredientID', $ingredient->ingredientID)->sum('qtyDelivered') ?? 0;
        $totalOut = PullOutDetail::where('ingredientID', $ingredient->ingredientID)->sum('qtyPulled') ?? 0;

        // Derived available — this is the source of truth
        $computedStock = max(0, $totalIn - $totalOut);

        // Silently sync currentStock if it drifted (old data, manual DB edits, etc.)
        if ((float) $ingredient->currentStock !== (float) $computedStock) {
            Ingredient::where('ingredientID', $ingredient->ingredientID)
                ->update(['currentStock' => $computedStock]);
            $ingredient->currentStock = $computedStock;
        }

        $ingredient->totalIn  = $totalIn;
        $ingredient->totalOut = $totalOut;

        // Nearest UPCOMING expiry date for this ingredient
        $ingredient->nearestExpiry = DeliveryReceiptDetail::where('ingredientID', $ingredient->ingredientID)
            ->whereNotNull('expiryDate')
            ->where('expiryDate', '>', today()->endOfDay())  // strictly after today
            ->orderBy('expiryDate')
            ->value('expiryDate');

        return $ingredient;
    });

    $stockIn = DeliveryReceipt::with(['details.ingredient', 'supplier'])->get()->flatMap(function ($dr) {
        $receivedByUser = DB::table('user')->where('userID', $dr->receivedBy)->first();
        $receivedByName = $receivedByUser ? $receivedByUser->username : 'masteradmin';
        return $dr->details->map(function ($detail) use ($dr, $receivedByName) {
            return [
                'date'       => $dr->drDate,
                'type'    => ($detail->qtyDelivered < 0) ? 'out' : 'in',

                'ingredient' => $detail->ingredient->name ?? '—',
'qty'     => abs($detail->qtyDelivered),
                'by'         => $receivedByName,
                'remarks'    => $dr->remarks ?? 'Supplier delivery',
            ];
        });
    });

    $stockOut = PullOut::with(['details.ingredient'])->get()->flatMap(function ($po) {
        $pulledByUser = DB::table('user')->where('userID', $po->pullOutBy)->first();
        $pulledByName = $pulledByUser ? $pulledByUser->username : 'masteradmin';
        return $po->details->map(function ($detail) use ($po, $pulledByName) {
            return [
                'date'       => $po->pullDate,
                'type'       => 'out',
                'ingredient' => $detail->ingredient->name ?? '—',
                'qty'        => $detail->qtyPulled,
                'by'         => $pulledByName,
                'remarks'    => $po->pullType ?? $po->remarks ?? '—',
            ];
        });
    });

    $transactions  = $stockIn->concat($stockOut)->sortByDesc('date')->values();
    $totalStockIn  = DeliveryReceiptDetail::sum('qtyDelivered');
    $totalStockOut = PullOutDetail::sum('qtyPulled');
    $suppliers     = Supplier::all();

    $adminUsers = DB::table('user')
        ->whereIn('roleID', [1, 2])
        ->select('userID', 'username')
        ->orderBy('username')
        ->get();

    return view('admin.inventory', compact(
        'ingredients', 'transactions', 'totalStockIn', 'totalStockOut', 'suppliers', 'adminUsers'
    ));
}

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required',
            'unit'        => 'required',
            'min_stock'   => 'required|numeric|min:0',
        ]);

        Ingredient::create([
            'name'          => $request->name,
            'unit'          => $request->unit,
            'minStockLevel' => $request->min_stock,
            'currentStock'  => 0,
        ]);

        return redirect()->route('admin.inventory')->with('success', 'Ingredient added!');
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name'        => 'required',
                'unit'        => 'required',
                'min_stock'   => 'required|numeric|min:0',
            ]);

            $ingredient = Ingredient::where('ingredientID', $id)->firstOrFail();
            $ingredient->update([
                'name'          => $request->name,
                'unit'          => $request->unit,
                'minStockLevel' => $request->min_stock,
            ]);

            return response()->json(['success' => true, 'message' => 'Ingredient updated!']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $ingredient = Ingredient::where('ingredientID', $id)->firstOrFail();

            $hasTransactions = DeliveryReceiptDetail::where('ingredientID', $id)->exists()
                            || PullOutDetail::where('ingredientID', $id)->exists();

            if ($hasTransactions) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete: ingredient has existing transaction history.'
                ]);
            }

            $ingredient->delete(); // SoftDeletes sets deleted_at

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function receive(Request $request)
{
    try {
        $isManual = $request->receive_type === 'Manual Adjustment';

        $rules = [
            'received_by'           => 'required|exists:user,userID',
            'items'                 => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredient,ingredientID',
            'items.*.qty'           => $isManual ? 'required|numeric|not_in:0' : 'required|numeric|min:0.01',
            'items.*.unit_cost'     => 'required|numeric|min:0',
            'items.*.expiry_date'   => $isManual ? 'nullable|date' : 'required|date',
        ];

        if (!$isManual) {
            $rules['supplier'] = 'required';
        }

        $request->validate($rules);

        $dr = DeliveryReceipt::create([
            'supplierID' => $isManual ? null : $request->supplier,
            'receivedBy' => $request->received_by,
            'drDate'     => now(),
            'remarks'    => $request->receive_type ?? 'Supplier Delivery',
        ]);

        foreach ($request->items as $item) {
            $qty = (float) $item['qty'];

            // Block negative adjustment that would go below zero
    if ($isManual && $qty < 0) {
        $ingredient = Ingredient::find($item['ingredient_id']);
        if ($ingredient && $ingredient->currentStock + $qty < 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot remove {$qty} from {$ingredient->name}. Available: {$ingredient->currentStock} {$ingredient->unit}"
            ]);
        }
    }

            DeliveryReceiptDetail::create([
                'drID'         => $dr->drID,
                'ingredientID' => $item['ingredient_id'],
                'qtyDelivered' => $item['qty'],
                'unitCost'     => $item['unit_cost'] ?? 0,
                'expiryDate'   => $item['expiry_date'] ?? null,
            ]);
            Ingredient::where('ingredientID', $item['ingredient_id'])
                ->increment('currentStock', $qty);
        }

        return response()->json(['success' => true]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    public function pullout(Request $request)
    {
        try {
            $request->validate([
                'pulled_by'             => 'required|exists:user,userID',
                'items'                 => 'required|array|min:1',
                'items.*.ingredient_id' => 'required|exists:ingredient,ingredientID',
                'items.*.qty'           => 'required|numeric|min:0.01',
            ]);

            $itemsData = [];
            foreach ($request->items as $item) {
                $ingredient = Ingredient::where('ingredientID', $item['ingredient_id'])->firstOrFail();
                if ($ingredient->currentStock < $item['qty']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$ingredient->name}. Available: {$ingredient->currentStock} {$ingredient->unit}"
                    ]);
                }
                $itemsData[] = ['ingredient' => $ingredient, 'qty' => $item['qty']];
            }

            DB::transaction(function () use ($request, $itemsData) {
                $po = PullOut::create([
                    'prepID'    => null,
                    'pullOutBy' => $request->pulled_by,
                    'pullDate'  => now(),
                    'pullType'  => $request->pull_type ?? 'Manual Adjustment',
                    'remarks'   => $request->remarks,
                ]);
                foreach ($itemsData as $data) {
                    PullOutDetail::create([
                        'pullOutID'    => $po->pullOutID,
                        'ingredientID' => $data['ingredient']->ingredientID,
                        'qtyPulled'    => $data['qty'],
                    ]);
                    $data['ingredient']->decrement('currentStock', $data['qty']);
                }
            });

            return response()->json(['success' => true]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeSupplier(Request $request)
    {
        $request->validate(['supplierName' => 'required|string', 'phone' => 'required|string']);
        Supplier::create(['supplierName' => $request->supplierName, 'phone' => $request->phone]);
        return redirect()->route('admin.inventory')->with('success', 'Supplier added!');
    }

    public function updateSupplier(Request $request, $id)
    {
        $request->validate(['supplierName' => 'required|string', 'phone' => 'required|string']);
        Supplier::where('supplierID', $id)->update(['supplierName' => $request->supplierName, 'phone' => $request->phone]);
        return redirect()->route('admin.inventory')->with('success', 'Supplier updated!');
    }


    public function expired()
{
    // Get ingredientIDs that have at least one expired DR record
    $expiredIngredientIDs = DeliveryReceiptDetail::whereNotNull('expiryDate')
        ->where('expiryDate', '<', today()->startOfDay())
        ->pluck('ingredientID')
        ->unique();

    $result = [];

    foreach ($expiredIngredientIDs as $ingredientID) {
        $ingredient = Ingredient::find($ingredientID);
        if (!$ingredient || $ingredient->currentStock <= 0) continue;

        // Has future stock? Owner already restocked — skip
        $hasFutureStock = DeliveryReceiptDetail::where('ingredientID', $ingredientID)
            ->whereNotNull('expiryDate')
            ->where('expiryDate', '>', today()->startOfDay())
            ->exists();

        if ($hasFutureStock) continue;

        // Get the most recent expired date for display
        $latestExpiry = DeliveryReceiptDetail::where('ingredientID', $ingredientID)
            ->whereNotNull('expiryDate')
            ->where('expiryDate', '<', today()->startOfDay())
            ->orderByDesc('expiryDate')
            ->value('expiryDate');

        $result[] = [
            'name'        => $ingredient->name,
            'expiry_date' => \Carbon\Carbon::parse($latestExpiry)->format('M d, Y'),
        ];
    }

    return response()->json(array_values($result));
}
}