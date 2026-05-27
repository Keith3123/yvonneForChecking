{{-- Registration Form (modal version) --}}
<form action="{{ route('register.store') }}" method="POST"
    class="space-y-6 text-sm"
    id="registerForm">
    @csrf

    {{-- Progress --}}
    @include('partials.register.progress-modal')

    {{-- ================= STEP 1 ================= --}}
    <div class="step step-1 space-y-6">

        <p class="text-sm text-gray-500">
            Create an account to start ordering
        </p>

        @if ($errors->any())
        <div class="text-red-500 text-xs">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="space-y-4">
            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Last Name</span>
                <input type="text" name="lastName" value="{{ old('lastName') }}" required autocomplete="family-name"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">First Name</span>
                <input type="text" name="firstName" value="{{ old('firstName') }}" required autocomplete="given-name"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Middle Name</span>
                <input type="text" name="mi" value="{{ old('mi') }}" autocomplete="additional-name"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>
        </div>

        <button type="button"
            class="next-btn w-full bg-pink-500 hover:bg-pink-600
                   text-white font-semibold py-3 rounded-xl transition">
            Continue
        </button>
    </div>

    {{-- ================= STEP 2 ================= --}}
    <div class="step step-2 hidden space-y-6">

        <p class="text-sm text-gray-500">
            Create an account to start ordering
        </p>

        <div class="space-y-4">
            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Email Address</span>
                {{-- nullable in controller --}}
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="email"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Address</span>
                <input type="text" name="address" value="{{ old('address') }}" required autocomplete="street-address"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

            {{-- Phone: <span> required by JS validateStep(1) --}}
            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Phone Number</span>
                <input type="text" name="phone" value="{{ old('phone') }}" required autocomplete="tel"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none @error('phone') is-invalid border-red-500 @enderror">
                <span class="text-red-500 text-xs mt-1 @error('phone') @else hidden @enderror">
                    @error('phone') {{ $message }} @else Enter a valid 11-digit phone number. @enderror
                </span>
            </label>
        </div>

        <div class="flex gap-3">
            <button type="button"
                class="back-btn w-1/2 bg-gray-200 hover:bg-gray-300
                       text-gray-700 font-medium py-3 rounded-xl transition">
                Back
            </button>
            <button type="button"
                class="next-btn w-1/2 bg-pink-500 hover:bg-pink-600
                       text-white font-semibold py-3 rounded-xl transition">
                Continue
            </button>
        </div>
    </div>

    {{-- ================= STEP 3 ================= --}}
    <div class="step step-3 hidden space-y-6">

        <p class="text-sm text-gray-500">
            Create an account to start ordering
        </p>

        <div class="space-y-4">

            {{-- Username --}}
            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Username</span>
                <input type="text" name="username" value="{{ old('username') }}" required
                    id="username" autocomplete="username"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
                <span class="text-red-500 text-xs mt-1 hidden" id="username-error">
                    Username already exists
                </span>
            </label>

            {{-- Password --}}
            <label class="flex flex-col relative">
                <span class="mb-1 font-medium text-gray-700">Password</span>
                <input type="password" name="password" required autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-xl p-3 pr-10 password-input focus:ring-2 focus:ring-pink-300 outline-none">
                <button type="button" class="absolute right-3 top-10 text-gray-400 toggle-password">
                    <i class="far fa-eye-slash"></i>
                </button>
                <div class="password-rules text-xs mt-2 space-y-1">
                    <p data-rule="length"    class="text-gray-400">At least 8 characters</p>
                    <p data-rule="uppercase" class="text-gray-400">One uppercase letter</p>
                    <p data-rule="lowercase" class="text-gray-400">One lowercase letter</p>
                    <p data-rule="number"    class="text-gray-400">One number</p>
                </div>
            </label>

            {{-- Confirm Password --}}
            <label class="flex flex-col relative">
                <span class="mb-1 font-medium text-gray-700">Confirm Password</span>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-xl p-3 pr-10 password-input focus:ring-2 focus:ring-pink-300 outline-none">
                <button type="button" class="absolute right-3 top-10 text-gray-400 toggle-password">
                    <i class="far fa-eye-slash"></i>
                </button>
                <p class="text-red-500 text-xs mt-1 hidden password-error">
                    Passwords do not match
                </p>
            </label>

        </div>

        {{-- Terms & Conditions --}}
        <div class="mb-2">
            <label class="flex items-start gap-2 cursor-pointer group">
                <input type="checkbox" id="terms-checkbox"
                    class="mt-0.5 accent-pink-500 w-4 h-4 shrink-0 cursor-pointer">
                <span class="text-xs text-gray-500 leading-relaxed">
                    I have read and agree to the
                    <button type="button" id="open-terms-modal"
                        class="text-pink-500 underline hover:text-pink-700 font-medium">
                        Terms and Conditions
                    </button>
                    of Yvonne's Cakes &amp; Pastries.
                </span>
            </label>
            <p class="text-red-500 text-xs mt-1 hidden" id="terms-error">
                You must agree to the Terms and Conditions to continue.
            </p>
        </div>

        <div class="flex gap-3">
            <button type="button"
                class="back-btn w-1/2 bg-gray-200 hover:bg-gray-300
                       text-gray-700 font-medium py-3 rounded-xl transition">
                Back
            </button>
            <button type="submit"
                class="w-1/2 bg-pink-500 hover:bg-pink-600
                       text-white font-semibold py-3 rounded-xl transition">
                Create Account
            </button>
        </div>
    </div>

