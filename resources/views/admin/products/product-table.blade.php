@foreach($products as $product)
    @include('admin.products.product-row', ['product'=>$product])
@endforeach