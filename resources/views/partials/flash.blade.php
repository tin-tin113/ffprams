@php
    $flashTypes = [
        'success' => ['class' => 'alert-success', 'icon' => 'bi-check-circle-fill'],
        'error'   => ['class' => 'alert-danger',  'icon' => 'bi-x-circle-fill'],
        'warning' => ['class' => 'alert-warning', 'icon' => 'bi-exclamation-triangle-fill'],
        'info'    => ['class' => 'alert-info',    'icon' => 'bi-info-circle-fill'],
    ];
@endphp

@foreach($flashTypes as $key => $config)
    @if(session($key))
        <div class="alert {{ $config['class'] }} alert-dismissible fade show d-flex align-items-center flash-auto-dismiss" role="alert">
            <i class="bi {{ $config['icon'] }} me-2"></i>
            <div>{{ session($key) }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.flash-auto-dismiss').forEach(function (alert) {
        setTimeout(function () {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 4000);
    });
});
</script>