</form>

{{-- ===================== OTP MODAL ===================== --}}
{{-- Outside <form> — register.js looks for these IDs in the whole document --}}
<div id="otpModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl mx-4">
        <h2 class="text-lg font-semibold text-center mb-2">Phone Verification</h2>
        <p class="text-sm text-gray-500 text-center mb-4">
            Enter the 6-digit OTP sent to your phone
        </p>

        <input type="text" id="otpInput" maxlength="6" inputmode="numeric"
            class="w-full border border-gray-300 rounded-xl p-3 text-center text-lg tracking-widest focus:ring-2 focus:ring-pink-300 outline-none"
            placeholder="000000">

        <p id="otpError" class="text-red-500 text-sm mt-2 hidden text-center"></p>

        <p id="otpTimer" data-duration="300"
            class="text-gray-500 text-sm mt-2 text-center">
            OTP expires in: 05:00
        </p>

        <button type="button" id="resendOtpBtn"
            class="w-full mt-3 text-pink-500 hover:text-pink-600 text-sm font-medium">
            Resend OTP
        </button>

        <div class="flex gap-3 mt-4">
            <button type="button" id="closeOtpModal"
                class="w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 rounded-xl">
                Cancel
            </button>
            <button type="button" id="verifyOtpBtn"
                class="w-1/2 bg-pink-500 hover:bg-pink-600 text-white py-2 rounded-xl">
                Verify
            </button>
        </div>
    </div>
</div>

