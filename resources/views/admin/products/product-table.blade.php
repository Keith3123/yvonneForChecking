@forelse($products as $product)
    @include('admin.products.product-row', ['product' => $product])
@empty
    <tr>
        <td colspan="7" class="p-8 text-center text-gray-400">
            <div class="flex flex-col items-center">
                <i class="fas fa-box-open text-4xl mb-3 text-gray-300"></i>
                <p class="text-lg font-medium">No products yet</p>
                <p class="text-sm">Click "Add Product" to get started</p>
            </div>
        </td>
    </tr>
@endforelse