@extends('layouts.admin')

@section('title', 'Paluwagan Management')

@section('content')

{{-- ============================================ --}}
{{-- TOAST  (inside @section so it renders) --}}
{{-- ============================================ --}}
<div id="success-toast" class="hidden fixed top-10 left-1/2 -translate-x-1/2 z-[200] animate-bounce pointer-events-none">
    <div class="bg-green-600 text-white px-8 py-4 rounded-full shadow-2xl flex items-center gap-3 border-2 border-white/20">
        <i class="fas fa-check-circle text-xl"></i>
        <span id="toast-message" class="font-bold whitespace-nowrap">Done!</span>
    </div>
</div>

<div
    x-data="{
        showDetails: false,
        showAdd: false,
        showEdit: false,
        showDeleteModal: false,
        showPaymentHistory: false,
        showReplaceModal: false,
        showCompleteModal: false,      
        completeEntryID: null,
        subPage: 1,
        subPerPage: 10,        
        replaceWaitingOnly: false,
        replaceShowAll: false,

        editPackageID: null,
        editPackageName: '',
        editPackageDescription: '',
        editPackageTotal: '',
        editPackageDuration: '',

        /* ---------- Payment History ---------- */
        historyEntryID: null,
        historyCustomer: '',
        historyPackage: '',
        historyPayments: [],
        historyTotalPaid: 0,
        historyTotalAmount: 0,
        historyLoading: false,

        /* ---------- Replace Customer ---------- */
        replaceEntryID: null,
        replacePackageName: '',
        replacePreviousPaid: 0,
        replaceMonthsPaid: 0,
        replaceSelectedCustomer: '',
        replaceCustomers: [],
        replaceLoading: false,
        replaceSearching: false,
        replaceSearch: '',
        replacePackageID: null,
        replaceStartMonth: null,
        replaceStartDay: null,

        openPaymentHistory(entryID, customerName, packageName) {
            this.historyEntryID   = entryID;
            this.historyCustomer  = customerName;
            this.historyPackage   = packageName;
            this.historyLoading   = true;
            this.showPaymentHistory = true;

            fetch(`/admin/paluwagan/entry/${entryID}/payments`)
                .then(r => r.json())
                .then(data => {
                    this.historyPayments    = data.payments    || [];
                    this.historyTotalPaid   = data.totalPaid   || 0;
                    this.historyTotalAmount = data.totalAmount || 0;
                    this.historyLoading     = false;
                })
                .catch(() => { this.historyLoading = false; });
        },

        openReplaceModal(entryID, packageName, previousPaid, monthsPaid, packageID, startMonth, startDay) {
    this.replaceEntryID          = entryID;
    this.replacePackageName      = packageName;
    this.replacePreviousPaid     = previousPaid;
    this.replaceMonthsPaid       = monthsPaid;
    this.replacePackageID        = packageID;
    this.replaceStartMonth       = startMonth;
    this.replaceStartDay = startDay;        // ← now correctly in scope
    this.replaceSelectedCustomer = '';
    this.replaceSearch           = '';
    this.replaceCustomers        = [];
    this.replaceShowAll          = false;   // ← reset
    this.showReplaceModal        = true;
    this.searchCustomers('');
},
 
    searchCustomers(query) {
    this.replaceSearching = true;
    const params = new URLSearchParams({ q: query });
    if (this.replacePackageID)  params.append('packageID',  this.replacePackageID);
    if (this.replaceStartMonth) params.append('startMonth', this.replaceStartMonth);
    if (this.replaceStartDay)   params.append('startDay',   this.replaceStartDay);

    fetch(`/admin/paluwagan/customers/search?${params}`)
        .then(r => r.json())
        .then(data => {
            this.replaceCustomers  = data.customers  || [];
            this.replaceWaitingOnly = data.waitingOnly ?? false;  // ← set from API
            this.replaceSearching  = false;
        })
        .catch(() => { this.replaceSearching = false; });
},

searchAllCustomers(query) {
    this.replaceSearching = true;
    const params = new URLSearchParams({ q: query });

    fetch(`/admin/paluwagan/customers/search?${params}`)
        .then(r => r.json())
        .then(data => {
            this.replaceCustomers = data.customers || [];
            this.replaceSearching = false;
        })
        .catch(() => { this.replaceSearching = false; });
},

        submitReplace() {
            if (!this.replaceSelectedCustomer) { alert('Please select a customer'); return; }
            this.replaceLoading = true;

            fetch(`/admin/paluwagan/entry/${this.replaceEntryID}/reassign`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ customerID: this.replaceSelectedCustomer })
            })
            .then(r => r.json())
            .then(res => {
                this.replaceLoading = false;
                if (res.success) {
                    this.showReplaceModal = false;
                    showToast('Customer replaced successfully! Previous payments retained.');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(res.message || 'Replace failed', 'error');
                }
            })
            .catch(() => { this.replaceLoading = false; showToast('Error replacing customer', 'error'); });
        },

        /* ✅ Mark Complete — now uses modal, not confirm() */
        confirmMarkComplete(entryID) {
            this.completeEntryID  = entryID;
            this.showCompleteModal = true;
        },

        doMarkComplete() {
            fetch(`/admin/paluwagan/entry/${this.completeEntryID}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message);
                this.showCompleteModal = false;
                showToast(data.message || 'Marked as complete');
                setTimeout(() => location.reload(), 1500);
            })
            .catch(err => {
                this.showCompleteModal = false;
                showToast(err.message || 'Error completing subscription', 'error');
            });
        },

        deletePackage() {
            fetch(`/admin/paluwagan/package/${this.editPackageID}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(async res => {
                const text = await res.text();
                try { return JSON.parse(text); }
                catch (e) { throw new Error('Server did not return JSON'); }
            })
            .then(res => {
                if (res.success) {
                    this.showDeleteModal = false;
                    showToast('Package deleted successfully!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(res.message || 'Delete failed', 'error');
                }
            })
            .catch(err => console.error(err));
        }
    }"
    class="px-4 sm:px-10 py-8">

    {{-- ============================================ --}}
    {{-- ✅ MARK COMPLETE CONFIRMATION MODAL --}}
    {{-- ============================================ --}}
    <div x-show="showCompleteModal" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div @click.away="showCompleteModal = false"
             class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-50 mb-4">
                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Mark as Complete?</h3>
                <p class="text-gray-500 mt-2 text-sm">
                    This will mark the subscription as fully completed. This action cannot be undone.
                </p>
            </div>
            <div class="flex gap-3 mt-8">
                <button @click="showCompleteModal = false"
                    class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 font-semibold">
                    Cancel
                </button>
                <button @click="doMarkComplete()"
                    class="flex-1 px-4 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 font-semibold shadow-lg shadow-green-200">
                    Yes, Complete
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- DELETE CONFIRMATION MODAL --}}
    {{-- ============================================ --}}
    <div x-show="showDeleteModal" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div @click.away="showDeleteModal = false"
             class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-4">
                    <i class="fas fa-trash-alt text-red-500 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Delete Package?</h3>
                <p class="text-gray-500 mt-2 text-sm">
                    Remove <span class="font-semibold text-gray-800" x-text="editPackageName"></span>? This cannot be undone.
                </p>
            </div>
            <div class="flex gap-3 mt-8">
                <button @click="showDeleteModal = false"
                    class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 font-semibold">
                    Cancel
                </button>
                <button @click="deletePackage()"
                    class="flex-1 px-4 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 font-semibold shadow-lg shadow-red-200">
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- PAYMENT HISTORY MODAL --}}
    {{-- ============================================ --}}
    <div x-show="showPaymentHistory" x-cloak x-transition
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div @click.away="showPaymentHistory = false"
             class="bg-white rounded-2xl w-full max-w-lg shadow-2xl flex flex-col"
             style="max-height: 90vh;">

            <div class="bg-pink-600 px-5 py-4 flex justify-between items-center rounded-t-2xl flex-shrink-0">
                <div>
                    <h2 class="text-white font-bold text-base">📋 Payment History</h2>
                    <p class="text-pink-200 text-xs" x-text="historyCustomer + ' • ' + historyPackage"></p>
                </div>
                <button @click="showPaymentHistory = false" class="text-white hover:text-pink-200 text-xl font-bold">✕</button>
            </div>

            <div class="overflow-y-auto flex-1 p-5" style="min-height: 0;">
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-green-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500">Total Paid</p>
                        <p class="font-bold text-green-600"
                           x-text="'₱' + Number(historyTotalPaid).toLocaleString('en', {minimumFractionDigits:2})"></p>
                    </div>
                    <div class="bg-red-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500">Remaining</p>
                        <p class="font-bold text-red-600"
                           x-text="'₱' + Number(historyTotalAmount - historyTotalPaid).toLocaleString('en', {minimumFractionDigits:2})"></p>
                    </div>
                </div>

                <div x-show="historyLoading" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-pink-500 text-2xl"></i>
                    <p class="text-gray-400 mt-2 text-sm">Loading payments...</p>
                </div>

                <div x-show="!historyLoading">
                    <template x-if="historyPayments.length === 0">
                        <div class="text-center py-8">
                            <i class="fas fa-receipt text-gray-300 text-3xl"></i>
                            <p class="text-gray-400 mt-2 text-sm">No payments recorded yet</p>
                        </div>
                    </template>

                    <div class="space-y-2">
                        {{-- inside the x-for template for each payment --}}
