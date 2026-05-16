@extends('layouts.app')
@section('no-footer')
@endsection
@section('content')
<div class="min-h-screen flex flex-col items-center pt-16 text-center bg-[#FFF8F5]">

    <div class="mb-6">
        <h2 class="text-lg font-semibold">Welcome to Yvonne's Cakes & Pastries</h2>
        <p class="text-xs text-gray-500">Custom cakes, pastries and food trays for every occasion</p>
    </div>

    {{-- Tabs --}}
    <div class="flex w-[280px] bg-white rounded-full mb-5 p-1 text-sm font-medium shadow">
        <button type="button" class="w-1/2 py-2 rounded-full bg-pink-100 text-pink-600 font-semibold">Register</button>
        <button type="button" onclick="window.location.href='{{ route('login') }}'"
            class="w-1/2 py-2 rounded-full text-gray-600 hover:bg-gray-100 transition">Login</button>
    </div>

    {{-- Registration Form --}}
    <form action="{{ route('register.store') }}" method="POST"
        class="w-full max-w-sm bg-white p-6 rounded-xl shadow-lg text-left" id="registerForm">
        @csrf
        @include('partials.register.progress')

        {{-- Step 1 --}}
        <div class="step step-1">
            <p class="text-sm text-gray-500 mb-4">Create an account to start ordering</p>
            @if ($errors->any())
            <div class="text-red-500 text-xs mb-3">{{ $errors->first() }}</div>
            @endif
            <div class="flex flex-col gap-3 mb-6 text-sm">
                <label class="flex flex-col">
                    <span class="mb-1">Last Name</span>
                    <input type="text" name="lastName" value="{{ old('lastName') }}" required
                        class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-pink-300 outline-none">
                </label>
                <label class="flex flex-col">
                    <span class="mb-1">First Name</span>
                    <input type="text" name="firstName" value="{{ old('firstName') }}" required
                        class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-pink-300 outline-none">
                </label>
                <label class="flex flex-col">
                    <span class="mb-1">Middle Initial</span>
                    <input type="text" name="mi" value="{{ old('mi') }}"
                        class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-pink-300 outline-none @error('mi') border-red-500 @enderror">
                    @error('mi')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                </label>
            </div>
            <button type="button" class="next-btn w-full bg-pink-500 hover:bg-pink-600 text-white font-semibold py-2 rounded-lg transition">Continue</button>
        </div>

        {{-- Step 2 --}}
        <div class="step step-2 hidden">
            <p class="text-sm text-gray-500 mb-4">Create an account to start ordering</p>
            <div class="flex flex-col gap-3 mb-6 text-sm">
                <label>Email Address
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full border border-gray-300 rounded-lg p-2 mt-1 focus:ring-2 focus:ring-pink-300 outline-none">
                </label>
                <label>Personal Address
                    <input type="text" name="address" value="{{ old('address') }}" required
                        class="w-full border border-gray-300 rounded-lg p-2 mt-1 focus:ring-2 focus:ring-pink-300 outline-none">
                </label>
                <label>Phone Number
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                        class="w-full border border-gray-300 rounded-lg p-2 mt-1 focus:ring-2 focus:ring-pink-300 outline-none @error('phone') is-invalid @enderror">
                    <span class="text-red-500 text-xs mt-1 @error('phone') @else hidden @enderror">
                        @error('phone') {{ $message }} @else Enter a valid 11-digit phone number. @enderror
                    </span>
                </label>
            </div>
            <div class="flex gap-3">
                <button type="button" class="back-btn w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 rounded-lg transition">Back</button>
                <button type="button" class="next-btn w-1/2 bg-pink-500 hover:bg-pink-600 text-white font-semibold py-2 rounded-lg transition">Continue</button>
            </div>
        </div>

        {{-- Step 3 --}}
        <div class="step step-3 hidden">
            <p class="text-sm text-gray-500 mb-4">Create an account to start ordering</p>
            <div class="flex flex-col gap-3 mb-4 text-sm">
                <label class="flex flex-col relative">
                    <span class="mb-1">Username</span>
                    <input type="text" name="username" value="{{ old('username') }}" required id="username"
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-pink-300 outline-none">
                    <span class="text-red-500 text-sm mt-1 hidden" id="username-error">Username already exists</span>
                </label>
                <label class="flex flex-col relative">
                    <span class="mb-1">Password</span>
                    <input type="password" name="password" required
                        class="w-full border border-gray-300 rounded-lg p-2 password-input focus:ring-2 focus:ring-pink-300 outline-none">
                    <button type="button" class="absolute right-2 top-9 text-gray-500 toggle-password"><i class="far fa-eye-slash"></i></button>
                    <div class="text-xs mt-2 space-y-1 password-rules">
                        <p data-rule="length"    class="text-gray-400">At least 8 characters</p>
                        <p data-rule="uppercase" class="text-gray-400">1 uppercase letter</p>
                        <p data-rule="lowercase" class="text-gray-400">1 lowercase letter</p>
                        <p data-rule="number"    class="text-gray-400">1 number</p>
                    </div>
                </label>
                <label class="flex flex-col relative">
                    <span class="mb-1">Confirm Password</span>
                    <input type="password" name="password_confirmation" required
                        class="w-full border border-gray-300 rounded-lg p-2 password-input focus:ring-2 focus:ring-pink-300 outline-none">
                    <button type="button" class="absolute right-2 top-9 text-gray-500 toggle-password"><i class="far fa-eye-slash"></i></button>
                    <p class="text-red-500 text-xs mt-1 hidden password-error">The password confirmation does not match.</p>
                </label>
            </div>

            {{-- T&C Checkbox --}}
            <div class="mb-5">
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" id="terms-checkbox" class="mt-0.5 accent-pink-500 w-4 h-4 shrink-0 cursor-pointer">
                    <span class="text-xs text-gray-500 leading-relaxed">
                        I have read and agree to the
                        <button type="button" id="open-terms-modal" class="text-pink-500 underline hover:text-pink-700 font-medium">
                            Terms and Conditions
                        </button>
                        of Yvonne's Cakes &amp; Pastries.
                    </span>
                </label>
                <p class="text-red-500 text-xs mt-1 hidden" id="terms-error">You must agree to the Terms and Conditions to continue.</p>
            </div>

            <div class="flex gap-3">
                <button type="button" class="back-btn w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 rounded-lg transition">Back</button>
                <button type="submit" class="submit-btn w-1/2 bg-pink-500 hover:bg-pink-600 text-white font-semibold py-2 rounded-lg transition">Create Account</button>
            </div>
        </div>
    </form>

    {{-- OTP Modal --}}
    <div id="otpModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl mx-4">
            <h2 class="text-lg font-semibold text-center mb-2">Phone Verification</h2>
            <p class="text-sm text-gray-500 text-center mb-4">Enter the 6-digit OTP sent to your phone</p>
            <input type="text" id="otpInput" maxlength="6" inputmode="numeric"
                class="w-full border border-gray-300 rounded-xl p-3 text-center text-lg tracking-widest focus:ring-2 focus:ring-pink-300 outline-none" placeholder="000000">
            <p id="otpError" class="text-red-500 text-sm mt-2 hidden text-center"></p>
            <p id="otpTimer" data-duration="300" class="text-gray-500 text-sm mt-2 text-center">OTP expires in: 05:00</p>
            <button type="button" id="resendOtpBtn" class="w-full mt-3 text-pink-500 hover:text-pink-600 text-sm font-medium">Resend OTP</button>
            <div class="flex gap-3 mt-4">
                <button type="button" id="closeOtpModal" class="w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 rounded-xl">Cancel</button>
                <button type="button" id="verifyOtpBtn" class="w-1/2 bg-pink-500 hover:bg-pink-600 text-white py-2 rounded-xl">Verify</button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- T&C MODAL — Enhanced Accordion Design                  --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div id="terms-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" style="backdrop-filter: blur(4px);">
        <div class="bg-white rounded-2xl w-full max-w-lg flex flex-col shadow-2xl" style="max-height: 88vh;">

            {{-- ── Header ── --}}
            <div class="px-5 pt-5 pb-4 shrink-0">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Terms & Conditions</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Yvonne's Cakes & Pastries · {{ date('F Y') }}</p>
                    </div>
                    <button id="close-terms-modal" type="button"
                        class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 transition text-base font-medium shrink-0 mt-0.5">
                        ✕
                    </button>
                </div>

                {{-- Progress bar --}}
                <div class="flex items-center gap-2">
                    <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div id="tnc-progress-bar" class="h-full bg-pink-400 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                    <span id="tnc-progress-text" class="text-[11px] text-gray-400 font-medium whitespace-nowrap">0 of 9 read</span>
                </div>

                {{-- Hint --}}
                <p class="text-[11px] text-gray-400 mt-2">Tap each section to read the full details.</p>
            </div>

            <div class="border-t border-gray-100 shrink-0"></div>

            {{-- ── Accordion Body ── --}}
            <div class="overflow-y-auto flex-1 px-4 py-3 space-y-1.5" id="tnc-accordion">

                {{-- SECTION TEMPLATE:
                     data-section: unique id
                     Each item = trigger row + collapsible content panel --}}

                {{-- 1. Acceptance --}}
                <div class="tnc-item rounded-xl border border-gray-200 overflow-hidden" data-section="1">
                    <button type="button" class="tnc-trigger w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-pink-100 text-pink-600 text-[11px] font-bold shrink-0">1</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 leading-tight">Acceptance of Terms</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">By registering, you agree to these terms in full.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="tnc-read-badge hidden text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Read ✓</span>
                            <svg class="tnc-chevron w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div class="tnc-panel hidden border-t border-gray-100 bg-gray-50 px-5 py-4 text-sm text-gray-600 leading-relaxed">
                        <p>By creating an account on Yvonne's Cakes &amp; Pastries, you confirm that you have read, understood, and agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, please do not register or use our services.</p>
                    </div>
                </div>

                {{-- 2. Account --}}
                <div class="tnc-item rounded-xl border border-gray-200 overflow-hidden" data-section="2">
                    <button type="button" class="tnc-trigger w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-pink-100 text-pink-600 text-[11px] font-bold shrink-0">2</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 leading-tight">Account Responsibility</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">You're responsible for your account. Max 5 accounts per number.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="tnc-read-badge hidden text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Read ✓</span>
                            <svg class="tnc-chevron w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div class="tnc-panel hidden border-t border-gray-100 bg-gray-50 px-5 py-4 text-sm text-gray-600 leading-relaxed space-y-2">
                        <p>You are solely responsible for keeping your account credentials confidential. Any activity that occurs under your account is your responsibility.</p>
                        <p>If you suspect unauthorized access, notify us immediately.</p>
                        <div class="flex items-start gap-2 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2.5 mt-2">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            <p class="text-xs text-amber-800">A single phone number may not be used to register more than <strong>5 accounts</strong>.</p>
                        </div>
                    </div>
                </div>

                {{-- 3. Orders & Payments --}}
                <div class="tnc-item rounded-xl border border-gray-200 overflow-hidden" data-section="3">
                    <button type="button" class="tnc-trigger w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-pink-100 text-pink-600 text-[11px] font-bold shrink-0">3</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 leading-tight">Orders & Payments</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">GCash (PayMongo) or COD. Non-refundable once Confirmed.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="tnc-read-badge hidden text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Read ✓</span>
                            <svg class="tnc-chevron w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div class="tnc-panel hidden border-t border-gray-100 bg-gray-50 px-5 py-4 text-sm text-gray-600 leading-relaxed space-y-4">
                        <p>All orders are subject to availability and confirmation. Prices may change without notice. We may cancel any order due to errors or unavailability.</p>

                        {{-- GCash block --}}
                        <div>
                            <div class="flex items-center gap-1.5 mb-2">
                                <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                <p class="text-xs font-bold text-blue-700 uppercase tracking-wide">GCash via PayMongo</p>
                            </div>
                            <div class="space-y-2 pl-3.5">
                                <div class="flex gap-2"><div class="w-1 shrink-0 rounded-full bg-blue-200 mt-1"></div><p class="text-xs text-gray-600">You may pay in <strong>Full Payment</strong> or <strong>Downpayment</strong>. Downpayment charges partial via GCash; remaining is collected in cash on delivery.</p></div>
                                <div class="flex gap-2"><div class="w-1 shrink-0 rounded-full bg-blue-200 mt-1"></div><p class="text-xs text-gray-600">GCash payments are <strong>non-refundable</strong> once your order is Confirmed and preparation has begun.</p></div>
                                <div class="flex gap-2"><div class="w-1 shrink-0 rounded-full bg-blue-200 mt-1"></div><p class="text-xs text-gray-600">A failed payment means your order is <strong>not placed</strong>. Retry or switch to COD.</p></div>
                                <div class="flex gap-2"><div class="w-1 shrink-0 rounded-full bg-blue-200 mt-1"></div><p class="text-xs text-gray-600">Order is only considered placed once you receive an <strong>order confirmation</strong>.</p></div>
                                <div class="flex gap-2"><div class="w-1 shrink-0 rounded-full bg-blue-200 mt-1"></div><p class="text-xs text-gray-600">For duplicate charges, contact us immediately with your PayMongo transaction reference.</p></div>
                                <div class="flex gap-2"><div class="w-1 shrink-0 rounded-full bg-blue-200 mt-1"></div><p class="text-xs text-gray-600">We are not liable for failures caused by insufficient balance, network issues, or PayMongo downtime.</p></div>
                            </div>
                        </div>

                        {{-- COD block --}}
                        <div>
                            <div class="flex items-center gap-1.5 mb-2">
                                <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                <p class="text-xs font-bold text-amber-700 uppercase tracking-wide">Cash on Delivery (COD)</p>
                            </div>
                            <div class="space-y-2 pl-3.5">
                                <div class="flex gap-2"><div class="w-1 shrink-0 rounded-full bg-amber-200 mt-1"></div><p class="text-xs text-gray-600">Full cash payment is required upon delivery. Prepare the <strong>exact amount</strong> when possible.</p></div>
                                <div class="flex gap-2"><div class="w-1 shrink-0 rounded-full bg-amber-200 mt-1"></div><p class="text-xs text-gray-600">Failure to pay upon delivery may result in your account being <strong>restricted</strong> from future orders.</p></div>
                            </div>
                        </div>

                        {{-- Cancellation block --}}
                        <div>
                            <div class="flex items-center gap-1.5 mb-2">
                                <div class="w-2 h-2 rounded-full bg-red-400"></div>
                                <p class="text-xs font-bold text-red-700 uppercase tracking-wide">Cancellation & Refunds</p>
                            </div>
                            <div class="space-y-2 pl-3.5">
                                <div class="flex gap-2"><div class="w-1 shrink-0 rounded-full bg-red-200 mt-1"></div><p class="text-xs text-gray-600">Cancellations are only allowed while the order is still <strong>Pending</strong>. Once Confirmed, no cancellations are accepted.</p></div>
                                <div class="flex gap-2"><div class="w-1 shrink-0 rounded-full bg-red-200 mt-1"></div><p class="text-xs text-gray-600">Refund requests for Pending GCash orders are processed within <strong>5–10 business days</strong> through PayMongo.</p></div>
                                <div class="flex gap-2"><div class="w-1 shrink-0 rounded-full bg-red-200 mt-1"></div><p class="text-xs text-gray-600">We reserve the right to deny refund requests outside the allowed window.</p></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. Paluwagan --}}
                <div class="tnc-item rounded-xl border border-gray-200 overflow-hidden" data-section="4">
                    <button type="button" class="tnc-trigger w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-pink-100 text-pink-600 text-[11px] font-bold shrink-0">4</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 leading-tight">Paluwagan Participation</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">20 slots/month. Payments non-refundable. Cancelled slots transfer to next in queue.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="tnc-read-badge hidden text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Read ✓</span>
                            <svg class="tnc-chevron w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div class="tnc-panel hidden border-t border-gray-100 bg-gray-50 px-5 py-4 text-sm text-gray-600 leading-relaxed space-y-4">
                        <p>Enrolling in a Paluwagan package is a <strong>financial commitment</strong>. By joining, you agree to all terms below.</p>

                        @php
                            $paluwagSections = [
                                [
                                    'title' => 'Enrollment & Slots',
                                    'items' => [
                                        'Each package has a maximum of <strong>20 active slots per month</strong>, each tied to a specific delivery date.',
                                        'If your preferred date is full, you may join the <strong>waiting list</strong> and will be activated first-come, first-served when a slot opens.',
                                        'You may only hold <strong>one active entry per slot</strong> (package + month + day combination).',
                                    ]
                                ],
                                [
                                    'title' => 'Monthly Payments',
                                    'items' => [
                                        'Payments are calculated by dividing the total package price by the months remaining from enrollment to your delivery date.',
                                        'Payments must be made on or before each due date. Late payments will be flagged.',
                                        'All payments are via <strong>GCash through PayMongo</strong> and are <strong>non-refundable</strong> once processed.',
                                    ]
                                ],
                                [
                                    'title' => 'Early Release',
                                    'items' => [
                                        'You may request early release before your scheduled date, provided your <strong>full balance is paid</strong>.',
                                        'Requests require <strong>admin approval</strong> and are not guaranteed.',
                                        'Requests with any remaining balance will be <strong>automatically rejected</strong>.',
                                    ]
                                ],
                                [
                                    'title' => 'Cancellation & Slot Transfer',
                                    'items' => [
                                        'You may cancel at any time. Your slot transfers automatically to the <strong>next person on the waiting list</strong> (FIFO).',
                                        'The new holder inherits your payments as credit. <strong>No refund</strong> is issued to the cancelling party.',
                                        'If no one is waiting, the slot is released and marked cancelled.',
                                        'Cancelling a waiting list entry removes you from the queue. You may rejoin at any time.',
                                    ]
                                ],
                                [
                                    'title' => 'Completion',
                                    'items' => [
                                        'A subscription is <strong>completed</strong> when all payments are settled and the product has been released.',
                                        'If your delivery date arrives with a fully paid balance, your subscription is <strong>automatically completed</strong>.',
                                        'We reserve the right to reassign slots if payment obligations are not met.',
                                    ]
                                ],
                            ];
                        @endphp

                        @foreach($paluwagSections as $ps)
                        <div>
                            <p class="text-xs font-bold text-pink-700 uppercase tracking-wide mb-2">{{ $ps['title'] }}</p>
                            <div class="space-y-2 pl-3">
                                @foreach($ps['items'] as $item)
                                <div class="flex gap-2.5">
                                    <div class="w-0.5 shrink-0 rounded-full bg-pink-200 mt-1" style="min-height:14px"></div>
                                    <p class="text-xs text-gray-600 leading-relaxed">{!! $item !!}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- 5. Privacy --}}
                <div class="tnc-item rounded-xl border border-gray-200 overflow-hidden" data-section="5">
                    <button type="button" class="tnc-trigger w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-pink-100 text-pink-600 text-[11px] font-bold shrink-0">5</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 leading-tight">Privacy & Your Data</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">We store your info securely. Never sold to third parties.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="tnc-read-badge hidden text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Read ✓</span>
                            <svg class="tnc-chevron w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div class="tnc-panel hidden border-t border-gray-100 bg-gray-50 px-5 py-4 text-sm text-gray-600 leading-relaxed space-y-3">
                        <p>We collect and store the following information when you register or update your profile:</p>
                        <div class="grid grid-cols-2 gap-2">
                            @php
                                $dataFields = [
                                    ['icon' => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Full name & username'],
                                    ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label' => 'Email address'],
                                    ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'label' => 'Contact number'],
                                    ['icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Address & coordinates'],
                                ];
                            @endphp
                            @foreach($dataFields as $df)
                            <div class="flex items-center gap-2 bg-white border border-gray-100 rounded-lg px-2.5 py-2">
                                <svg class="w-3.5 h-3.5 text-pink-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $df['icon'] }}"/>
                                </svg>
                                <span class="text-xs text-gray-600">{{ $df['label'] }}</span>
                            </div>
                            @endforeach
                        </div>
                        <div class="flex items-start gap-2 bg-green-50 border border-green-100 rounded-lg px-3 py-2.5">
                            <svg class="w-4 h-4 text-green-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <p class="text-xs text-green-800">Passwords are encrypted and never stored in plain text. Your data is never sold or shared with third parties.</p>
                        </div>
                    </div>
                </div>

                {{-- 6. Google --}}
                <div class="tnc-item rounded-xl border border-gray-200 overflow-hidden" data-section="6">
                    <button type="button" class="tnc-trigger w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-pink-100 text-pink-600 text-[11px] font-bold shrink-0">6</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 leading-tight">Google Account Linking</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">Linking replaces your email. One Google account per user.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="tnc-read-badge hidden text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Read ✓</span>
                            <svg class="tnc-chevron w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div class="tnc-panel hidden border-t border-gray-100 bg-gray-50 px-5 py-4 text-sm text-gray-600 leading-relaxed space-y-2">
                        <div class="flex gap-2.5"><div class="w-0.5 shrink-0 rounded-full bg-pink-200 mt-1" style="min-height:14px"></div><p class="text-xs text-gray-600 leading-relaxed">Linking your Google account will <strong>replace your current email</strong> with the email from your Google account, which is then automatically verified.</p></div>
                        <div class="flex gap-2.5"><div class="w-0.5 shrink-0 rounded-full bg-pink-200 mt-1" style="min-height:14px"></div><p class="text-xs text-gray-600 leading-relaxed">A Google account can only be linked to <strong>one Yvonne's account</strong>. Attempts to link an already-bound Google account will be blocked.</p></div>
                        <div class="flex gap-2.5"><div class="w-0.5 shrink-0 rounded-full bg-pink-200 mt-1" style="min-height:14px"></div><p class="text-xs text-gray-600 leading-relaxed">Unlinking removes your Google ID and clears your verified email status.</p></div>
                        <div class="flex items-start gap-2 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2.5 mt-1">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            <p class="text-xs text-amber-800">Make sure you have a <strong>password set</strong> before unlinking your Google account, or you may lose access.</p>
                        </div>
                    </div>
                </div>

                {{-- 7. Product Availability --}}
                <div class="tnc-item rounded-xl border border-gray-200 overflow-hidden" data-section="7">
                    <button type="button" class="tnc-trigger w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-pink-100 text-pink-600 text-[11px] font-bold shrink-0">7</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 leading-tight">Product Availability</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">Products, prices, and promos can change anytime without notice.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="tnc-read-badge hidden text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Read ✓</span>
                            <svg class="tnc-chevron w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div class="tnc-panel hidden border-t border-gray-100 bg-gray-50 px-5 py-4 text-sm text-gray-600 leading-relaxed space-y-2">
                        <div class="flex gap-2.5"><div class="w-0.5 shrink-0 rounded-full bg-pink-200 mt-1" style="min-height:14px"></div><p class="text-xs text-gray-600">Products marked as <strong>Unavailable</strong> cannot be added to cart or ordered.</p></div>
                        <div class="flex gap-2.5"><div class="w-0.5 shrink-0 rounded-full bg-pink-200 mt-1" style="min-height:14px"></div><p class="text-xs text-gray-600">We may update, discontinue, or modify any product — including its serving sizes, pricing, or ingredients — at any time without prior notice.</p></div>
                        <div class="flex gap-2.5"><div class="w-0.5 shrink-0 rounded-full bg-pink-200 mt-1" style="min-height:14px"></div><p class="text-xs text-gray-600">Promotional discounts are applied at the time of order and are not guaranteed for future purchases.</p></div>
                    </div>
                </div>

                {{-- 8. Changes --}}
                <div class="tnc-item rounded-xl border border-gray-200 overflow-hidden" data-section="8">
                    <button type="button" class="tnc-trigger w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-pink-100 text-pink-600 text-[11px] font-bold shrink-0">8</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 leading-tight">Changes to These Terms</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">We may update these terms. Continued use means acceptance.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="tnc-read-badge hidden text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Read ✓</span>
                            <svg class="tnc-chevron w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div class="tnc-panel hidden border-t border-gray-100 bg-gray-50 px-5 py-4 text-sm text-gray-600 leading-relaxed">
                        <p>We may update these Terms and Conditions at any time. Continued use of the platform after changes are published constitutes your acceptance of the revised terms. We recommend reviewing this page periodically.</p>
                    </div>
                </div>

                {{-- 9. Contact --}}
                <div class="tnc-item rounded-xl border border-gray-200 overflow-hidden" data-section="9">
                    <button type="button" class="tnc-trigger w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-pink-100 text-pink-600 text-[11px] font-bold shrink-0">9</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 leading-tight">Contact Us</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">Questions? Reach us through our official channels.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="tnc-read-badge hidden text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Read ✓</span>
                            <svg class="tnc-chevron w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div class="tnc-panel hidden border-t border-gray-100 bg-gray-50 px-5 py-4 text-sm text-gray-600 leading-relaxed">
                        <p>For any questions or concerns about these Terms, please reach out to us through our official channels. We're happy to assist you.</p>
                    </div>
                </div>

            </div>

            {{-- ── Footer ── --}}
            <div class="border-t border-gray-100 shrink-0">
                <div class="px-5 py-4 flex items-center justify-between gap-3">
                    <div>
                        <p id="tnc-footer-status" class="text-xs text-gray-400">Open each section to read.</p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button id="close-terms-modal-btn" type="button"
                            class="px-4 py-2 text-xs rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 transition font-medium">
                            Close
                        </button>
                        <button id="accept-terms-btn" type="button"
                            class="px-4 py-2 text-xs rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            I Agree
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal     = document.getElementById('terms-modal');
    const openBtn   = document.getElementById('open-terms-modal');
    const closeBtn  = document.getElementById('close-terms-modal');
    const closeFooter = document.getElementById('close-terms-modal-btn');
    const acceptBtn = document.getElementById('accept-terms-btn');
    const checkbox  = document.getElementById('terms-checkbox');

    const TOTAL     = 9;
    const readSet   = new Set();

    function updateProgress() {
        const count  = readSet.size;
        const pct    = Math.round((count / TOTAL) * 100);
        document.getElementById('tnc-progress-bar').style.width  = pct + '%';
        document.getElementById('tnc-progress-text').textContent = `${count} of ${TOTAL} read`;

        const status = document.getElementById('tnc-footer-status');
        if (count === 0)        status.textContent = 'Open each section to read.';
        else if (count < TOTAL) status.textContent = `${TOTAL - count} section${TOTAL - count > 1 ? 's' : ''} left to read.`;
        else                    status.textContent = 'You\'ve read all sections. ✓';
    }

    // Accordion logic
    document.querySelectorAll('.tnc-trigger').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const item    = trigger.closest('.tnc-item');
            const panel   = item.querySelector('.tnc-panel');
            const chevron = item.querySelector('.tnc-chevron');
            const badge   = item.querySelector('.tnc-read-badge');
            const section = item.dataset.section;
            const isOpen  = !panel.classList.contains('hidden');

            // Close all others
            document.querySelectorAll('.tnc-item').forEach(other => {
                if (other !== item) {
                    other.querySelector('.tnc-panel').classList.add('hidden');
                    other.querySelector('.tnc-chevron').style.transform = '';
                }
            });

            // Toggle current
            if (isOpen) {
                panel.classList.add('hidden');
                chevron.style.transform = '';
            } else {
                panel.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
                // Mark as read
                if (!readSet.has(section)) {
                    readSet.add(section);
                    badge.classList.remove('hidden');
                    item.classList.remove('border-gray-200');
                    item.classList.add('border-pink-200');
                    updateProgress();
                }
            }
        });
    });

    // Modal open/close
    const openModal = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        // Close all accordion panels
        document.querySelectorAll('.tnc-panel').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.tnc-chevron').forEach(c => c.style.transform = '');
    };

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    closeFooter?.addEventListener('click', closeModal);
    modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    acceptBtn?.addEventListener('click', () => {
        if (checkbox) checkbox.checked = true;
        document.getElementById('terms-error')?.classList.add('hidden');
        closeModal();
    });

    updateProgress();
});
</script>

@vite('resources/js/register.js')
@endsection