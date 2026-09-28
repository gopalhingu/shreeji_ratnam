@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h4 mb-1">Setting</h1>
            <p class="text-muted small mb-4">Run cache and migration actions from this page. These actions do not delete diamond records.</p>
            <div id="settingAlert"></div>
            @foreach ($actions as $key => $action)
                <div class="card mb-3">
                    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="me-3">
                            <h2 class="h6 mb-1">{{ $action['title'] }}</h2>
                            <p class="text-muted small mb-0">{{ $action['description'] }}</p>
                        </div>
                        <button type="button" class="btn btn-primary setting-run" data-action="{{ $key }}" data-title="{{ $action['title'] }}">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Run
                        </button>
                    </div>
                </div>
            @endforeach
            <pre id="settingOutput" class="d-none bg-light border rounded p-3 small mb-0"></pre>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    var buttons = document.querySelectorAll('.setting-run');
    var alertBox = document.getElementById('settingAlert');
    var outputBox = document.getElementById('settingOutput');
    var runUrl = @json(route('setting.run'));
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var busy = false;

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showAlert(type, message) {
        alertBox.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">'
            + escapeHtml(message)
            + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }

    function setBusy(on, button) {
        busy = on;
        var index;
        for (index = 0; index < buttons.length; index++) {
            buttons[index].disabled = on;
        }
        if (!button) {
            return;
        }
        var spinner = button.querySelector('.spinner-border');
        if (spinner) {
            spinner.classList.toggle('d-none', !on);
        }
    }

    function runAction(button) {
        setBusy(true, button);
        alertBox.innerHTML = '';
        outputBox.classList.add('d-none');
        fetch(runUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: button.getAttribute('data-action') })
        }).then(function (response) {
            return response.text().then(function (text) {
                var body = {};
                try {
                    body = text ? JSON.parse(text) : {};
                } catch (error) {
                    throw new Error('The server could not complete that action.');
                }
                if (!response.ok || !body.ok) {
                    var failed = new Error(body.message || 'The action could not be completed.');
                    failed.output = body.output || '';
                    throw failed;
                }
                return body;
            });
        }).then(function (body) {
            showAlert('success', body.message || 'Finished.');
            outputBox.textContent = body.output || 'Finished.';
            outputBox.classList.remove('d-none');
        }).catch(function (error) {
            showAlert('danger', error.message || 'The action could not be completed.');
            if (error.output) {
                outputBox.textContent = error.output;
                outputBox.classList.remove('d-none');
            }
        }).then(function () {
            setBusy(false, button);
        });
    }

    var index;
    for (index = 0; index < buttons.length; index++) {
        buttons[index].addEventListener('click', function () {
            var button = this;
            if (busy) {
                return;
            }
            var title = button.getAttribute('data-title') || 'this action';
            if (typeof Swal === 'undefined') {
                runAction(button);
                return;
            }
            Swal.fire({
                title: 'Run ' + title + '?',
                text: 'This runs on the server now.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Run',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                runAction(button);
            });
        });
    }
})();
</script>
@endsection
