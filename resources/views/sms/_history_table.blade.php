<div id="historyTableContainer">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Recipient</th>
                    <th>Contact</th>
                    <th>Message Preview</th>
                    <th>Status</th>
                    <th>Sent At</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($smsLogs as $log)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold text-dark">{{ $log->beneficiary->full_name ?? 'N/A' }}</div>
                            <div class="x-small text-muted">{{ $log->beneficiary->barangay->name ?? '—' }}</div>
                        </td>
                        <td>{{ $log->beneficiary->contact_number ?? '—' }}</td>
                        <td>
                            <div class="text-truncate" style="max-width: 250px;">
                                {{ $log->message }}
                            </div>
                        </td>
                        <td>
                            @if($log->status === 'sent')
                                <span class="badge badge-soft-success">Sent</span>
                            @elseif($log->status === 'failed')
                                <span class="badge badge-soft-danger">Failed</span>
                            @else
                                <span class="badge badge-soft-warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-dark">{{ $log->sent_at?->format('M d, Y') }}</div>
                            <div class="x-small text-muted">{{ $log->sent_at?->format('h:i A') }}</div>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-icon-only rounded-circle border hover-bg-light view-log-btn" 
                                data-id="{{ $log->id }}"
                                data-name="{{ $log->beneficiary->full_name }}"
                                data-message="{{ $log->message }}"
                                data-status="{{ $log->status }}"
                                data-sent="{{ $log->sent_at?->format('M d, Y h:i A') }}">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-chat-left-dots fs-1 d-block mb-3 opacity-25"></i>
                            No broadcast history found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 ajax-pagination">
        {{ $smsLogs->links() }}
    </div>
</div>
