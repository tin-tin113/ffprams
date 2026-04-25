{{-- Reusable Alert Modal for Notifications --}}
<div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true" style="z-index: 1090;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4 text-center">
                <div id="alertIconContainer" class="d-inline-flex p-3 rounded-circle mb-3">
                    <i id="alertIcon" class="bi fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2" id="alertTitle"></h5>
                <p class="text-muted small mb-4" id="alertMessage"></p>
                <button type="button" class="btn btn-primary w-100 rounded-pill fw-semibold py-2 shadow-sm" data-bs-dismiss="modal">
                    Got it
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #alertModal .modal-content {
        background-color: #ffffff;
    }
    
    #alertModal .bg-success-subtle { background-color: #dcfce7 !important; color: #166534 !important; }
    #alertModal .bg-danger-subtle { background-color: #fee2e2 !important; color: #991b1b !important; }
    #alertModal .bg-warning-subtle { background-color: #fef9c3 !important; color: #854d0e !important; }
    #alertModal .bg-info-subtle { background-color: #e0f2fe !important; color: #0369a1 !important; }
    #alertModal .bg-primary-subtle { background-color: #dbeafe !important; color: #1e40af !important; }
</style>

<script>
/**
 * Shows a premium styled alert modal
 * @param {string} title 
 * @param {string} message 
 * @param {string} type - 'success', 'error', 'warning', 'info'
 */
function showAlert(title, message, type = 'info') {
    const modalEl = document.getElementById('alertModal');
    const titleEl = document.getElementById('alertTitle');
    const messageEl = document.getElementById('alertMessage');
    const iconEl = document.getElementById('alertIcon');
    const iconContainer = document.getElementById('alertIconContainer');

    titleEl.textContent = title;
    messageEl.textContent = message;

    // Reset classes
    iconContainer.className = 'd-inline-flex p-3 rounded-circle mb-3';
    
    if (type === 'success') {
        iconContainer.classList.add('bg-success-subtle');
        iconEl.className = 'bi bi-check-circle-fill';
    } else if (type === 'error') {
        iconContainer.classList.add('bg-danger-subtle');
        iconEl.className = 'bi bi-exclamation-octagon-fill';
    } else if (type === 'warning') {
        iconContainer.classList.add('bg-warning-subtle');
        iconEl.className = 'bi bi-exclamation-triangle-fill';
    } else {
        iconContainer.classList.add('bg-info-subtle');
        iconEl.className = 'bi bi-info-circle-fill';
    }

    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

/**
 * Global override for native window.alert
 * This ensures all legacy alert() calls automatically use our proper modal.
 */
(function() {
    const nativeAlert = window.alert;
    window.alert = function(message) {
        if (!message) return;
        
        let type = 'info';
        let title = 'System Notification';
        const msg = String(message).toLowerCase();
        
        // Intelligent type detection
        if (msg.includes('error') || msg.includes('failed') || msg.includes('unable') || msg.includes('invalid') || msg.includes('denied')) {
            type = 'error';
            title = 'Action Failed';
        } else if (msg.includes('success') || msg.includes('successfully') || msg.includes('saved') || msg.includes('updated')) {
            type = 'success';
            title = 'Success';
        } else if (msg.includes('warning') || msg.includes('attention') || msg.includes('required') || msg.includes('please')) {
            type = 'warning';
            title = 'Attention Required';
        }

        // Special case for "already exists" mentioned by user
        if (msg.includes('already exists')) {
            type = 'error';
            title = 'Duplicate Record';
        }

        showAlert(title, message, type);
        
        // Log to console so developers still see it
        console.log('Native alert intercepted:', message);
    };
})();
</script>
