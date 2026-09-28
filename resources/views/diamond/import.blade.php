@extends('layouts.app')

@section('css')
<style>
    .import-shell {
        width: 100%;
        max-width: none;
        padding: 0 16px 20px;
    }
    .import-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px 18px 14px;
    }
    .import-steps {
        display: flex;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0 0 16px;
    }
    .import-steps li {
        flex: 1;
        text-align: center;
        font-size: 13px;
        padding: 8px 10px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
    }
    .import-steps li.is-current {
        background: #0d6efd;
        color: #fff;
        font-weight: 600;
    }
    .import-steps li.is-done {
        background: #e8f6ee;
        color: #146c43;
        font-weight: 600;
    }
    .import-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 4px 0 10px;
    }
    .import-toolbar-main {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }
    #importButton {
        min-width: 180px;
        white-space: nowrap;
    }
    #importButton .spinner-border {
        margin-right: 6px;
        vertical-align: -2px;
    }
    #importPreview {
        border: 1px solid #dbe3ea;
        border-radius: 8px;
        background: #fff;
    }
    #importScroll {
        max-height: calc(100vh - 250px);
        min-height: 460px;
        overflow: auto;
        border-radius: 8px;
    }
    #importPreviewTable {
        border-collapse: collapse;
        width: max-content;
        min-width: 100%;
        font-size: 13px;
        margin-bottom: 0;
    }
    #importPreviewTable th,
    #importPreviewTable td {
        border: 1px solid #e6ebf0;
        padding: 0 10px;
        height: 36px;
        line-height: 34px;
        box-sizing: border-box;
        max-width: 240px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
        background: #fff;
    }
    #importPreviewTable thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #f3f7f4;
        color: #1f2937;
        font-size: 12px;
        font-weight: 650;
        letter-spacing: 0.01em;
        box-shadow: inset 0 -1px 0 #d5e0d8;
    }
    #importPreviewTable .row-no {
        position: sticky;
        left: 0;
        z-index: 2;
        min-width: 58px;
        width: 58px;
        text-align: right;
        color: #64748b;
        font-variant-numeric: tabular-nums;
        background: #fff;
        box-shadow: 1px 0 0 #e6ebf0;
    }
    #importPreviewTable thead .row-no {
        z-index: 5;
        background: #f3f7f4;
        color: #1f2937;
    }
    #importPreviewTable tbody tr.is-alt td {
        background: #fbfcfd;
    }
    #importPreviewTable tbody tr:hover td {
        background: #f3f7ff;
    }
    #importPreviewTable tbody tr.is-error td {
        background: #fff1f1;
        box-shadow: inset 0 1px 0 #dc3545, inset 0 -1px 0 #dc3545;
    }
    #importPreviewTable tbody tr.is-error td:first-child {
        box-shadow: inset 3px 0 0 #dc3545, inset 0 1px 0 #dc3545, inset 0 -1px 0 #dc3545;
    }
    #importPreviewTable tbody tr.is-error td:last-child {
        box-shadow: inset -3px 0 0 #dc3545, inset 0 1px 0 #dc3545, inset 0 -1px 0 #dc3545;
    }
    #importPreviewTable tbody tr.is-error .row-no {
        background: #fff1f1;
        color: #b02a37;
        font-weight: 650;
    }
    #importPreviewTable tbody tr.is-error:hover td,
    #importPreviewTable tbody tr.is-error:hover .row-no {
        background: #ffe4e6;
    }
    #importPreviewTable tbody tr.is-saving td {
        background: #fff8e8;
    }
    #importPreviewTable tbody tr.is-saved td {
        background: #eefaf3;
    }
    #importPreviewTable tr.spacer td {
        height: auto;
        padding: 0;
        border: 0;
        background: transparent !important;
        box-shadow: none;
    }
    #importSummary .btn.is-active {
        box-shadow: inset 0 0 0 2px currentColor;
    }
    .import-lock {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(15, 23, 42, 0.48);
    }
    .import-lock.d-none {
        display: none !important;
    }
    .import-lock-card {
        width: min(440px, 100%);
        background: #fff;
        border-radius: 14px;
        padding: 32px 28px 26px;
        text-align: center;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
    }
    .import-lock-card .spinner-border {
        width: 3.25rem;
        height: 3.25rem;
        border-width: 0.28em;
        margin-bottom: 18px;
    }
    .import-lock-card .progress {
        height: 8px;
        background: #e9ecef;
    }
    body.import-busy {
        overflow: hidden;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
<div class="import-shell">
    <div class="import-panel">
        <h1 class="h4 mb-1">Import diamonds</h1>
        <p class="text-muted small mb-3">Choose an Excel file. Each row is checked in the browser, rows with errors are listed first, and valid rows are saved in batches. A successful import replaces the current catalog.</p>
        <ol class="import-steps" id="importSteps">
            <li id="stepChoose" class="is-current">1. Choose file</li>
            <li id="stepReview">2. Review rows</li>
            <li id="stepSave">3. Import</li>
        </ol>
        <div id="importAlert"></div>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="mb-3">
            <label for="import_file" class="form-label">Excel file</label>
            <input type="file" class="form-control" id="import_file" accept=".xlsx,.xls,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
        </div>

        <div class="import-toolbar">
            <div id="importSummary" class="import-toolbar-main d-none">
                <button type="button" class="btn btn-sm btn-outline-secondary is-active" data-filter="all">All <span id="countAll">0</span></button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-filter="errors">Errors <span id="countErrors">0</span></button>
                <button type="button" class="btn btn-sm btn-outline-success" data-filter="ready">Valid <span id="countReady">0</span></button>
                <span id="importBanner" class="small text-muted"></span>
            </div>
            <button type="button" class="btn btn-primary ms-auto" id="importButton" disabled>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="loadingSpinner"></span>
                <span id="importButtonLabel">Import</span>
            </button>
        </div>

        <div id="importPreview" class="d-none">
            <div id="importScroll">
                <table class="table table-sm mb-0" id="importPreviewTable">
                    <thead id="importHead"></thead>
                    <tbody id="importBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="importLock" class="import-lock d-none" role="alertdialog" aria-modal="true" aria-labelledby="importStatus" aria-busy="true">
    <div class="import-lock-card">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading</span>
        </div>
        <div id="importStatus" class="h5 mb-1">Preparing…</div>
        <div id="importCount" class="text-muted mb-1"></div>
        <p class="small text-muted mb-3">Please wait. The page is locked until this step finishes.</p>
        <div class="progress">
            <div id="importBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
window.diamondImport = @json($importConfig);
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="{{ url('public/js/diamond-import.js') }}?v={{ filemtime(public_path('js/diamond-import.js')) }}"></script>
@endsection
