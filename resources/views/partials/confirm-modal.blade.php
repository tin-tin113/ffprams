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
function confirmAction(title, message, actionUrl, method) {
    method = method || 'POST';
    document.getElementById('confirmModalLabel').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmForm').setAttribute('action', actionUrl);
    document.getElementById('confirmMethod').value = method;

    var modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
}
</script>
