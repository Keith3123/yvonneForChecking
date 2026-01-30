<div class="relative flex items-center justify-between mb-8 w-11/12 mx-auto max-w-sm">

    {{-- Full gray line --}}
    <div class="absolute top-1/2 left-0 w-full h-[3px] bg-gray-200 -translate-y-1/2 rounded-full"></div>

    {{-- Active pink progress line --}}
    <div
        id="progress-line"
        class="absolute top-1/2 left-0 h-[3px] bg-pink-400
               transition-all duration-500 ease-out
               -translate-y-1/2 rounded-full"
        style="width: 0%;">
    </div>

    {{-- Step circles --}}
    <div
        class="progress-step relative z-10 flex items-center justify-center
               w-7 h-7 rounded-full
               bg-pink-400 text-white text-xs font-semibold
               shadow-md transition-all duration-300">
        1
    </div>

    <div
        class="progress-step relative z-10 flex items-center justify-center
               w-7 h-7 rounded-full
               bg-gray-300 text-gray-700 text-xs font-semibold
               transition-all duration-300">
        2
    </div>

    <div
        class="progress-step relative z-10 flex items-center justify-center
               w-7 h-7 rounded-full
               bg-gray-300 text-gray-700 text-xs font-semibold
               transition-all duration-300">
        3
    </div>
</div>
