<div id="paluwagan-modal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-lg w-full relative overflow-y-auto max-h-[90vh]">
        <button id="close-modal-paluwagan" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

        <!-- STEP 1 -->
        <div id="paluwagan-step1">
            <h2 id="paluwagan-name" class="text-2xl font-bold mb-1"></h2>
            <img id="paluwagan-image" src="" class="rounded-lg w-full h-60 object-cover mb-5">
            
            <div class="mt-4">
                <h3 class="font-semibold text-gray-800 mb-1">What's Included</h3>
                <ul id="paluwagan-desc" class="list-disc ml-6 text-gray-700"></ul>
            </div>

            <div class="bg-[#FFF1F0] p-3 rounded-lg mb-4 text-sm text-gray-800 mt-4">
                <p class="font-semibold mb-1">Paluwagan Details</p>
                <p>Total Package: <span id="paluwagan-total"></span></p>
                <p>Monthly Payment: <span id="paluwagan-monthly"></span></p>
                <p>Duration: <span id="paluwagan-duration"></span></p>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button id="join-paluwagan"
                        class="bg-[#FF1493] hover:bg-[#FF69B4] text-white px-5 py-2 rounded-lg font-semibold transition">
                        Join Paluwagan
                </button>
            </div>
        </div>

        <!-- STEP 2 -->
        <div id="paluwagan-step2" class="hidden">
            <h2 class="text-2xl font-bold mb-4">Paluwagan Enrollment</h2>
            
            <label class="font-semibold text-gray-700 block mb-2">Select Start Month</label>
            <select id="start-month" class="border rounded-md w-full p-2 mb-4">
                @foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $month)
                    <option>{{ $month }}</option>
                @endforeach
            </select>

            <img id="paluwagan-image2" src="" class="rounded-lg w-full h-60 object-cover mb-5">

            <div class="bg-[#FFF1F0] p-3 rounded-lg mb-4 text-sm text-gray-800">
                <p class="font-semibold mb-1">Important Reminders</p>
                <ul class="list-disc list-inside text-gray-700 space-y-1">
                    <li>Payments are due on the 15th of each month.</li>
                    <li>5-day extension for late payment, then penalty per day.</li>
                    <li>No cancellation or refund once payment starts.</li>
                    <li>All payments are non-refundable.</li>
                </ul>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button id="back-paluwagan"
                        class="border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 transition">Back</button>
                <button id="confirmEnrollmentBtn" class="bg-pink-600 hover:bg-pink-700 text-white font-semibold px-4 py-2 rounded">
                    Confirm Enrollment
                </button>
            </div>
        </div>

    </div>
</div>

<script>
let selectedPackageID = null;

// Function to populate modal dynamically with clicked package info
function openPaluwaganModal(packageData) {
    selectedPackageID = packageData.id;

    const modal = document.getElementById('paluwagan-modal');
    modal.dataset.packageId = packageData.id;

    document.getElementById('paluwagan-name').textContent = packageData.name;
    document.getElementById('paluwagan-image').src = packageData.imageURL;

    const descEl = document.getElementById('paluwagan-desc');
    descEl.innerHTML = '';
    (packageData.descriptionList || []).forEach(item => {
        const li = document.createElement('li');
        li.textContent = item;
        descEl.appendChild(li);
    });

    if (packageData.servings && packageData.servings.length > 0) {
    const serving = packageData.servings[0];
    const duration = parseInt(serving.size) || packageData.total_months || 1;
    const price = parseFloat(serving.price) || packageData.package_amount || 0;

    document.getElementById('paluwagan-total').textContent = price.toFixed(2);
    document.getElementById('paluwagan-monthly').textContent = (price / duration).toFixed(2);
    document.getElementById('paluwagan-duration').textContent = duration + ' months';
}


    // Step 1 visible initially
    modal.querySelector('#paluwagan-step1').classList.remove('hidden');
    modal.querySelector('#paluwagan-step2').classList.add('hidden');
    modal.classList.remove('hidden');
}

// Open modal when clicking a Paluwagan package
document.querySelectorAll('.paluwagan-card').forEach(card => {
    card.addEventListener('click', () => {
        const packageData = {
            id: card.dataset.id,
            name: card.dataset.name,
            imageURL: card.dataset.image,
            descriptionList: JSON.parse(card.dataset.description || '[]'),
            package_amount: parseFloat(card.dataset.total) || 0,
            total_months: parseInt(card.dataset.duration) || 1,
            servings: JSON.parse(card.dataset.servings || '[]')
        };
        openPaluwaganModal(packageData);
    });
});

// Join button: move to step 2
document.getElementById('join-paluwagan').addEventListener('click', () => {
    document.getElementById('paluwagan-step1').classList.add('hidden');
    document.getElementById('paluwagan-step2').classList.remove('hidden');
    document.getElementById('paluwagan-image2').src = document.getElementById('paluwagan-image').src;
});

// Back button: return to step 1
document.getElementById('back-paluwagan').addEventListener('click', () => {
    document.getElementById('paluwagan-step2').classList.add('hidden');
    document.getElementById('paluwagan-step1').classList.remove('hidden');
});

// Confirm enrollment (AJAX request)
document.getElementById('confirmEnrollmentBtn').addEventListener('click', async () => {
    const startMonth = document.getElementById('start-month').value;

    if (!selectedPackageID) {
        alert('Package not selected!');
        return;
    }

    try {
        const response = await fetch('/paluwagan/join', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                packageID: selectedPackageID,
                startMonth: startMonth
            })
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message || 'Successfully joined Paluwagan!');
            document.getElementById('paluwagan-modal').classList.add('hidden');
            window.location.href = '/paluwagan';
        } else {
            alert(data.error || 'Failed to join paluwagan');
        }
    } catch (err) {
        console.error(err);
        alert('Network error while joining Paluwagan.');
    }
});

// Close modal
document.getElementById('close-modal-paluwagan').addEventListener('click', () => {
    document.getElementById('paluwagan-modal').classList.add('hidden');
});


</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".paluwagan-card").forEach(card => {
        card.addEventListener("click", () => {
            const packageData = {
                id: card.dataset.id,
                name: card.dataset.name,
                imageURL: card.dataset.image,
                descriptionList: JSON.parse(card.dataset.description),
                servings: JSON.parse(card.dataset.servings)
            };

            openPaluwaganModal(packageData);
        });
    });
});

</script>
