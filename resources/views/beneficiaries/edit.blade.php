@extends('layouts.app')

@section('title', 'Edit Beneficiary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('beneficiaries.index') }}">Beneficiaries</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0">Edit Beneficiary &mdash; {{ $beneficiary->full_name }}</h1>
    </div>

    <div id="beneficiaryEditAjaxNotice" class="alert d-none" role="alert"></div>

    <form id="beneficiaryEditForm" action="{{ route('beneficiaries.update', $beneficiary) }}" method="POST">
        @csrf
        @method('PUT')
        @include('beneficiaries.partials.form')
    </form>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
        <div id="beneficiaryEditToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="beneficiaryEditToastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/beneficiary-dynamic-agencies.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('beneficiaryEditForm');
    if (!form) return;

    var submitButton = form.querySelector('button[type="submit"]');
    var ajaxNotice = document.getElementById('beneficiaryEditAjaxNotice');
    var toastEl = document.getElementById('beneficiaryEditToast');
    var toastMessageEl = document.getElementById('beneficiaryEditToastMessage');
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

        form.querySelectorAll('.text-danger.js-inline-error').forEach(function (el) {
            el.remove();
        });
    }

    function isVisibleElement(element) {
        return !!(element.offsetWidth || element.offsetHeight || element.getClientRects().length);
    }

    function setFieldError(fieldName, message) {
        var selector = '[name="' + fieldName.replace(/"/g, '\\"') + '"]';
        var elements = form.querySelectorAll(selector);

        if (!elements.length && fieldName.indexOf('.') !== -1) {
            var bracketName = fieldName.replace(/\.([^\.]+)/g, '[$1]');
            selector = '[name="' + bracketName.replace(/"/g, '\\"') + '"]';
            elements = form.querySelectorAll(selector);
        }

        if (!elements.length && (fieldName === 'agencies' || fieldName.indexOf('agencies.') === 0)) {
            elements = form.querySelectorAll('input[name="agencies[]"]');
        }

        if (!elements.length) {
            return;
        }

        var target = Array.from(elements).find(function (el) { return el.type !== 'hidden' && isVisibleElement(el); })
            || Array.from(elements).find(function (el) { return el.type !== 'hidden'; })
            || elements[0];
        var isChoiceGroup = target.type === 'radio'
            || (target.type === 'checkbox' && elements.length > 1)
            || fieldName === 'agencies'
            || fieldName.indexOf('agencies.') === 0;

        elements.forEach(function (el) {
            if (el.type !== 'hidden') {
                el.classList.add('is-invalid');
            }
        });

        var feedbackParent = target.closest('.col-md-2, .col-md-3, .col-md-4, .col-md-6, .col-md-8, .col-12') || target.parentElement;
        if (!feedbackParent) {
            return;
        }

        if (isChoiceGroup) {
            var existingInline = feedbackParent.querySelector('.text-danger.small.js-inline-error');
            if (existingInline) {
                existingInline.textContent = message;
                return;
            }

            var inlineError = document.createElement('div');
            inlineError.className = 'text-danger small mt-1 js-inline-error';
            inlineError.textContent = message;
            feedbackParent.appendChild(inlineError);
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
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';
            return;
        }

        submitButton.disabled = false;
        if (submitButton.dataset.originalHtml) {
            submitButton.innerHTML = submitButton.dataset.originalHtml;
        }
    }

    function parseResponse(response) {
        return response.text().then(function (raw) {
            var data = {};
            if (raw) {
                try {
                    data = JSON.parse(raw);
                } catch (e) {
                    data = {};
                }
            }

            return {
                ok: response.ok,
                status: response.status,
                data: data
            };
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
        .then(parseResponse)
        .then(function (result) {
            if (result.ok) {
                showToast('success', result.data.message || 'Beneficiary updated successfully.');
                showNotice('success', result.data.message || 'Beneficiary profile updated successfully.', result.data.redirect_url, 'View profile');
                return;
            }

            if (result.status === 422 && result.data.errors) {
                var firstErrorMessage = null;

                Object.keys(result.data.errors).forEach(function (field) {
                    var messages = result.data.errors[field] || [];
                    if (messages.length > 0) {
                        if (!firstErrorMessage) {
                            firstErrorMessage = messages[0];
                        }

                        setFieldError(field, messages[0]);
                    }
                });

                showToast('error', 'Please fix the highlighted fields and try again.');
                showNotice('error', firstErrorMessage || 'Some required fields are missing or invalid. Please review the highlighted fields.');
                return;
            }

            if (result.status === 409 && result.data.duplicate) {
                showToast('warning', result.data.message || 'Possible duplicate beneficiary found.');
                showNotice('warning', result.data.message || 'Possible duplicate beneficiary found.', result.data.redirect_url, 'View existing record');
                return;
            }

            showToast('error', result.data.message || 'Update failed. Please try again.');
            showNotice('error', result.data.message || 'Update failed. Please try again.');
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