<template x-for="(payment, index) in historyPayments" :key="index">
    <div class="flex items-center justify-between p-3 rounded-xl border"
         :class="payment.status === 'approved' || payment.status === 'paid'
                 ? 'bg-green-50 border-green-200'
                 : 'bg-yellow-50 border-yellow-200'">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold"
                 :class="payment.status === 'approved' || payment.status === 'paid'
                         ? 'bg-green-500 text-white'
                         : 'bg-yellow-400 text-white'"
                 x-text="index + 1"></div>
            <div>
                <p class="font-semibold text-sm text-gray-800" x-text="payment.monthLabel"></p>
                <p class="text-xs text-green-600" x-text="'Paid: ' + payment.paidAt"></p>
                <p x-show="payment.method" class="text-xs text-gray-400"
                   x-text="'via ' + payment.method"></p>
            </div>
        </div>
        <div class="text-right">
            <p class="font-bold text-sm"
               :class="payment.status === 'approved' || payment.status === 'paid'
                       ? 'text-green-600' : 'text-yellow-600'"
               x-text="'₱' + Number(payment.amountPaid).toLocaleString('en', {minimumFractionDigits:2})"></p>
            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold"
                  :class="payment.status === 'approved' || payment.status === 'paid'
                          ? 'bg-green-100 text-green-700'
                          : 'bg-yellow-100 text-yellow-700'"
                  x-text="payment.status === 'pending' ? 'Processing' : 'Paid'">
            </span>
        </div>
    </div>
</template>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 border-t flex-shrink-0">
                <button @click="showPaymentHistory = false"
                        class="w-full py-2.5 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 font-semibold text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
{{-- REPLACE CUSTOMER MODAL — paste over existing one --}}
{{-- ============================================ --}}
<div x-show="showReplaceModal" x-cloak x-transition
     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
    <div @click.away="showReplaceModal = false"
         class="bg-white rounded-2xl w-full max-w-md shadow-2xl flex flex-col"
         style="max-height: 90vh;">

        <div class="bg-blue-600 px-5 py-4 flex justify-between items-center rounded-t-2xl flex-shrink-0">
            <div>
                <h2 class="text-white font-bold text-base">🔄 Replace Customer</h2>
                <p class="text-blue-200 text-xs" x-text="replacePackageName"></p>
            </div>
            <button @click="showReplaceModal = false" class="text-white hover:text-blue-200 text-xl font-bold">✕</button>
        </div>

        <div class="overflow-y-auto flex-1 p-5 space-y-4" style="min-height: 0;">

            {{-- Previous payment info --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <div class="flex items-start gap-2">
                    <i class="fas fa-info-circle text-yellow-500 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-sm text-yellow-800">Previous Payment Progress</p>
                        <p class="text-xs text-yellow-700 mt-1">
                            The cancelled customer already paid
                            <span class="font-bold"
                                  x-text="'₱' + Number(replacePreviousPaid).toLocaleString('en', {minimumFractionDigits:2})"></span>
                            (<span x-text="replaceMonthsPaid"></span> months).
                            This progress is <span class="font-bold">FREE</span> for the new customer.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Waiting list header --}}
            <div x-show="replaceWaitingOnly" class="flex items-center gap-2">
                <span class="text-xs font-bold text-yellow-700 bg-yellow-100 border border-yellow-300
                             px-2 py-0.5 rounded-full">⏳ Waiting List</span>
                <span class="text-xs text-gray-500">customers who already queued for this slot</span>
            </div>

            {{-- Search — only shown in "all customers" mode --}}
            <div x-show="!replaceWaitingOnly || replaceShowAll">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Search Customer</label>
                <input type="text"
                       x-model="replaceSearch"
                       @input.debounce.300ms="searchAllCustomers(replaceSearch)"
                       placeholder="Type customer name..."
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5
                              focus:border-blue-400 focus:outline-none text-sm">
            </div>

            {{-- Customer list --}}
            <div>
                <div x-show="replaceSearching" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin text-blue-500"></i>
                </div>

                <div x-show="!replaceSearching" class="space-y-1.5 max-h-52 overflow-y-auto">

                    {{-- Empty state when no waiting customers --}}
                    <template x-if="replaceCustomers.length === 0 && replaceWaitingOnly && !replaceShowAll">
                        <div class="text-center py-6">
                            <p class="text-gray-400 text-sm">No customers in the waiting list for this slot.</p>
                        </div>
                    </template>

                    {{-- Empty state when searching all --}}
                    <template x-if="replaceCustomers.length === 0 && (!replaceWaitingOnly || replaceShowAll)">
                        <p class="text-gray-400 text-sm text-center py-4">No customers found</p>
                    </template>

                    <template x-for="cust in replaceCustomers" :key="cust.customerID">
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer
                                      transition hover:bg-blue-50"
                               :class="replaceSelectedCustomer == cust.customerID
                                       ? 'border-blue-500 bg-blue-50'
                                       : 'border-gray-200'">
                            <input type="radio"
                                   name="replace_customer"
                                   :value="cust.customerID"
                                   x-model="replaceSelectedCustomer"
                                   class="text-blue-600">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-sm" x-text="cust.name"></p>
                                    <span x-show="cust.isWaiting"
                                          class="text-[10px] font-bold bg-yellow-100 text-yellow-700
                                                 border border-yellow-300 px-1.5 py-0.5 rounded-full
                                                 whitespace-nowrap">
                                        ⏳ Waiting
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 truncate" x-text="cust.email"></p>
                            </div>
                        </label>
                    </template>
                </div>

                {{-- "Assign to any customer" link when there ARE waiting customers --}}
                <div x-show="replaceWaitingOnly && !replaceShowAll && replaceCustomers.length > 0"
                     class="mt-2 text-center">
                    <button @click="replaceShowAll = true; searchAllCustomers('')"
                            class="text-blue-500 text-xs underline hover:text-blue-700">
                        Assign to someone not on the waitlist
                    </button>
                </div>

                {{-- Back to waiting list link --}}
                <div x-show="replaceShowAll" class="mt-2 text-center">
                    <button @click="replaceShowAll = false; searchCustomers('')"
                            class="text-yellow-600 text-xs underline hover:text-yellow-800">
                        ← Back to waiting list
                    </button>
                </div>
            </div>
        </div>

        <div class="px-5 py-3 border-t flex gap-2 flex-shrink-0">
            <button @click="showReplaceModal = false"
                    class="flex-1 py-2.5 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200
                           font-semibold text-sm">
                Cancel
            </button>
            <button @click="submitReplace()"
                    :disabled="replaceLoading || !replaceSelectedCustomer"
                    class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700
                           font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed
                           flex items-center justify-center gap-2">
                <span x-show="!replaceLoading">Replace Customer</span>
                <i x-show="replaceLoading" class="fas fa-spinner fa-spin"></i>
            </button>
        </div>
    </div>
