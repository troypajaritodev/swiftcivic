function toggleQR(method) {
    const qrDiv = document.getElementById('gcash_qr');
    if (qrDiv && method === 'GCash') {
        qrDiv.classList.remove('hidden');
    } else if (qrDiv) {
        qrDiv.classList.add('hidden');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('payment_method');
    if (select) {
        // Show QR if GCash is already selected (e.g., form resubmission)
        toggleQR(select.value);
        
        // Add change event listener
        select.addEventListener('change', function() {
            toggleQR(this.value);
        });
    }
});

