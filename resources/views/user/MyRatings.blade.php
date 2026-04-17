@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="mb-8 pb-6">
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">My Rating History</h1>
                <p class="mt-2 text-lg text-gray-600">Review all the feedback you've submitted for your orders.</p>
        </div>

        <div class="space-y-6">
            @forelse($ratings as $item)
                <div class="bg-white overflow-hidden shadow-sm border border-gray-100 rounded-xl transition hover:shadow-md">
                    <div class="p-6">
                        {{-- 1. Top Row: Stars and Time --}}
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div class="flex items-center">
                                <div class="flex items-center text-yellow-400">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="h-5 w-5 flex-shrink-0 {{ $i <= $item->rating ? 'fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                </div>
                                <span class="ml-3 text-sm font-semibold text-gray-700">{{ $item->rating }}.0 / 5.0</span>
                            </div>
                            <span class="text-sm text-gray-400">Submitted {{ $item->created_at->diffForHumans() }}</span>
                        </div>

                        {{-- 2. The Comment (Now middle) --}}
                        <div class="mt-4">
                            <p class="text-gray-800 leading-relaxed italic text-lg">
                                "{{ $item->comment ?? 'No written feedback provided.' }}"
                            </p>
                        </div>

                        {{-- 3. Product Badges (Now below comment) --}}
                        @php
                            $orderItems = $item->order->orderItems ?? collect();
                        @endphp

                        @if($orderItems->isNotEmpty())
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach($orderItems as $orderItem)
                                    @php
                                        $product = $orderItem->product ?? null;
                                        $category = $product->productType ?? null;
                                    @endphp
                                    @if($product)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-700 border border-pink-200">
                                            {{ $category->productType ?? 'General' }} · {{ $product->name }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- 4. Footer Info --}}
                        <div class="mt-6 flex items-center text-sm text-gray-500 border-t border-gray-50 pt-4">
                            <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ $item->order->orderDate->format('F d, Y') }}
                        </div>
                    </div>
                </div>
            @empty
            @endforelse
        </div>
    </div>
@endsection