</div>

    {{-- ── APPROVE RELEASE CONFIRMATION MODAL ──────────────── --}}
    <div id="approve-release-modal"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4 hidden">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-50 mb-4">
                    <span class="text-3xl">📦</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Approve Early Release?</h3>
                <p class="text-gray-500 mt-2 text-sm">
                    Release product to
                    <span id="approve-customer-name" class="font-semibold text-gray-800"></span>
                    now.<br>
                    <span class="text-yellow-600 font-medium">Payment schedule continues as normal.</span>
                </p>
            </div>
            <div class="flex gap-3 mt-8">
                <button onclick="closeApproveModal()"
                    class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 font-semibold">
                    Cancel
                </button>
                <button id="confirm-approve-btn"
                    class="flex-1 px-4 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600
                           font-semibold shadow-lg shadow-green-200 flex items-center justify-center gap-2">
                    <span id="approve-btn-text">Yes, Approve</span>
                    <svg id="approve-spinner" class="hidden animate-spin h-4 w-4 text-white"
                         fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ── REJECT RELEASE CONFIRMATION MODAL ───────────────── --}}
    <div id="reject-release-modal"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4 hidden">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-4">
                    <i class="fas fa-times-circle text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Reject Release Request?</h3>
                <p class="text-gray-500 mt-2 text-sm">
                    Reject the release request from
                    <span id="reject-customer-name" class="font-semibold text-gray-800"></span>.
                    <br>Their subscription stays active and they can request again later.
                </p>
            </div>
            <div class="flex gap-3 mt-8">
                <button onclick="closeRejectModal()"
                    class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 font-semibold">
                    Cancel
                </button>
                <button id="confirm-reject-btn"
                    class="flex-1 px-4 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600
                           font-semibold shadow-lg shadow-red-200 flex items-center justify-center gap-2">
                    <span id="reject-btn-text">Yes, Reject</span>
                    <svg id="reject-spinner" class="hidden animate-spin h-4 w-4 text-white"
                         fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- PAGE HEADER --}}
    {{-- ============================================ --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Paluwagan Management</h1>
            <p class="text-gray-500 -mt-1 mb-2 text-sm sm:text-base">
                Monitor and manage paluwagan packages, schedules, and subscriptions
            </p>
        </div>
        <button class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg shadow w-full md:w-auto transition"
                @click="showAdd = true">
            + Add Package
        </button>
    </div>

    {{-- ============================================ --}}
    {{-- SUMMARY CARDS --}}
    {{-- ============================================ --}}
    @php
        $cards = [
            ['title'=>'Active Subscriptions', 'icon'=>'fas fa-file-contract',       'value'=>$summary['activeSubscriptions'] ?? 0,                          'desc'=>'Total active subscriptions',       'color'=>'pink'],
            ['title'=>'Collected Revenue',    'icon'=>'fas fa-circle-check',        'value'=>'₱'.number_format($summary['collectedRevenue'] ?? 0, 2),       'desc'=>'Total revenue collected',          'color'=>'pink'],
            ['title'=>'Expected Revenue',     'icon'=>'fas fa-calendar-days',       'value'=>'₱'.number_format($summary['expectedRevenue'] ?? 0, 2),        'desc'=>'Revenue expected for the month',   'color'=>'pink'],
            ['title'=>'Late Payments',        'icon'=>'fas fa-triangle-exclamation','value'=>$summary['latePayments'] ?? 0,                                 'desc'=>'Late payments this month',         'color'=>'pink'],
            ['title'=>'Pending Releases',     'icon'=>'fas fa-box-open',            'value'=>$summary['pendingReleases'] ?? 0,                              'desc'=>'Early release requests pending',   'color'=>'orange'],
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 sm:gap-5 mb-8">
        @foreach($cards as $card)
        <div class="bg-gradient-to-br {{ $card['color'] === 'orange' ? 'from-orange-50' : 'from-pink-50' }} to-white border {{ $card['color'] === 'orange' ? 'border-orange-100' : 'border-pink-100' }} shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
            <div class="flex items-start justify-between">
                <p class="text-gray-600 font-semibold text-sm sm:text-base">{{ $card['title'] }}</p>
                <div class="bg-white/80 rounded-full p-2 border {{ $card['color'] === 'orange' ? 'border-orange-100' : 'border-pink-100' }}">
                    <i class="{{ $card['icon'] }} {{ $card['color'] === 'orange' ? 'text-orange-500' : 'text-pink-500' }}"></i>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold mt-4">{{ $card['value'] }}</h3>
            <p class="text-gray-400 text-xs mt-1">{{ $card['desc'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ============================================ --}}
    {{-- EARLY RELEASE REQUESTS PANEL --}}
    {{-- ============================================ --}}
    @php
        $pendingReleaseEntries = collect($subscriptions)->filter(fn($s) => $s['status'] === 'release_requested');
    @endphp

    @if($pendingReleaseEntries->count() > 0)
    <div class="border-2 border-orange-300 rounded-xl p-5 bg-orange-50 shadow-sm mb-8">
        <div class="flex items-center gap-3 mb-4">
            <span class="text-2xl">📦</span>
            <div>
                <h2 class="text-lg font-semibold text-orange-800">
                    Early Release Requests
                    <span class="ml-2 bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full">
                        {{ $pendingReleaseEntries->count() }}
                    </span>
                </h2>
                <p class="text-xs text-orange-600">
                    These customers want their product released before full payment is completed
                </p>
            </div>
        </div>

        <div class="space-y-3">
            @foreach($pendingReleaseEntries as $sub)
            <div class="bg-white rounded-xl border border-orange-200 p-4 flex flex-col md:flex-row
                        md:items-center md:justify-between gap-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-bold text-gray-800">{{ $sub['customerName'] }}</span>
                        <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">
                            Entry #{{ $sub['entryID'] }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600">📦 {{ $sub['packageName'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Requested: {{ $sub['releaseRequestedAt'] }}
                        @if($sub['releaseNote'])
                            &bull; Note: <em class="text-gray-700">"{{ $sub['releaseNote'] }}"</em>
                        @endif
                    </p>
                    <div class="flex flex-wrap gap-4 mt-2 text-xs text-gray-500">
                        <span>Paid: <strong class="text-green-600">₱{{ number_format($sub['totalPaid'], 2) }}</strong></span>
                        <span>Remaining Balance: <strong class="text-red-500">₱{{ number_format($sub['totalAmount'] - $sub['totalPaid'], 2) }}</strong></span>
                        <span>Progress: <strong>{{ $sub['monthsPaid'] }}/{{ $sub['totalMonths'] }} months</strong></span>
                    </div>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <button onclick="approveRelease({{ $sub['entryID'] }}, '{{ addslashes($sub['customerName']) }}')"
                        class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-600 transition">
                        ✅ Approve
                    </button>
                    <button onclick="rejectRelease({{ $sub['entryID'] }}, '{{ addslashes($sub['customerName']) }}')"
                        class="bg-red-400 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-500 transition">
                        ✕ Reject
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- PALUWAGAN PACKAGES TABLE --}}
    {{-- ============================================ --}}
    <div class="border rounded-xl border-pink-200 p-5 bg-white shadow-sm mb-10">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Paluwagan Packages</h2>
        </div>

        <div class="max-h-[500px] overflow-y-auto border rounded-lg">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm sm:text-base min-w-max">
                    <thead class="border-b text-gray-600 bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="py-2 px-3">ID</th>
                            <th class="py-2 px-3">Package Name</th>
                            <th class="py-2 px-3">What's Included</th>
                            <th class="py-2 px-3">Image</th>
                            <th class="py-2 px-3">Duration</th>
                            <th class="py-2 px-3">Monthly</th>
                            <th class="py-2 px-3">Total</th>
                            <th class="py-2 px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                        <tr class="border-b hover:bg-pink-50 transition">
                            <td class="py-2 px-3">{{ $package->packageID }}</td>
                            <td class="py-2 px-3 font-medium">{{ $package->packageName }}</td>
                            <td class="py-2 px-3">
                                @php $lines = preg_split('/\r\n|\r|\n|\. /', $package->description); @endphp
                                <ul class="list-disc pl-5">
                                    @foreach($lines as $item)
                                        @if(trim($item)) <li>{{ trim($item) }}</li> @endif
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-2 px-3">
                                @if($package->image && file_exists(storage_path('app/public/products/' . $package->image)))
                                    <img src="{{ asset('images/products/' . $package->image) }}"
                                         class="w-20 h-20 object-cover rounded">
                                @else
                                    <span class="text-gray-400">No image</span>
                                @endif
                            </td>
                            <td class="py-2 px-3">{{ $package->durationMonths }}</td>
                            <td class="py-2 px-3">₱{{ number_format($package->monthlyPayment, 2) }}</td>
                            <td class="py-2 px-3">₱{{ number_format($package->totalAmount, 2) }}</td>
                            <td class="py-2 px-3 flex gap-2">
                                <button class="text-pink-600 hover:text-pink-800 text-sm"
                                    @click="
                                        showEdit = true;
                                        editPackageID = {{ $package->packageID }};
                                        editPackageName = '{{ addslashes($package->packageName) }}';
                                        editPackageDescription = `{{ addslashes($package->description) }}`;
                                        editPackageTotal = '{{ $package->totalAmount }}';
                                        editPackageDuration = '{{ $package->durationMonths }}';
                                        $nextTick(() => preCheckEditProducts(editPackageDescription));
                                    ">Edit</button>
                                <button class="text-red-500 hover:text-red-700 font-medium"
                                    @click="
                                        editPackageID = {{ $package->packageID }};
                                        editPackageName = '{{ addslashes($package->packageName) }}';
                                        showDeleteModal = true;
                                    ">Delete</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-400">No packages found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
</div>
 

{{-- ============================================ --}}
{{-- ADD PACKAGE MODAL --}}
{{-- ============================================ --}}
<div x-show="showAdd" x-cloak x-transition
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
    <div @click.away="showAdd = false"
         class="bg-white rounded-xl shadow-lg w-full max-w-4xl max-h-[90vh] flex flex-col">

        {{-- Header --}}
        <div class="bg-pink-600 px-6 py-4 rounded-t-xl flex justify-between items-center flex-shrink-0">
            <h3 class="text-white font-bold text-lg">Create Paluwagan Package</h3>
            <button @click="showAdd = false" class="text-white hover:text-pink-200 text-xl font-bold">✕</button>
        </div>

        {{-- Body: 2-column --}}
        <div class="flex flex-col md:flex-row overflow-hidden flex-1 min-h-0">

            {{-- LEFT: Form --}}
            <form id="addPackageForm" enctype="multipart/form-data"
                  class="flex-1 p-6 overflow-y-auto space-y-4 border-r border-gray-100">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-1">Package Name</label>
                    <input type="text" name="packageName"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        What's Included
                        <span class="text-xs text-gray-400 font-normal ml-1">(auto-filled from checked products, or type manually)</span>
                    </label>
                    <textarea id="addDescriptionField" name="description" rows="5"
                              class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none resize-none"
                              placeholder="Check products on the right, or type here..." required></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Total Amount</label>
                    <input type="number" step="0.01" name="totalAmount"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Duration (Months)</label>
                    <input type="number" name="durationMonths"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Package Image</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full border p-2 rounded-lg border-pink-200" required>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showAdd = false"
                            class="px-4 py-2 border rounded-lg hover:bg-gray-100 text-sm">Cancel</button>
                    <button type="submit"
                            class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 text-sm font-semibold">
                        Create Package
                    </button>
                </div>
            </form>

            {{-- RIGHT: Product Picker --}}
            <div class="w-full md:w-72 flex flex-col p-4 bg-gray-50 overflow-hidden min-h-0">
                <p class="text-sm font-semibold text-gray-700 mb-2 flex-shrink-0">
                    Select Products to Include
                </p>

                {{-- Search --}}
                <input type="text" id="productPickerSearch"
                       placeholder="Search products..."
                       class="w-full border rounded-lg px-3 py-2 text-sm mb-3 focus:ring-2 focus:ring-pink-300 outline-none flex-shrink-0">

                {{-- Product list --}}
                <div id="productPickerList"
                     class="overflow-y-auto flex-1 space-y-1.5 pr-1">
                    @php $grouped = $paluwaganItems->groupBy('category'); @endphp

                    @forelse($grouped as $category => $items)
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mt-3 mb-1 px-1">
                            {{ $category }}
                        </p>
                        @foreach($items as $item)
                        <label class="flex items-center gap-3 p-2.5 rounded-lg border border-gray-200
                                    bg-white cursor-pointer hover:border-pink-300 hover:bg-pink-50
                                    transition product-pick-item"
                            data-name="{{ strtolower($item->name) }}">
                            <input type="checkbox"
                                class="product-pick-checkbox accent-pink-500 w-4 h-4 flex-shrink-0"
                                value="{{ $item->name }}">
                            <p class="text-sm text-gray-800">{{ $item->name }}</p>
                        </label>
                        @endforeach
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">No items found</p>
                    @endforelse
                </div>

                {{-- Selected count --}}
                <p class="text-xs text-pink-500 font-medium mt-3 flex-shrink-0" id="productPickerCount">
                    0 product(s) selected
                </p>
            </div>
        </div>
    </div>
</div>

    {{-- ============================================ --}}
{{-- EDIT PACKAGE MODAL --}}
{{-- ============================================ --}}
<div x-show="showEdit" x-cloak x-transition
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
    <div @click.away="showEdit = false"
         class="bg-white rounded-xl shadow-lg w-full max-w-4xl max-h-[90vh] flex flex-col">

        {{-- Header --}}
        <div class="bg-pink-600 px-6 py-4 rounded-t-xl flex justify-between items-center flex-shrink-0">
            <h3 class="text-white font-bold text-lg">Edit Paluwagan Package</h3>
            <button @click="showEdit = false" class="text-white hover:text-pink-200 text-xl font-bold">✕</button>
        </div>

        {{-- Body: 2-column --}}
        <div class="flex flex-col md:flex-row overflow-hidden flex-1 min-h-0">

            {{-- LEFT: Form --}}
            <form x-ref="editForm" enctype="multipart/form-data"
                  class="flex-1 p-6 overflow-y-auto space-y-4 border-r border-gray-100"
                  @submit.prevent="
                    const formData = new FormData($refs.editForm);
                    formData.append('_method', 'PUT');
                    fetch(`/admin/paluwagan/package/${editPackageID}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            showEdit = false;
                            showToast('Package updated successfully!');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast(res.message || 'Update failed', 'error');
                        }
                    })
                    .catch(err => { console.error(err); showToast('Error updating package', 'error'); });
                  ">

                <div>
                    <label class="block text-sm font-medium mb-1">Package Name</label>
                    <input type="text" name="packageName" x-model="editPackageName"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        What's Included
                        <span class="text-xs text-gray-400 font-normal ml-1">(auto-filled from checked products, or type manually)</span>
                    </label>
                    <textarea id="editDescriptionField" name="description"
                              x-model="editPackageDescription"
                              rows="5"
                              class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none resize-none"
                              required></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Total Amount</label>
                    <input type="number" name="totalAmount" x-model="editPackageTotal"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Duration (Months)</label>
                    <input type="number" name="durationMonths" x-model="editPackageDuration"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Change Image (optional)</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full border p-2 rounded-lg border-pink-200">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showEdit = false"
                            class="px-4 py-2 border rounded-lg hover:bg-gray-100 text-sm">Cancel</button>
                    <button type="submit"
                            class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 text-sm font-semibold">
                        Update Package
                    </button>
                </div>
            </form>

            {{-- RIGHT: Product Picker --}}
            <div class="w-full md:w-72 flex flex-col p-4 bg-gray-50 overflow-hidden min-h-0">
                <p class="text-sm font-semibold text-gray-700 mb-2 flex-shrink-0">
                    Select Products to Include
                </p>

                {{-- Search --}}
                <input type="text" id="editProductPickerSearch"
                       placeholder="Search products..."
                       class="w-full border rounded-lg px-3 py-2 text-sm mb-3 focus:ring-2 focus:ring-pink-300 outline-none flex-shrink-0">

                {{-- Product list --}}
                <div id="editProductPickerList"
                     class="overflow-y-auto flex-1 space-y-1.5 pr-1">
                    @php $grouped = $paluwaganItems->groupBy('category'); @endphp

                    @forelse($grouped as $category => $items)
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mt-3 mb-1 px-1">
                            {{ $category }}
                        </p>
                        @foreach($items as $item)
                        <label class="flex items-center gap-3 p-2.5 rounded-lg border border-gray-200
                                      bg-white cursor-pointer hover:border-pink-300 hover:bg-pink-50
                                      transition edit-product-pick-item"
                               data-name="{{ strtolower($item->name) }}">
                            <input type="checkbox"
                                   class="edit-product-pick-checkbox accent-pink-500 w-4 h-4 flex-shrink-0"
                                   value="{{ $item->name }}">
                            <p class="text-sm text-gray-800">{{ $item->name }}</p>
                        </label>
                        @endforeach
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">No items found</p>
                    @endforelse
                </div>

                {{-- Selected count --}}
                <p class="text-xs text-pink-500 font-medium mt-3 flex-shrink-0" id="editProductPickerCount">
                    0 product(s) selected
                </p>
            </div>
        </div>
    </div>
</div>

    {{-- ============================================ --}}
    {{-- MONTH AVAILABILITY --}}
    {{-- ============================================ --}}
    <div class="border rounded-xl border-pink-200 p-5 bg-white shadow-sm mb-8">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-4">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Month Availability per Package</h2>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" id="searchPackage" placeholder="Search package..."
                        class="border pl-9 pr-3 py-2 rounded-lg w-full md:w-64 text-sm focus:outline-none focus:ring-2 focus:ring-pink-400">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                </div>
            </div>
        </div>

        {{-- Package pagination controls --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 mb-3 text-xs text-gray-500">
            <span id="pkg-count-label"></span>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500">Rows per page</label>
                <select id="pkg-per-page" class="border rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-pink-400 bg-white">
                    <option value="3">3</option>
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                </select>
            </div>
        </div>

        <div class="max-h-[500px] overflow-y-auto pr-2">
            @foreach($packages as $package)
            <div class="mb-4 border rounded-lg p-4 package-item" data-pkg-index="{{ $loop->index }}">
                <div class="flex justify-between items-center cursor-pointer package-header">
                    <h3 class="font-bold package-name">{{ $package->packageName }}</h3>
                    <div class="flex gap-2">
                        <button class="bulk-activate text-xs px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                                data-package="{{ $package->packageID }}">Activate All</button>
                        <button class="bulk-deactivate text-xs px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                                data-package="{{ $package->packageID }}">Deactivate All</button>
                    </div>
                </div>
                <div class="grid grid-cols-6 gap-3 mt-3 month-grid hidden">
                    @foreach(range(1,12) as $month)
                        @php
                            $record   = $package->monthAvailability->firstWhere('month', $month);
                            $isActive = $record ? $record->status === 'active' : false;
                        @endphp
                        <div data-month="{{ $month }}" data-package="{{ $package->packageID }}"
                            class="month-box p-2 border rounded cursor-pointer transition {{ $isActive ? 'bg-pink-100 border-pink-400' : '' }}">
                            <div class="flex justify-between items-center">
                                <span class="text-sm">{{ \Carbon\Carbon::create()->month($month)->format('F') }}</span>
                                <input type="checkbox" class="toggle-month" {{ $isActive ? 'checked' : '' }}>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
            {{-- Package Pagination --}}
    <div class="flex items-center justify-between gap-3 mt-3 text-xs text-gray-500">
        <span id="pkg-count-label-bottom"></span>
        <div class="flex items-center gap-1" id="pkg-page-buttons"></div>
    </div>
    </div>

    {{-- ============================================ --}}
{{-- CUSTOMER SUBSCRIPTIONS TABLE --}}
{{-- ============================================ --}}
<div class="border rounded-xl border-pink-200 p-5 bg-white shadow-sm">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-4">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Customer Subscriptions</h2>
        <div class="flex items-center gap-2">
            <button onclick="openPenaltyModal()"
                class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg font-semibold transition">
                🔴 Apply Penalties Now
            </button>
            <label class="text-xs text-gray-500 whitespace-nowrap">Rows per page</label>
            <select id="sub-per-page" class="border rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-pink-400 bg-white">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-3 mb-4">
        <input type="text" id="searchCustomer" placeholder="Search customer..."
            class="border px-3 py-2 rounded-lg w-full md:w-2/4 text-sm focus:outline-none focus:ring-2 focus:ring-pink-400">
        <select id="statusFilter" class="border px-3 py-2 rounded-lg w-full md:w-1/4 text-sm focus:outline-none focus:ring-2 focus:ring-pink-400">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="release_requested">Release Requested</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <select id="dueFilter" class="border px-3 py-2 rounded-lg w-full md:w-1/4 text-sm focus:outline-none focus:ring-2 focus:ring-pink-400">
            <option value="">All Due Dates</option>
            <option value="today">Due Today</option>
            <option value="week">Due This Week</option>
            <option value="overdue">Overdue</option>
        </select>
        <select id="scheduleStatusFilter" class="border px-3 py-2 rounded-lg w-full md:w-1/4 text-sm focus:outline-none focus:ring-2 focus:ring-pink-400">
            <option value="">All Schedule Status</option>
            <option value="late">Late</option>
            <option value="pending">Pending</option>
            <option value="paid">All Paid</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm sm:text-base min-w-max">
            <thead class="border-b text-gray-600 bg-gray-50">
                <tr>
                    <th class="py-2 px-3">Entry ID</th>
                    <th class="py-2 px-3">Customer</th>
                    <th class="py-2 px-3">Package</th>
                    <th class="py-2 px-3 text-center">Progress</th>
                    <th class="py-2 px-3">Monthly</th>
                    <th class="py-2 px-3">Paid</th>
                    <th class="py-2 px-3">Balance</th>
                    <th class="py-2 px-3">Next Due</th>
                    <th class="py-2 px-3">Release Date & Time</th>
                    <th class="py-2 px-3">Status</th>
                    <th class="py-2 px-3">Payment Status</th>
                    <th class="py-2 px-3">Actions</th>
                </tr>
            </thead>
            <tbody id="sub-tbody">
                @forelse($subscriptions as $sub)
                <tr class="border-b hover:bg-pink-50 transition subscription-row"
                    data-name="{{ strtolower($sub['customerName']) }}"
                    data-status="{{ $sub['status'] }}"
                    data-due="{{ $sub['nextDueDate'] }}"
                    data-sched-status="{{ $sub['scheduleStatus'] ?? 'pending' }}"
                    data-sub-index="{{ $loop->index }}">

                    <td class="py-2 px-3">{{ $sub['entryID'] }}</td>
                    <td class="py-2 px-3 font-medium">{{ $sub['customerName'] }}</td>
                    <td class="py-2 px-3">{{ $sub['packageName'] }}</td>

                    <td class="py-2 px-3 text-center">
                        @php
                            $percent = $sub['totalMonths'] > 0
                                ? round(($sub['monthsPaid'] / $sub['totalMonths']) * 100) : 0;
                        @endphp
                        <div class="relative w-12 h-12 mx-auto">
                            <svg class="w-12 h-12 transform -rotate-90">
                                <circle cx="24" cy="24" r="20" stroke="#e5e7eb" stroke-width="4" fill="none"/>
                                <circle cx="24" cy="24" r="20" stroke="#ec4899" stroke-width="4" fill="none"
                                    stroke-dasharray="126"
                                    stroke-dashoffset="{{ 126 - (126 * $percent / 100) }}"
                                    stroke-linecap="round"/>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-xs font-bold">
                                {{ $percent }}%
                            </span>
                        </div>
                    </td>

                    <td class="py-2 px-3">₱{{ number_format($sub['monthlyPayment'], 2) }}</td>
                    <td class="py-2 px-3 text-green-600 font-semibold">₱{{ number_format($sub['totalPaid'], 2) }}</td>
                    <td class="py-2 px-3 text-red-500 font-semibold">
                        ₱{{ number_format(max(0, ($sub['totalAmount'] + ($sub['totalPenalty'] ?? 0)) - $sub['totalPaid']), 2) }}
                    </td>
                    <td class="py-2 px-3">
                        {{ $sub['nextDueDate']
                            ? \Carbon\Carbon::parse($sub['nextDueDate'])->format('M d, Y')
                            : '-' }}
                    </td>

                    <td>
                        @if(!empty($sub['releaseDate']))
                            <span class="block font-medium text-gray-800">{{ $sub['releaseDate'] }}</span>
                            @if(!empty($sub['releaseTime']))
                                <span class="text-xs text-gray-500"> {{ $sub['releaseTime'] }}</span>
                            @endif
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>

                    <td class="py-2 px-3">
                        <div class="flex flex-col gap-1">
                            <span class="px-2 py-1 text-xs rounded-full
                                @switch($sub['status'])
                                    @case('active')            bg-green-100  text-green-800  @break
                                    @case('release_requested') bg-orange-100 text-orange-800 @break
                                    @case('completed')         bg-blue-100   text-blue-800   @break
                                    @case('cancelled')         bg-red-100    text-red-800    @break
                                @endswitch">
                                @if($sub['status'] === 'release_requested')
                                    ⏳ Release Pending
                                @else
                                    {{ ucfirst($sub['status']) }}
                                @endif
                            </span>
                            @if(!empty($sub['releasedAt']))
                                <span class="text-[10px] text-green-600 font-semibold whitespace-nowrap">
                                    📦 Released {{ $sub['releasedAt'] }}
                                </span>
                            @endif
                        </div>
                    </td>

                    <td class="py-2 px-3">
                        @php $ss = $sub['scheduleStatus'] ?? 'pending'; @endphp
                        <span class="px-2 py-1 text-xs rounded-full
                            @switch($ss)
                                @case('late')      bg-red-100    text-red-800    @break
                                @case('pending')   bg-yellow-100 text-yellow-800 @break
                                @case('paid')      bg-green-100  text-green-800  @break
                                @case('cancelled') bg-gray-100   text-gray-800   @break
                            @endswitch">
                            {{ ucfirst($ss) }}
                        </span>
                    </td>   

                    <td class="py-2 px-3">
                        <div class="flex gap-1 flex-wrap">
                            <button @click="openPaymentHistory(
                                        '{{ $sub['entryID'] }}',
                                        '{{ addslashes($sub['customerName']) }}',
                                        '{{ addslashes($sub['packageName']) }}')"
                                class="bg-purple-500 text-white px-2.5 py-1 rounded text-xs hover:bg-purple-600">
                                <i class="fas fa-receipt"></i> History
                            </button>
                            @if($sub['status'] === 'release_requested')
                                <button onclick="approveRelease({{ $sub['entryID'] }}, '{{ addslashes($sub['customerName']) }}')"
                                    class="bg-green-500 text-white px-2.5 py-1 rounded text-xs hover:bg-green-600">
                                    ✅ Release
                                </button>
                                <button onclick="rejectRelease({{ $sub['entryID'] }}, '{{ addslashes($sub['customerName']) }}')"
                                    class="bg-red-400 text-white px-2.5 py-1 rounded text-xs hover:bg-red-500">
                                    ✕ Reject
                                </button>
                            @endif
                            @if($sub['status'] === 'active')
                                <button @click="confirmMarkComplete('{{ $sub['entryID'] }}')"
                                    class="bg-green-500 text-white px-2.5 py-1 rounded text-xs hover:bg-green-600">
                                    Complete
                                </button>
                            @endif
                                                        @if($sub['status'] === 'cancelled')
                                <button @click="openReplaceModal(
                                            '{{ $sub['entryID'] }}',
                                            '{{ addslashes($sub['packageName']) }}',
                                            {{ $sub['totalPaid'] }},
                                            {{ $sub['monthsPaid'] }},
                                            '{{ $sub['packageID'] ?? '' }}',
                                            '{{ $sub['startMonth'] ?? '' }}',
                                            '{{ $sub['startDay'] ?? '' }}')"
                                    class="bg-blue-500 text-white px-2.5 py-1 rounded text-xs hover:bg-blue-600">
                                    <i class="fas fa-user-plus"></i> Replace
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="sub-empty-row">
                    <td colspan="10" class="py-10 text-center text-gray-400">No subscriptions found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Subscription Pagination --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 text-xs text-gray-500">
        <span id="sub-count-label"></span>
        <div class="flex items-center gap-1" id="sub-page-buttons"></div>
    </div>
</div>
<!-- Penalty Confirmation Modal -->
<div id="penaltyModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <span class="text-red-600 text-xl">⚠️</span>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Confirm Action</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to apply penalties to all late payments? This cannot be undone.
                </p>
            </div>
            <div class="items-center px-4 py-3 flex justify-center gap-3">
                <button onclick="closePenaltyModal()" class="px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-300 focus:outline-none transition">
                    Cancel
                </button>
                <button id="confirmPenaltyBtn" onclick="confirmRunPenalties()" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-600 focus:outline-none transition">
                    Yes, Apply
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openPenaltyModal() {
    document.getElementById('penaltyModal').classList.remove('hidden');
}

function closePenaltyModal() {
    document.getElementById('penaltyModal').classList.add('hidden');
}

async function confirmRunPenalties() {
    const btn = document.getElementById('confirmPenaltyBtn');
    
    // Disable ang button para malikayan ang double-click
    btn.disabled = true;
    btn.innerText = 'Processing...';

    try {
        const res = await fetch('/admin/paluwagan/apply-penalties', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await res.json();
        closePenaltyModal(); // I-close ang modal pagkahuman

        if (res.ok) {
            showToast(data.message || 'Penalties applied.', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.error || 'Something went wrong.', 'error');
        }
    } catch (error) {
        closePenaltyModal();
        showToast('Network error. Please try again.', 'error');
    } finally {
        // I-reset ang button state
        btn.disabled = false;
        btn.innerText = 'Yes, Apply';
    }
}
// =============================================
// TOAST
// =============================================
function showToast(message, type = 'success') {
    const toast = document.getElementById('success-toast');
    const text  = document.getElementById('toast-message');
    text.innerText = message;
    toast.firstElementChild.classList.remove('bg-green-600', 'bg-red-600', 'bg-yellow-500');
    if (type === 'success')    toast.firstElementChild.classList.add('bg-green-600');
    else if (type === 'error') toast.firstElementChild.classList.add('bg-red-600');
    else                       toast.firstElementChild.classList.add('bg-yellow-500');
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 2500);
}

// =============================================
// MONTH AVAILABILITY
// =============================================
function updateMonth(packageID, month, status, parent, checkbox) {
    fetch('/admin/paluwagan/month/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ packageID, month, status })
    })
    .then(res => res.json())
    .then(res => {
        if (!res.success) throw new Error();
        if (status === 'active') parent.classList.add('bg-pink-100', 'border-pink-400');
        else parent.classList.remove('bg-pink-100', 'border-pink-400');
    })
    .catch(() => { showToast('Update failed', 'error'); checkbox.checked = !checkbox.checked; });
}

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('toggle-month')) {
        const parent = e.target.closest('.month-box');
        updateMonth(parent.dataset.package, parent.dataset.month,
            e.target.checked ? 'active' : 'inactive', parent, e.target);
    }
});

function bulkAction(packageID, activate) {
    document.querySelectorAll(`.month-box[data-package="${packageID}"]`).forEach(box => {
        const checkbox = box.querySelector('.toggle-month');
        checkbox.checked = activate;
        updateMonth(packageID, box.dataset.month, activate ? 'active' : 'inactive', box, checkbox);
    });
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('bulk-activate')) {
        e.stopPropagation();
        bulkAction(e.target.dataset.package, true);
    }
    if (e.target.classList.contains('bulk-deactivate')) {
        e.stopPropagation();
        bulkAction(e.target.dataset.package, false);
    }
    if (e.target.closest('.package-header')) {
        const grid = e.target.closest('.package-item').querySelector('.month-grid');
        grid.classList.toggle('hidden');
    }
});

// =============================================
// PACKAGE PAGINATION
// =============================================
const pkgItems        = [...document.querySelectorAll('.package-item')];
const pkgCountLabel   = document.getElementById('pkg-count-label');
const pkgCountBottom  = document.getElementById('pkg-count-label-bottom');
const pkgPageButtons  = document.getElementById('pkg-page-buttons');
const pkgPerPageSel   = document.getElementById('pkg-per-page');
const pkgSearchInput  = document.getElementById('searchPackage');

let pkgPage    = 1;
let pkgPerPage = parseInt(pkgPerPageSel.value);
let pkgSearch  = '';

const btnClass = (active) =>
    `px-2.5 py-1 rounded-lg border text-xs font-medium transition ${
        active
        ? 'bg-pink-500 text-white border-pink-500'
        : 'bg-white text-gray-600 border-gray-200 hover:border-pink-300'
    }`;

function renderPackages() {
    const sq = pkgSearch.toLowerCase();
    const visible = pkgItems.filter(item =>
        item.querySelector('.package-name').textContent.toLowerCase().includes(sq)
    );

    const total      = visible.length;
    const totalPages = Math.max(1, Math.ceil(total / pkgPerPage));
    pkgPage          = Math.min(pkgPage, totalPages);
    const start      = (pkgPage - 1) * pkgPerPage;
    const end        = start + pkgPerPage;

    pkgItems.forEach(item => item.style.display = 'none');
    visible.slice(start, end).forEach(item => item.style.display = '');

    const from = total === 0 ? 0 : start + 1;
    const to   = Math.min(end, total);
    const label = total === 0 ? 'No packages match' : `Showing ${from}–${to} of ${total} package(s)`;
    if (pkgCountLabel)  pkgCountLabel.textContent  = label;
    if (pkgCountBottom) pkgCountBottom.textContent = label;

    pkgPageButtons.innerHTML = '';
    if (totalPages <= 1) return;

    const makeBtn = (label, target, active, disabled) => {
        const btn = document.createElement('button');
        btn.textContent = label;
        btn.className   = btnClass(active);
        btn.disabled    = disabled;
        if (disabled) btn.style.opacity = '0.4';
        btn.addEventListener('click', () => { pkgPage = target; renderPackages(); });
        return btn;
    };

    pkgPageButtons.appendChild(makeBtn('‹', pkgPage - 1, false, pkgPage === 1));
    for (let p = 1; p <= totalPages; p++) {
        if (totalPages > 7 && Math.abs(p - pkgPage) > 2 && p !== 1 && p !== totalPages) {
            if (p === pkgPage - 3 || p === pkgPage + 3) {
                const dots = document.createElement('span');
                dots.textContent = '…';
                dots.style.padding = '0 4px';
                pkgPageButtons.appendChild(dots);
            }
            continue;
        }
        pkgPageButtons.appendChild(makeBtn(p, p, p === pkgPage, false));
    }
    pkgPageButtons.appendChild(makeBtn('›', pkgPage + 1, false, pkgPage === totalPages));
}

pkgSearchInput.addEventListener('input', function () {
    pkgSearch = this.value;
    pkgPage   = 1;
    renderPackages();
});

pkgPerPageSel.addEventListener('change', function () {
    pkgPerPage = parseInt(this.value);
    pkgPage    = 1;
    renderPackages();
});

// =============================================
// ADD PACKAGE
// =============================================
document.getElementById('addPackageForm').addEventListener('submit', function (e) {
    e.preventDefault();
    fetch('{{ url("admin/paluwagan/package/create") }}', {
        method: 'POST', body: new FormData(this)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) { showToast('Package created!'); setTimeout(() => location.reload(), 1500); }
        else showToast(res.message || 'Create failed', 'error');
    })
    .catch(() => showToast('Error creating package', 'error'));
});


// =============================================
// PRODUCT PICKER (Add Package Modal)
// =============================================
function syncProductsToDescription() {
    const checked = [...document.querySelectorAll('.product-pick-checkbox:checked')]
        .map(cb => cb.value);

    document.getElementById('addDescriptionField').value = checked.join('\n');
    document.getElementById('productPickerCount').textContent =
        `${checked.length} product(s) selected`;
}

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('product-pick-checkbox')) {
        // Highlight the label
        const label = e.target.closest('label');
        if (e.target.checked) {
            label.classList.add('border-pink-400', 'bg-pink-50');
        } else {
            label.classList.remove('border-pink-400', 'bg-pink-50');
        }
        syncProductsToDescription();
    }
});

document.getElementById('productPickerSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.product-pick-item').forEach(item => {
        item.style.display = item.dataset.name.includes(q) ? '' : 'none';
    });
});

// Reset checkboxes when modal closes
document.addEventListener('alpine:initialized', () => {
    // watch for showAdd = false to clear checkboxes
});

// Clear product checkboxes when the Add modal is re-opened
document.addEventListener('click', function (e) {
    if (e.target.closest('[\\@click="showAdd = true"]') ||
        (e.target.tagName === 'BUTTON' && e.target.textContent.includes('Add Package'))) {
        setTimeout(() => {
            document.querySelectorAll('.product-pick-checkbox').forEach(cb => {
                cb.checked = false;
                cb.closest('label').classList.remove('border-pink-400', 'bg-pink-50');
            });
            document.getElementById('addDescriptionField').value = '';
            document.getElementById('productPickerCount').textContent = '0 product(s) selected';
            document.getElementById('productPickerSearch').value = '';
            document.querySelectorAll('.product-pick-item').forEach(i => i.style.display = '');
        }, 50);
    }
});

// =============================================
// PRODUCT PICKER (Edit Package Modal)
// =============================================
function syncEditProductsToDescription() {
    const checked = [...document.querySelectorAll('.edit-product-pick-checkbox:checked')]
        .map(cb => cb.value);

    // sync to textarea AND Alpine x-model
    const textarea = document.getElementById('editDescriptionField');
    textarea.value = checked.join('\n');

    // trigger Alpine to pick up the change
    textarea.dispatchEvent(new Event('input'));

    document.getElementById('editProductPickerCount').textContent =
        `${checked.length} product(s) selected`;
}

function preCheckEditProducts(description) {
    const lines = description.split('\n').map(l => l.trim().toLowerCase()).filter(Boolean);

    document.querySelectorAll('.edit-product-pick-checkbox').forEach(cb => {
        const match = lines.includes(cb.value.trim().toLowerCase());
        cb.checked  = match;

        const label = cb.closest('label');
        if (match) {
            label.classList.add('border-pink-400', 'bg-pink-50');
        } else {
            label.classList.remove('border-pink-400', 'bg-pink-50');
        }
    });

    const count = document.querySelectorAll('.edit-product-pick-checkbox:checked').length;
    document.getElementById('editProductPickerCount').textContent =
        `${count} product(s) selected`;
}

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('edit-product-pick-checkbox')) {
        const label = e.target.closest('label');
        if (e.target.checked) {
            label.classList.add('border-pink-400', 'bg-pink-50');
        } else {
            label.classList.remove('border-pink-400', 'bg-pink-50');
        }
        syncEditProductsToDescription();
    }
});

document.getElementById('editProductPickerSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.edit-product-pick-item').forEach(item => {
        item.style.display = item.dataset.name.includes(q) ? '' : 'none';
    });
});


// =============================================
// SUBSCRIPTION FILTERS + PAGINATION
// =============================================
const searchInput  = document.getElementById('searchCustomer');
const statusFilter = document.getElementById('statusFilter');
const dueFilter    = document.getElementById('dueFilter');
const subPerPageSel = document.getElementById('sub-per-page');
const subRows       = [...document.querySelectorAll('.subscription-row')];
const subEmptyRow   = document.getElementById('sub-empty-row');
const subCountLabel = document.getElementById('sub-count-label');
const subPageBtns   = document.getElementById('sub-page-buttons');

let subPage    = 1;
let subPerPage = parseInt(subPerPageSel.value);

function getVisibleSubs() {
    const sq          = searchInput.value.toLowerCase();
    const statusV     = statusFilter.value;
    const dueV        = dueFilter.value;
    const schedStatV  = document.getElementById('scheduleStatusFilter').value;

    const now       = new Date();
    const todayStr  = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')}`;
    const todayDate = new Date(todayStr);
    const weekLater = new Date(todayDate);
    weekLater.setDate(weekLater.getDate() + 7);

    return subRows.filter(row => {
        let show = true;
        if (sq && !row.dataset.name.includes(sq)) show = false;
        if (statusV && row.dataset.status !== statusV) show = false;
        if (schedStatV && row.dataset.schedStatus !== schedStatV) show = false;
        if (dueV) {
            const rawDue = row.dataset.due;
            if (!rawDue) { show = show && false; }
            else {
                const dueDate = new Date(rawDue.replace(/-/g, '/'));
                if (dueV === 'today') show = show && (dueDate.toDateString() === todayDate.toDateString());
                if (dueV === 'week')  show = show && (dueDate >= todayDate && dueDate <= weekLater);
                if (dueV === 'overdue') show = show && (dueDate < todayDate);
            }
        }
        return show;
    });
}

function renderSubscriptions() {
    const visible    = getVisibleSubs();
    const total      = visible.length;
    const totalPages = Math.max(1, Math.ceil(total / subPerPage));
    subPage          = Math.min(subPage, totalPages);
    const start      = (subPage - 1) * subPerPage;
    const end        = start + subPerPage;

    subRows.forEach(row => row.style.display = 'none');
    visible.slice(start, end).forEach(row => row.style.display = '');

    if (subEmptyRow) subEmptyRow.style.display = total === 0 ? '' : 'none';

    const from = total === 0 ? 0 : start + 1;
    const to   = Math.min(end, total);
    subCountLabel.textContent = total === 0
        ? 'No subscriptions match'
        : `Showing ${from}–${to} of ${total} subscription(s)`;

    subPageBtns.innerHTML = '';
    if (totalPages <= 1) return;

    const makeBtn = (label, target, active, disabled) => {
        const btn = document.createElement('button');
        btn.textContent = label;
        btn.className   = btnClass(active);
        btn.disabled    = disabled;
        if (disabled) btn.style.opacity = '0.4';
        btn.addEventListener('click', () => { subPage = target; renderSubscriptions(); });
        return btn;
    };

    subPageBtns.appendChild(makeBtn('‹', subPage - 1, false, subPage === 1));
    for (let p = 1; p <= totalPages; p++) {
        if (totalPages > 7 && Math.abs(p - subPage) > 2 && p !== 1 && p !== totalPages) {
            if (p === subPage - 3 || p === subPage + 3) {
                const dots = document.createElement('span');
                dots.textContent = '…';
                dots.style.padding = '0 4px';
                subPageBtns.appendChild(dots);
            }
            continue;
        }
        subPageBtns.appendChild(makeBtn(p, p, p === subPage, false));
    }
    subPageBtns.appendChild(makeBtn('›', subPage + 1, false, subPage === totalPages));
}
document.getElementById('scheduleStatusFilter').addEventListener('change', () => { subPage = 1; renderSubscriptions(); });
searchInput.addEventListener('input',   () => { subPage = 1; renderSubscriptions(); });
statusFilter.addEventListener('change', () => { subPage = 1; renderSubscriptions(); });
dueFilter.addEventListener('change',   () => { subPage = 1; renderSubscriptions(); });
subPerPageSel.addEventListener('change', function () {
    subPerPage = parseInt(this.value);
    subPage    = 1;
    renderSubscriptions();
});

// =============================================
// EARLY RELEASE: APPROVE / REJECT
// =============================================
let pendingApproveID   = null;
let pendingRejectID    = null;
let isApprovingRelease = false;
let isRejectingRelease = false;

function approveRelease(entryID, customerName) {
    pendingApproveID = entryID;
    document.getElementById('approve-customer-name').textContent = customerName;
    document.getElementById('approve-release-modal').classList.remove('hidden');
}
function closeApproveModal() {
    document.getElementById('approve-release-modal').classList.add('hidden');
    pendingApproveID = null;
}
function rejectRelease(entryID, customerName) {
    pendingRejectID = entryID;
    document.getElementById('reject-customer-name').textContent = customerName;
    document.getElementById('reject-release-modal').classList.remove('hidden');
}
function closeRejectModal() {
    document.getElementById('reject-release-modal').classList.add('hidden');
    pendingRejectID = null;
}

document.getElementById('confirm-approve-btn').addEventListener('click', async function () {
    if (!pendingApproveID || isApprovingRelease) return;
    isApprovingRelease = true;
    const btnText = document.getElementById('approve-btn-text');
    const spinner = document.getElementById('approve-spinner');
    btnText.textContent = 'Approving...';
    spinner.classList.remove('hidden');
    try {
        const res  = await fetch(`/admin/paluwagan/entry/${pendingApproveID}/approve-release`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        closeApproveModal();
        showToast('Release approved! Payment schedule continues.', 'success');
        setTimeout(() => location.reload(), 1500);
    } catch (err) {
        showToast(err.message || 'Error approving release', 'error');
    } finally {
        isApprovingRelease  = false;
        btnText.textContent = 'Yes, Approve';
        spinner.classList.add('hidden');
    }
});

document.getElementById('confirm-reject-btn').addEventListener('click', async function () {
    if (!pendingRejectID || isRejectingRelease) return;
    isRejectingRelease = true;
    const btnText = document.getElementById('reject-btn-text');
    const spinner = document.getElementById('reject-spinner');
    btnText.textContent = 'Rejecting...';
    spinner.classList.remove('hidden');
    try {
        const res  = await fetch(`/admin/paluwagan/entry/${pendingRejectID}/reject-release`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        closeRejectModal();
        showToast('Release request rejected. Entry is active again.', 'success');
        setTimeout(() => location.reload(), 1500);
    } catch (err) {
        showToast(err.message || 'Error rejecting release', 'error');
    } finally {
        isRejectingRelease  = false;
        btnText.textContent = 'Yes, Reject';
        spinner.classList.add('hidden');
    }
});

document.getElementById('approve-release-modal').addEventListener('click', function (e) {
    if (e.target === this) closeApproveModal();
});
document.getElementById('reject-release-modal').addEventListener('click', function (e) {
    if (e.target === this) closeRejectModal();
});

// =============================================
// INIT
// =============================================
renderPackages();
renderSubscriptions();
</script>
@endsection