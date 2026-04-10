@extends('layouts.app')

@section('title', 'Register New Beneficiary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('beneficiaries.index') }}">Beneficiaries</a></li>
    <li class="breadcrumb-item active">Register New</li>
@endsection

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0">Register New Beneficiary</h1>
    </div>

    <div id="beneficiaryAjaxNotice" class="alert d-none" role="alert"></div>

    <form id="beneficiaryCreateForm" action="{{ route('beneficiaries.store') }}" method="POST">
        @csrf
        @include('beneficiaries.partials.form')
    </form>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
        <div id="beneficiaryToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="beneficiaryToastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('beneficiaryCreateForm');
    if (!form) return;

    var submitButton = form.querySelector('button[type="submit"]');
    var ajaxNotice = document.getElementById('beneficiaryAjaxNotice');
    var toastEl = document.getElementById('beneficiaryToast');
    var toastMessageEl = document.getElementById('beneficiaryToastMessage');
    var toast = toastEl ? bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 4500 }) : null;

    function showToast(type, message) {
        if (!toast || !toastEl || !toastMessageEl) {
            return;
        }

        var bgClass = 'text-bg-primary';
        if (type === 'success') bgClass = 'text-bg-success';
        if (type === 'error') bgClass = 'text-bg-danger';
        if (type === 'warning') bgClass = 'text-bg-warning';

        toastEl.className = 'toast align-items-center border-0 ' + bgClass;
        toastMessageEl.textContent = message;
        toast.show();
    }

    function clearNotice() {
        if (!ajaxNotice) return;
        ajaxNotice.className = 'alert d-none';
        ajaxNotice.textContent = '';
    }

    function showNotice(type, message, linkUrl, linkText) {
        if (!ajaxNotice) return;

        var cssClass = 'alert-info';
        if (type === 'success') cssClass = 'alert-success';
        if (type === 'error') cssClass = 'alert-danger';
        if (type === 'warning') cssClass = 'alert-warning';

        ajaxNotice.className = 'alert ' + cssClass;
        ajaxNotice.textContent = message;

        if (linkUrl && linkText) {
            var spacer = document.createTextNode(' ');
            var link = document.createElement('a');
            link.href = linkUrl;
            link.className = 'alert-link';
            link.textContent = linkText;
            ajaxNotice.appendChild(spacer);
            ajaxNotice.appendChild(link);
        }
    }

    function clearFieldErrors() {
        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });

        form.querySelectorAll('.invalid-feedback.js-invalid-feedback').forEach(function (el) {
            el.remove();
        });
    }

    function setFieldError(fieldName, message) {
        var selector = '[name="' + fieldName.replace(/"/g, '\\"') + '"]';
        var elements = form.querySelectorAll(selector);

        if (!elements.length && fieldName.indexOf('.') !== -1) {
            var bracketName = fieldName.replace(/\.([^\.]+)/g, '[$1]');
            selector = '[name="' + bracketName.replace(/"/g, '\\"') + '"]';
            elements = form.querySelectorAll(selector);
        }

        if (!elements.length) {
            return;
        }

        var target = Array.from(elements).find(function (el) { return el.type !== 'hidden'; }) || elements[0];
        target.classList.add('is-invalid');

        var feedbackParent = target.closest('.col-md-2, .col-md-3, .col-md-4, .col-md-6, .col-md-8, .col-12') || target.parentElement;
        if (!feedbackParent) {
            return;
        }

        var existingFeedback = feedbackParent.querySelector('.invalid-feedback.js-invalid-feedback');
        if (existingFeedback) {
            existingFeedback.textContent = message;
            return;
        }

        var feedback = document.createElement('div');
        feedback.className = 'invalid-feedback js-invalid-feedback';
        feedback.textContent = message;
        feedbackParent.appendChild(feedback);
    }

    function setSubmittingState(isSubmitting) {
        if (!submitButton) return;

        if (isSubmitting) {
            submitButton.disabled = true;
            submitButton.dataset.originalHtml = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Registering...';
            return;
        }

        submitButton.disabled = false;
        if (submitButton.dataset.originalHtml) {
            submitButton.innerHTML = submitButton.dataset.originalHtml;
        }
    }

    function resetCreateFormState() {
        form.reset();

        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var dd = String(today.getDate()).padStart(2, '0');
        var registeredAt = document.getElementById('registered_at');
        if (registeredAt && !registeredAt.value) {
            registeredAt.value = yyyy + '-' + mm + '-' + dd;
        }

        ['agency_id', 'classification', 'association_member', 'has_fishing_vessel'].forEach(function (id) {
            var element = document.getElementById(id);
            if (element) {
                element.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        clearNotice();
        clearFieldErrors();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        setSubmittingState(true);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form)
        })
        .then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, status: response.status, data: data };
            });
        })
        .then(function (result) {
            if (result.ok) {
                showToast('success', result.data.message || 'Beneficiary registered successfully.');
                showNotice('success', 'Beneficiary registered successfully. You can register another one now.');
                resetCreateFormState();
                return;
            }

            if (result.status === 422 && result.data.errors) {
                Object.keys(result.data.errors).forEach(function (field) {
                    var messages = result.data.errors[field] || [];
                    if (messages.length > 0) {
                        setFieldError(field, messages[0]);
                    }
                });

                showToast('error', 'Please fix the highlighted fields and try again.');
                showNotice('error', 'Some required fields are missing or invalid. Please review the highlighted fields.');
                return;
            }

            if (result.status === 409 && result.data.duplicate) {
                showToast('warning', result.data.message || 'Possible duplicate beneficiary found.');
                showNotice('warning', result.data.message || 'Possible duplicate beneficiary found.', result.data.redirect_url, 'View existing record');
                return;
            }

            showToast('error', result.data.message || 'Registration failed. Please try again.');
            showNotice('error', result.data.message || 'Registration failed. Please try again.');
        })
        .catch(function () {
            showToast('error', 'Network error. Please check your connection and try again.');
            showNotice('error', 'Network error. Please check your connection and try again.');
        })
        .finally(function () {
            setSubmittingState(false);
        });
    });
});
</script>
@endpush