{{-- T&C MODAL --}}
<div id="terms-modal" class="fixed inset-0 z-50 hidden items-center justify-center " style="backdrop-filter: blur(4px);">
    <div class="bg-white rounded-2xl w-full max-w-lg mx-4 flex flex-col shadow-2xl" style="max-height: 88vh;">

        {{-- Header --}}
        <div class="flex items-start justify-between px-5 pt-5 pb-4 border-b border-gray-100 shrink-0">
            <div>
                <h2 class="text-base font-semibold text-gray-900 text-left">Terms & Conditions</h2>
                <p class="text-xs text-gray-400 mt-0.5">Yvonne's Cakes & Pastries · {{ date('F Y') }}</p>
            </div>
            <button id="close-terms-modal" type="button"
                class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 transition text-base font-medium shrink-0 mt-0.5">
                ✕
            </button>
        </div>

        {{-- Body --}}
        <div class="overflow-y-auto flex-1 px-5 py-4 space-y-5 text-sm text-gray-600 text-left">

            <section>
                <p class="font-semibold text-gray-800 mb-1.5">1. Acceptance of Terms</p>
                <p class="text-xs leading-relaxed">By creating an account on Yvonne's Cakes &amp; Pastries, you confirm that you have read, understood, and agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, please do not register or use our services.</p>
            </section>

            <section>
                <p class="font-semibold text-gray-800 mb-1.5">2. Account Responsibility</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4">
                    <li>You are solely responsible for keeping your account credentials confidential. Any activity that occurs under your account is your responsibility.</li>
                    <li>If you suspect unauthorized access, notify us immediately.</li>
                    <li>A single phone number may not be used to register more than <strong>5 accounts</strong>.</li>
                </ul>
            </section>

            <section>
                <p class="font-semibold text-gray-800 mb-1.5">3. Orders & Payments</p>
                <p class="text-xs leading-relaxed mb-2">All orders are subject to availability and confirmation. Prices may change without notice.</p>
                <p class="text-xs font-semibold text-gray-700 mb-1">GCash</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4 mb-2">
                    <li>You may pay in <strong>Full Payment</strong> or <strong>Downpayment</strong>. Downpayment charges partial via GCash; remaining is collected in cash on delivery.</li>
                    <li>A failed payment means your order is <strong>not placed</strong>. Retry or switch to COD.</li>
                    <li>For duplicate charges, contact us immediately via call <strong>0907 421 7589</strong> with your GCash transaction reference.</li>
                    <li>We are not liable for failures caused by insufficient balance, network issues, or GCash downtime.</li>
                </ul>
                <p class="text-xs font-semibold text-gray-700 mb-1">Cash on Delivery (COD)</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4 mb-2">
                    <li>Full cash payment is required upon delivery. Prepare the <strong>exact amount</strong> when possible.</li>
                    <li>Failure to pay upon delivery may result in your account being <strong>restricted</strong> from future orders.</li>
                </ul>
                <p class="text-xs font-semibold text-gray-700 mb-1">Cancellation & Refunds</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4">
                    <li>Cancellations are only allowed while the order is still <strong>Pending</strong>. Once Confirmed, no cancellations are accepted.</li>
                    <li>Refund requests for Pending GCash orders are processed within <strong>5–10 business days</strong> via phone call <strong>0907 421 7589</strong>.</li>
                    <li>We reserve the right to deny refund requests outside the allowed window.</li>
                </ul>
            </section>

            <section>
                <p class="font-semibold text-gray-800 mb-1.5">4. Paluwagan Participation</p>
                <p class="text-xs leading-relaxed mb-2">Enrolling in a Paluwagan package is a <strong>financial commitment</strong>. By joining, you agree to all terms below.</p>
                <p class="text-xs font-semibold text-gray-700 mb-1">Enrollment & Slots</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4 mb-2">
                    <li>Each package has a maximum of <strong>20 active slots per month</strong>, each tied to a specific delivery date &amp; time.</li>
                    <li>If your preferred date is full, you may join the <strong>waiting list</strong> and will be activated first-come, first-served when a slot opens.</li>
                    <li>You may only hold <strong>one active entry per slot</strong> (package + month + day combination).</li>
                </ul>
                <p class="text-xs font-semibold text-gray-700 mb-1">Monthly Payments</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4 mb-2">
                    <li>Payments are calculated by dividing the total package price by the months remaining from enrollment to your delivery date.</li>
                    <li>Payments must be made on or before each due date. There is a 5-day extension for late payment, then ₱30 penalty per daywill be applied to your balance.</li>
                    <li>All payments are via <strong>GCash</strong> and are <strong>non-refundable</strong> once processed.</li>
                </ul>
                <p class="text-xs font-semibold text-gray-700 mb-1">Early Release</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4 mb-2">
                    <li>You may request early release before your scheduled date, provided your <strong>full balance is paid</strong>.</li>
                    <li>Requests require <strong>admin approval</strong> and are not guaranteed.</li>
                    <li>Requests with any remaining balance will be <strong>automatically rejected</strong>.</li>
                </ul>
                <p class="text-xs font-semibold text-gray-700 mb-1">Cancellation & Slot Transfer</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4 mb-2">
                    <li>You may cancel at any time. Your slot transfers automatically to the <strong>next person on the waiting list</strong>.</li>
                    <li>The new holder inherits your payments as credit. <strong>No refund</strong> is issued to the cancelling party.</li>
                    <li>If no one is waiting, the slot is released and marked cancelled.</li>
                </ul>
                <p class="text-xs font-semibold text-gray-700 mb-1">Completion</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4">
                    <li>A paluwagan enrollment is <strong>completed</strong> when all payments are settled and the product has been released.</li>
                    <li>If your delivery date arrives with a fully paid balance, your paluwagan enrollment is <strong>automatically completed</strong>.</li>
                    <li>We reserve the right to reassign slots if payment obligations are not met.</li>
                </ul>
            </section>

            <section>
                <p class="font-semibold text-gray-800 mb-1.5">5. Privacy & Your Data</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4">
                    <li>We collect and store your full name, username, email address, contact number, and address solely for order fulfillment and account management.</li>
                    <li>Passwords are encrypted and never stored in plain text.</li>
                    <li>Your data is <strong>never sold or shared with third parties</strong>.</li>
                </ul>
            </section>

            <section>
                <p class="font-semibold text-gray-800 mb-1.5">6. Google Account Linking</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4">
                    <li>Linking your Google account will <strong>replace your current email</strong> with the email from your Google account, which is then automatically verified.</li>
                    <li>A Google account can only be linked to <strong>one Yvonne's account</strong>. Attempts to link an already-bound Google account will be blocked.</li>
                    <li>Unlinking removes your Google ID and clears your verified email status.</li>
                    <li>Make sure you have a <strong>password set</strong> before unlinking your Google account, or you may lose access.</li>
                </ul>
            </section>

            <section>
                <p class="font-semibold text-gray-800 mb-1.5">7. Product Availability</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4">
                    <li>Products marked as <strong>Unavailable</strong> cannot be added to cart or ordered.</li>
                    <li>We may update, discontinue, or modify any product — including its serving sizes, pricing, or ingredients — at any time without prior notice.</li>
                    <li>Promotional discounts are applied at the time of order and are not guaranteed for future purchases.</li>
                </ul>
            </section>

            <section>
                <p class="font-semibold text-gray-800 mb-1.5">8. Changes to These Terms</p>
                <p class="text-xs leading-relaxed">We may update these Terms and Conditions at any time. Continued use of the platform after changes are published constitutes your acceptance of the revised terms.</p>
            </section>

            <section>
                <p class="font-semibold text-gray-800 mb-1.5">9. Contact Us</p>
                <ul class="text-xs leading-relaxed space-y-1 list-disc list-outside pl-4">
                    <li>Phone call: <strong>0907 421 7589</strong></li>
                    <li>Facebook: <strong>Yvonne's Cakes, Pastries &amp; Food Trays</strong> — <a href="https://www.facebook.com/erika.yvonne1008" class="text-pink-500 underline" target="_blank">facebook.com/erika.yvonne1008</a></li>
                </ul>
            </section>

        </div>

        {{-- Footer --}}
        <div class="border-t border-gray-100 px-5 py-4 flex justify-end gap-2 shrink-0">
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal          = document.getElementById('terms-modal');
    const openBtn        = document.getElementById('open-terms-modal');
    const closeBtn       = document.getElementById('close-terms-modal');
    const closeBtnFooter = document.getElementById('close-terms-modal-btn');
    const acceptBtn      = document.getElementById('accept-terms-btn');
    const checkbox       = document.getElementById('terms-checkbox');

    const openModal  = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
    const closeModal = () => { modal.classList.add('hidden');  modal.classList.remove('flex'); };

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    closeBtnFooter?.addEventListener('click', closeModal);

    acceptBtn?.addEventListener('click', () => {
        if (checkbox) checkbox.checked = true;
        document.getElementById('terms-error')?.classList.add('hidden');
        closeModal();
    });

    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
});
</script>

@vite('resources/js/register.js')