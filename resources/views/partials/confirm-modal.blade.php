{{-- Reusable Confirmation Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="confirmForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="confirmMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage">Are you sure?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="confirmBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #confirmModal.confirm-modal-top-layer {
        z-index: 1080;
    }

    .modal-backdrop.confirm-modal-top-backdrop {
        z-index: 1075;
    }
</style>

<script>
let pendingConfirmForm = null;
let pendingConfirmCallback = null;

function ensureModalInBody(modalEl) {
    if (modalEl && modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
    }
}

function getLatestBackdrop() {
    var backdrops = document.querySelectorAll('.modal-backdrop');
    return backdrops.length ? backdrops[backdrops.length - 1] : null;
}

function openConfirmModal(title, message) {
    document.getElementById('confirmModalLabel').textContent = title || 'Confirm Action';
    document.getElementById('confirmMessage').textContent = message || 'Are you sure?';

    var confirmModalEl = document.getElementById('confirmModal');
    ensureModalInBody(confirmModalEl);

    var existingModal = bootstrap.Modal.getInstance(confirmModalEl);
    if (existingModal) {
        existingModal.dispose();
    }

    var modal = new bootstrap.Modal(confirmModalEl);
    modal.show();
}

function confirmAction(title, message, actionUrl, method) {
    method = method || 'POST';
    pendingConfirmForm = null;
    pendingConfirmCallback = null;

    document.getElementById('confirmForm').setAttribute('action', actionUrl);
    document.getElementById('confirmMethod').value = method;

    openConfirmModal(title, message);
}

function confirmThenRun(title, message, onConfirm) {
    pendingConfirmForm = null;
    pendingConfirmCallback = typeof onConfirm === 'function' ? onConfirm : null;

    // Keep a harmless default action for callback mode.
    document.getElementById('confirmForm').setAttribute('action', '#');
    document.getElementById('confirmMethod').value = 'POST';

    openConfirmModal(title, message);
}

document.addEventListener('DOMContentLoaded', function () {
    var confirmModalEl = document.getElementById('confirmModal');
    var confirmForm = document.getElementById('confirmForm');

    ensureModalInBody(confirmModalEl);

    function clearConfirmTopLayer() {
        confirmModalEl.classList.remove('confirm-modal-top-layer');
        document.querySelectorAll('.modal-backdrop.confirm-modal-top-backdrop').forEach(function (backdrop) {
            backdrop.classList.remove('confirm-modal-top-backdrop');
        });
    }

    confirmModalEl.addEventListener('shown.bs.modal', function () {
        confirmModalEl.classList.add('confirm-modal-top-layer');
        var latestBackdrop = getLatestBackdrop();
        if (latestBackdrop) {
            latestBackdrop.classList.add('confirm-modal-top-backdrop');
        }
    });

    // Intercept forms marked with data-confirm-* attributes.
    document.querySelectorAll('form[data-confirm-message]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.skipConfirm === '1') {
                return;
            }

            e.preventDefault();
            pendingConfirmForm = form;
            pendingConfirmCallback = null;

            openConfirmModal(
                form.dataset.confirmTitle || 'Confirm Action',
                form.dataset.confirmMessage
            );
        });
    });

    confirmForm.addEventListener('submit', function (e) {
        if (pendingConfirmForm) {
            e.preventDefault();
            var form = pendingConfirmForm;
            pendingConfirmForm = null;
            pendingConfirmCallback = null;
            form.dataset.skipConfirm = '1';
            var activeModal = bootstrap.Modal.getInstance(confirmModalEl);
            if (activeModal) {
                activeModal.hide();
            }
            form.submit();
            return;
        }

        e.preventDefault();
        if (pendingConfirmCallback) {
            var callback = pendingConfirmCallback;
            pendingConfirmForm = null;
            pendingConfirmCallback = null;
            var activeCallbackModal = bootstrap.Modal.getInstance(confirmModalEl);
            if (activeCallbackModal) {
                activeCallbackModal.hide();
            }
            callback();
            return;
        }

        // confirmAction mode: submit this modal form normally.
        confirmForm.submit();
    });

    confirmModalEl.addEventListener('hidden.bs.modal', function () {
        clearConfirmTopLayer();
        pendingConfirmForm = null;
        pendingConfirmCallback = null;
    });
});
</script>
