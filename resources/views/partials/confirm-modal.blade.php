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

<script>
let pendingConfirmForm = null;

function confirmAction(title, message, actionUrl, method) {
    method = method || 'POST';
    pendingConfirmForm = null;

    document.getElementById('confirmModalLabel').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmForm').setAttribute('action', actionUrl);
    document.getElementById('confirmMethod').value = method;

    var modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    var confirmModalEl = document.getElementById('confirmModal');
    var confirmModal = new bootstrap.Modal(confirmModalEl);
    var confirmForm = document.getElementById('confirmForm');
    var confirmBtn = document.getElementById('confirmBtn');

    // Intercept forms marked with data-confirm-* attributes.
    document.querySelectorAll('form[data-confirm-message]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.skipConfirm === '1') {
                return;
            }

            e.preventDefault();
            pendingConfirmForm = form;

            document.getElementById('confirmModalLabel').textContent = form.dataset.confirmTitle || 'Confirm Action';
            document.getElementById('confirmMessage').textContent = form.dataset.confirmMessage;

            confirmModal.show();
        });
    });

    confirmBtn.addEventListener('click', function (e) {
        if (!pendingConfirmForm) {
            return;
        }

        e.preventDefault();
        var form = pendingConfirmForm;
        pendingConfirmForm = null;
        form.dataset.skipConfirm = '1';
        confirmModal.hide();
        form.submit();
    });

    confirmModalEl.addEventListener('hidden.bs.modal', function () {
        pendingConfirmForm = null;
    });
});
</script>
