(function () {
    var config = window.diamondImport || {};
    var batchUrl = config.batchUrl || '';
    var batchSize = Math.min(200, parseInt(config.batchSize, 10) || 200);
    var columnRules = config.columns || {};
    var input = document.getElementById('import_file');
    var button = document.getElementById('importButton');
    var buttonLabel = document.getElementById('importButtonLabel');
    var spinner = document.getElementById('loadingSpinner');
    var preview = document.getElementById('importPreview');
    var tableCard = document.getElementById('importTableCard');
    var scrollBox = document.getElementById('importScroll');
    var head = document.getElementById('importHead');
    var body = document.getElementById('importBody');
    var summary = document.getElementById('importSummary');
    var banner = document.getElementById('importBanner');
    var lock = document.getElementById('importLock');
    var statusText = document.getElementById('importStatus');
    var countText = document.getElementById('importCount');
    var bar = document.getElementById('importBar');
    var alertBox = document.getElementById('importAlert');
    var countAll = document.getElementById('countAll');
    var countErrors = document.getElementById('countErrors');
    var countReady = document.getElementById('countReady');
    var stepChoose = document.getElementById('stepChoose');
    var stepReview = document.getElementById('stepReview');
    var stepSave = document.getElementById('stepSave');

    if (!input || !button || !batchUrl) {
        return;
    }

    var ticket = 0;
    var busy = false;
    var finished = false;
    var reviewReady = false;
    var lockScroll = false;
    var filter = 'all';
    var labels = [];
    var keys = [];
    var errors = [];
    var ready = [];
    var rowHeight = 36;
    var renderScheduled = false;
    var MAX_COLUMNS = 120;
    var MAX_ROWS = 100000;

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

    function clearAlert() {
        alertBox.innerHTML = '';
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function setStep(step) {
        stepChoose.className = step === 'choose' ? 'is-current' : 'is-done';
        stepReview.className = step === 'review' ? 'is-current' : (step === 'save' ? 'is-done' : '');
        stepSave.className = step === 'save' ? 'is-current' : '';
    }

    function setProgress(done, total, label, animate) {
        var percent = total > 0 ? Math.min(100, Math.round((done / total) * 100)) : 0;
        bar.style.width = percent + '%';
        bar.setAttribute('aria-valuenow', String(percent));
        statusText.textContent = label;
        countText.textContent = total > 0 ? Number(done).toLocaleString() + ' / ' + Number(total).toLocaleString() : '';
        bar.classList.toggle('progress-bar-animated', !!animate);
        bar.classList.toggle('progress-bar-striped', !!animate);
    }

    function setLocked(on) {
        busy = !!on;
        if (!lock) {
            return;
        }
        if (on) {
            lock.classList.remove('d-none');
            document.body.classList.add('import-busy');
            input.disabled = true;
            button.disabled = true;
        } else {
            lock.classList.add('d-none');
            document.body.classList.remove('import-busy');
            input.disabled = false;
        }
    }

    function formatColumn(column) {
        var text = String(column == null ? '' : column).replace(/\u00a0/g, ' ').replace(/[\r\n\t]+/g, ' ');
        text = text.replace(/\s+/g, ' ').trim();
        text = text.replace(/&/g, 'and').replace(/#/g, 'number').replace(/%/g, 'percentage');
        return text.toLowerCase().replace(/ /g, '_');
    }

    function canonicalColumn(name) {
        if (name === 'stockid' || name === 'stock_no' || name === 'stock_number') {
            return 'stock_id';
        }
        return name;
    }

    function buildHeader(cells) {
        var width = 0;
        var index;
        for (index = 0; index < cells.length; index++) {
            if (cells[index] != null && String(cells[index]).trim() !== '') {
                width = index + 1;
            }
        }
        if (width < 1) {
            return { error: 'The Excel file is missing a header row.' };
        }
        if (width > MAX_COLUMNS) {
            return { error: 'The Excel file has too many columns.' };
        }
        var headerKeys = [];
        var headerLabels = [];
        var used = Object.create(null);
        for (index = 0; index < width; index++) {
            var label = cells[index] == null ? '' : String(cells[index]).replace(/\u00a0/g, ' ').replace(/\s+/g, ' ').trim();
            var name = canonicalColumn(formatColumn(label));
            if (!name) {
                name = 'column';
            }
            var base = name;
            var suffix = 2;
            while (used[name]) {
                name = base + '_' + suffix;
                suffix++;
            }
            used[name] = true;
            headerKeys.push(name);
            headerLabels.push(label);
        }
        if (headerKeys.indexOf('stock_id') === -1) {
            return { error: 'The Excel file must include a Stock Id column.' };
        }
        return { keys: headerKeys, labels: headerLabels };
    }

    function isNumeric(value) {
        return typeof value === 'string' && /^-?(?:\d+\.?\d*|\.\d+)$/.test(value);
    }

    function sanitizeCell(value) {
        if (value == null) {
            return null;
        }
        var text = String(value).replace(/\u00a0/g, ' ').trim();
        if (text === '') {
            return null;
        }
        var numeric = text.replace(/[, $₹]/g, '');
        if (numeric.charAt(numeric.length - 1) === '%') {
            numeric = numeric.slice(0, -1);
        }
        if (isNumeric(numeric)) {
            text = numeric;
        }
        if (text.length > 4000) {
            text = text.slice(0, 4000);
        }
        return text;
    }

    function fixed(value, scale) {
        var number = Number(value);
        if (!isFinite(number)) {
            number = 0;
        }
        var factor = Math.pow(10, scale);
        return (Math.round(number * factor) / factor).toFixed(scale);
    }

    function phpPresent(value) {
        return value != null && value !== '' && value !== '0';
    }

    function columnLabel(column) {
        return String(column).replace(/_/g, ' ').replace(/\b\w/g, function (letter) {
            return letter.toUpperCase();
        });
    }

    function normalizeRecord(record) {
        var copy = {};
        var key;
        for (key in record) {
            if (Object.prototype.hasOwnProperty.call(record, key)) {
                copy[key] = record[key];
            }
        }
        var length = Number(copy.length || 0);
        var width = Number(copy.width || 0);
        var weight = Number(copy.weight || 0);
        var liveRap = Number(copy.live_rap || 0);
        var discounts = Number(copy.discounts || 0);
        copy.ratio = phpPresent(copy.ratio) ? fixed(copy.ratio, 2) : fixed(length / (width > 0 ? width : 1), 2);
        copy.rap_amount = phpPresent(copy.rap_amount) ? fixed(copy.rap_amount, 2) : fixed(weight * liveRap, 2);
        copy.price_per_carat = phpPresent(copy.price_per_carat) ? fixed(copy.price_per_carat, 2) : fixed((liveRap * (discounts / 100)) + liveRap, 2);
        var price = Number(copy.price_per_carat || 0);
        copy.total_price = phpPresent(copy.total_price) ? fixed(copy.total_price, 2) : fixed(weight * price, 2);
        copy.bargaining_price_per_carat = phpPresent(copy.bargaining_price_per_carat) ? fixed(copy.bargaining_price_per_carat, 2) : fixed(0, 2);
        var bargain = Number(copy.bargaining_price_per_carat || 0);
        copy.bargaining_total_price = phpPresent(copy.bargaining_total_price) ? fixed(copy.bargaining_total_price, 2) : fixed(weight * bargain, 2);
        return copy;
    }

    function validateRow(cells, seen) {
        var record = {};
        var index;
        for (index = 0; index < keys.length; index++) {
            record[keys[index]] = cells[index] == null ? null : cells[index];
        }
        var hasValue = false;
        for (index = 0; index < cells.length; index++) {
            if (cells[index] != null && cells[index] !== '') {
                hasValue = true;
                break;
            }
        }
        if (!hasValue) {
            return { blank: true, issues: [] };
        }

        var normalized = normalizeRecord(record);
        var issues = [];
        var stock = normalized.stock_id == null ? '' : String(normalized.stock_id).trim();
        var stockKey = stock.toLowerCase();
        if (!stock) {
            issues.push('Stock id is required.');
        } else if (seen[stockKey]) {
            issues.push('Duplicate stock id "' + stock + '".');
        }

        var column;
        for (column in columnRules) {
            if (!Object.prototype.hasOwnProperty.call(columnRules, column)) {
                continue;
            }
            var rule = columnRules[column];
            var value = normalized[column];
            if (value == null || value === '' || !rule || !rule.kind) {
                continue;
            }
            var label = columnLabel(column);
            if (rule.kind === 'decimal') {
                if (!isNumeric(String(value))) {
                    issues.push(label + ' must be a number.');
                } else {
                    var formatted = fixed(value, parseInt(rule.scale, 10) || 0);
                    var integer = formatted.charAt(0) === '-' ? formatted.slice(1) : formatted;
                    integer = integer.split('.')[0];
                    var digits = (parseInt(rule.precision, 10) || 0) - (parseInt(rule.scale, 10) || 0);
                    if (integer.length > digits) {
                        issues.push(label + ' is too large.');
                    }
                }
            } else if (rule.kind === 'string' && !isNumeric(String(value)) && String(value).length > parseInt(rule.length, 10)) {
                issues.push(label + ' is longer than ' + rule.length + ' characters.');
            }
        }

        return { blank: false, issues: issues, stockKey: stockKey };
    }

    function visibleRows() {
        if (filter === 'errors') {
            return errors;
        }
        if (filter === 'ready') {
            return ready;
        }
        return errors.concat(ready);
    }

    function updateSummary() {
        var errorCount = errors.length;
        var readyCount = ready.length;
        countAll.textContent = (errorCount + readyCount).toLocaleString();
        countErrors.textContent = errorCount.toLocaleString();
        countReady.textContent = readyCount.toLocaleString();
        if (errorCount > 0) {
            if (errorCount === 1) {
                banner.textContent = '1 row with an error is listed first in red and will not be imported. Hover a red row to see the reason.';
            } else {
                banner.textContent = errorCount.toLocaleString() + ' rows with errors are listed first in red and will not be imported. Hover a red row to see the reason.';
            }
        } else if (readyCount > 0) {
            banner.textContent = 'All rows are ready to import.';
        } else {
            banner.textContent = 'The file has no data rows.';
        }
        summary.classList.remove('d-none');
        if (!busy && !finished) {
            button.disabled = !reviewReady || readyCount < 1;
            buttonLabel.textContent = readyCount > 0 ? 'Import ' + readyCount.toLocaleString() + ' rows' : 'Import';
        }
    }

    function scheduleRender() {
        if (renderScheduled) {
            return;
        }
        renderScheduled = true;
        window.requestAnimationFrame(function () {
            renderScheduled = false;
            renderBody();
        });
    }

    function renderBody() {
        var rows = visibleRows();
        var total = rows.length;
        var scrollTop = scrollBox.scrollTop;
        var view = scrollBox.clientHeight || 520;
        lockScroll = true;
        var start = Math.max(0, Math.floor(scrollTop / rowHeight) - 4);
        var count = Math.ceil(view / rowHeight) + 12;
        var end = Math.min(total, start + count);
        var top = start * rowHeight;
        var bottom = Math.max(0, (total - end) * rowHeight);
        var colspan = visibleColumnCount();
        var html = '';
        if (top > 0) {
            html += '<tr class="spacer"><td colspan="' + colspan + '" style="height:' + top + 'px;line-height:0;font-size:0"></td></tr>';
        }
        var index;
        for (index = start; index < end; index++) {
            html += renderRow(rows[index], index);
        }
        if (bottom > 0) {
            html += '<tr class="spacer"><td colspan="' + colspan + '" style="height:' + bottom + 'px;line-height:0;font-size:0"></td></tr>';
        }
        if (!html) {
            html = '<tr><td class="text-muted" colspan="' + colspan + '">No rows to show.</td></tr>';
        }
        body.innerHTML = html;
        if (scrollBox.scrollTop !== scrollTop) {
            scrollBox.scrollTop = scrollTop;
        }
        lockScroll = false;
        updateSummary();
    }

    function renderRow(row, index) {
        var state = row.state || (row.issues.length ? 'error' : 'ready');
        var issueText = row.issues.length ? row.issues.join(' ') : '';
        var css = 'is-' + state;
        if (state !== 'error' && index % 2 === 1) {
            css += ' is-alt';
        }
        var tip = issueText || '';
        var html = '<tr class="' + css + '">';
        html += '<td class="row-no" title="' + escapeHtml(tip || ('Row ' + row.number)) + '">' + row.number + '</td>';
        var cell;
        for (cell = 0; cell < row.cells.length; cell++) {
            if (isSerialColumn(cell)) {
                continue;
            }
            var value = row.cells[cell] == null ? '' : row.cells[cell];
            var title = tip ? tip : value;
            html += '<td title="' + escapeHtml(title) + '">' + escapeHtml(value) + '</td>';
        }
        html += '</tr>';
        return html;
    }

    function isSerialColumn(index) {
        var key = String(keys[index] || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        var label = String(labels[index] || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        return key === 'serialno' || key === 'serialnumber' || key === 'srno' || key === 'sno'
            || label === 'serialno' || label === 'serialnumber' || label === 'srno' || label === 'sno';
    }

    function visibleColumnCount() {
        var count = 1;
        var index;
        for (index = 0; index < labels.length; index++) {
            if (!isSerialColumn(index)) {
                count++;
            }
        }
        return count;
    }

    function renderHead() {
        var html = '<tr><th class="row-no">No.</th>';
        var index;
        for (index = 0; index < labels.length; index++) {
            if (isSerialColumn(index)) {
                continue;
            }
            html += '<th>' + escapeHtml(labels[index] || keys[index]) + '</th>';
        }
        html += '</tr>';
        head.innerHTML = html;
    }

    function resetTable() {
        labels = [];
        keys = [];
        errors = [];
        ready = [];
        filter = 'all';
        finished = false;
        reviewReady = false;
        head.innerHTML = '';
        body.innerHTML = '';
        preview.classList.add('d-none');
        summary.classList.add('d-none');
        if (tableCard) {
            tableCard.classList.add('d-none');
        }
        button.disabled = true;
        buttonLabel.textContent = 'Import';
        setActiveFilter('all');
    }

    function setActiveFilter(next) {
        filter = next;
        var buttons = summary.querySelectorAll('[data-filter]');
        var index;
        for (index = 0; index < buttons.length; index++) {
            buttons[index].classList.toggle('is-active', buttons[index].getAttribute('data-filter') === next);
        }
    }

    function denseRow(sparse, width) {
        var cells = [];
        var column;
        for (column = 0; column < width; column++) {
            var value = sparse ? sparse[column] : null;
            cells.push(value == null || value === '' ? null : sanitizeCell(value));
        }
        return cells;
    }

    function tidyNumber(text) {
        if (!/^-?\d+\.\d{6,}$/.test(text)) {
            return text;
        }
        var number = Number(text);
        if (!isFinite(number)) {
            return text;
        }
        var rounded = Math.round(number * 1000000) / 1000000;
        if (Math.abs(rounded - Math.round(rounded)) < 0.0000001) {
            return String(Math.round(rounded));
        }
        return rounded.toFixed(6).replace(/0+$/, '').replace(/\.$/, '');
    }

    function formatCell(cell) {
        if (!cell || cell.v == null || cell.v === '') {
            return null;
        }
        var text = '';
        try {
            if (typeof XLSX !== 'undefined' && XLSX.utils && XLSX.utils.format_cell) {
                text = XLSX.utils.format_cell(cell);
            }
        } catch (error) {
            text = '';
        }
        if (text == null || String(text).trim() === '') {
            text = cell.w != null && String(cell.w).trim() !== '' ? cell.w : cell.v;
        }
        text = tidyNumber(String(text).trim());
        return text === '' ? null : text;
    }

    function postBatch(payload) {
        return fetch(batchUrl, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).then(function (response) {
            return response.text().then(function (text) {
                var body = {};
                if (text) {
                    try {
                        body = JSON.parse(text);
                    } catch (error) {
                        if (response.status === 419 || response.status === 401) {
                            throw new Error('Your session expired. Refresh the page and sign in again.');
                        }
                        throw new Error('The server could not complete that step. The current catalog was not changed.');
                    }
                }
                if (response.status === 419 || response.status === 401) {
                    throw new Error('Your session expired. Refresh the page and sign in again.');
                }
                if (!response.ok || !body.ok) {
                    throw new Error((body && body.message) || 'The import failed. The current catalog was not changed.');
                }
                return body;
            });
        });
    }

    function readWorkbook(file, myTicket) {
        return new Promise(function (resolve, reject) {
            var reader = new FileReader();
            reader.onload = function (event) {
                window.setTimeout(function () {
                    if (myTicket !== ticket) {
                        reject(new Error('cancelled'));
                        return;
                    }
                    try {
                        if (typeof XLSX === 'undefined') {
                            throw new Error('The spreadsheet library did not load. Refresh the page and try again.');
                        }
                        resolve(XLSX.read(event.target.result, { type: 'array', cellDates: false }));
                    } catch (error) {
                        reject(error);
                    }
                }, 30);
            };
            reader.onerror = function () {
                reject(new Error('The file could not be read.'));
            };
            reader.readAsArrayBuffer(file);
        });
    }

    function activeSheet(workbook) {
        var names = workbook.SheetNames || [];
        var index = 0;
        if (workbook.Workbook && workbook.Workbook.WBView && workbook.Workbook.WBView[0] && workbook.Workbook.WBView[0].activeTab) {
            index = workbook.Workbook.WBView[0].activeTab;
        }
        if (!names[index]) {
            index = 0;
        }
        return workbook.Sheets[names[index]] || null;
    }

    function indexSheet(sheet, myTicket, done) {
        setProgress(0, 1, 'Preparing rows…', true);
        window.setTimeout(function () {
            if (myTicket !== ticket) {
                return;
            }
            startIndexing(sheet, myTicket, done);
        }, 30);
    }

    function startIndexing(sheet, myTicket, done) {
        var keysInSheet = Object.keys(sheet);
        var grouped = Object.create(null);
        var cursor = 0;
        var wide = false;
        var tooMany = false;

        function step() {
            if (myTicket !== ticket) {
                return;
            }
            var end = Math.min(cursor + 8000, keysInSheet.length);
            var index;
            for (index = cursor; index < end; index++) {
                var key = keysInSheet[index];
                if (!key || key.charAt(0) === '!') {
                    continue;
                }
                var coord = XLSX.utils.decode_cell(key);
                if (coord.c >= MAX_COLUMNS) {
                    if (coord.r === 0) {
                        wide = true;
                    }
                    continue;
                }
                if (coord.r > MAX_ROWS) {
                    tooMany = true;
                    continue;
                }
                var value = formatCell(sheet[key]);
                if (value == null) {
                    continue;
                }
                if (!grouped[coord.r]) {
                    grouped[coord.r] = [];
                }
                grouped[coord.r][coord.c] = value;
            }
            cursor = end;
            setProgress(cursor, keysInSheet.length, 'Reading cells…', true);
            if (cursor < keysInSheet.length) {
                window.setTimeout(step, 0);
                return;
            }
            done(grouped, wide, tooMany);
        }

        setProgress(0, keysInSheet.length, 'Reading cells…', true);
        window.setTimeout(step, 0);
    }

    function classifyRows(grouped, myTicket, ignoredWide) {
        var rowNumbers = Object.keys(grouped).map(function (key) {
            return parseInt(key, 10);
        }).filter(function (number) {
            return !isNaN(number);
        });
        rowNumbers.sort(function (a, b) {
            return a - b;
        });
        if (rowNumbers.length === 0 || rowNumbers[0] !== 0 || !grouped[0]) {
            showAlert('danger', 'The Excel file is missing a header row.');
            setStep('choose');
            setLocked(false);
            return;
        }
        var headerCells = [];
        var last = 0;
        var column;
        var sparseHeader = grouped[0];
        for (column = 0; column < MAX_COLUMNS; column++) {
            if (sparseHeader[column] != null && String(sparseHeader[column]).trim() !== '') {
                last = column + 1;
            }
        }
        for (column = 0; column < last; column++) {
            headerCells.push(sparseHeader[column] == null ? null : String(sparseHeader[column]));
        }
        var header = buildHeader(headerCells);
        if (header.error) {
            showAlert('danger', header.error + (ignoredWide ? ' Columns after column 120 were ignored.' : ''));
            setStep('choose');
            setLocked(false);
            return;
        }
        if (ignoredWide) {
            showAlert('warning', 'Columns after column 120 were ignored.');
        }
        labels = header.labels;
        keys = header.keys;
        renderHead();
        preview.classList.remove('d-none');
        if (tableCard) {
            tableCard.classList.remove('d-none');
        }
        var dataRows = [];
        var index;
        for (index = 0; index < rowNumbers.length; index++) {
            if (rowNumbers[index] === 0) {
                continue;
            }
            dataRows.push(rowNumbers[index]);
        }
        if (!dataRows.length) {
            showAlert('danger', 'The Excel file has no data rows.');
            setStep('choose');
            setLocked(false);
            return;
        }
        var seen = Object.create(null);
        var cursor = 0;
        var stopped = false;

        function step() {
            if (myTicket !== ticket) {
                return;
            }
            var end = Math.min(cursor + 250, dataRows.length);
            var rowIndex;
            for (rowIndex = cursor; rowIndex < end; rowIndex++) {
                var excelRow = dataRows[rowIndex];
                var cells = denseRow(grouped[excelRow], keys.length);
                var result = validateRow(cells, seen);
                if (result.blank) {
                    continue;
                }
                var row = {
                    number: errors.length + ready.length + 1,
                    cells: cells,
                    issues: result.issues,
                    state: result.issues.length ? 'error' : 'ready'
                };
                if (result.issues.length) {
                    errors.push(row);
                } else {
                    if (result.stockKey) {
                        seen[result.stockKey] = true;
                    }
                    ready.push(row);
                }
                if (errors.length + ready.length > MAX_ROWS) {
                    stopped = true;
                    break;
                }
            }
            cursor = end;
            setProgress(Math.min(cursor, dataRows.length), dataRows.length, 'Checking rows…', true);
            setStep('review');
            scheduleRender();
            if (stopped) {
                reviewReady = false;
                showAlert('danger', 'The Excel file has too many rows to import.');
                button.disabled = true;
                setLocked(false);
                return;
            }
            if (cursor < dataRows.length) {
                window.setTimeout(step, 0);
                return;
            }
            reviewReady = true;
            setLocked(false);
            scrollBox.scrollTop = 0;
            scheduleRender();
        }

        window.setTimeout(step, 0);
    }

    function extractFile(file) {
        var myTicket = ++ticket;
        resetTable();
        clearAlert();
        setStep('choose');
        var extension = file.name.split('.').pop().toLowerCase();
        if (extension !== 'xlsx' && extension !== 'xls') {
            showAlert('danger', 'Please upload an Excel file (.xlsx or .xls).');
            return;
        }
        setLocked(true);
        setProgress(0, 1, 'Opening the workbook…', true);
        readWorkbook(file, myTicket).then(function (workbook) {
            if (myTicket !== ticket) {
                return;
            }
            var sheet = activeSheet(workbook);
            if (!sheet) {
                throw new Error('The Excel file does not contain a worksheet.');
            }
            indexSheet(sheet, myTicket, function (grouped, wide, tooMany) {
                if (myTicket !== ticket) {
                    return;
                }
                if (tooMany) {
                    showAlert('danger', 'The Excel file has too many rows to import.');
                    setStep('choose');
                    setLocked(false);
                    return;
                }
                classifyRows(grouped, myTicket, wide);
            });
        }).catch(function (error) {
            if (myTicket !== ticket || error.message === 'cancelled') {
                return;
            }
            setStep('choose');
            setLocked(false);
            showAlert('danger', error.message || 'The workbook could not be read.');
        });
    }

    function markSaving(rows, state) {
        var index;
        for (index = 0; index < rows.length; index++) {
            if (rows[index].state !== 'error') {
                rows[index].state = state;
            }
        }
    }

    function applyBatchResults(part, results) {
        var index;
        for (index = 0; index < part.length; index++) {
            var result = results && results[index] ? results[index] : { ok: false, error: 'The server did not accept this row.' };
            if (result.blank) {
                part[index].state = 'ready';
                continue;
            }
            if (result.ok) {
                part[index].state = 'saved';
                continue;
            }
            part[index].state = 'error';
            part[index].issues = [result.error || 'This row could not be saved.'];
            var readyIndex = ready.indexOf(part[index]);
            if (readyIndex !== -1) {
                ready.splice(readyIndex, 1);
            }
            errors.push(part[index]);
        }
    }

    function catalogMessage(message) {
        if (!message) {
            return 'The import failed. The current catalog was not changed.';
        }
        if (message.toLowerCase().indexOf('catalog') === -1) {
            return message + ' The current catalog was not changed.';
        }
        return message;
    }

    function runImport() {
        if (busy || finished || !ready.length) {
            return;
        }
        var errorCount = errors.length;
        var readyCount = ready.length;
        var message = readyCount.toLocaleString() + ' rows will be saved.';
        if (errorCount) {
            message += '<br>' + errorCount.toLocaleString() + ' rows with errors will be skipped.';
        }
        if (typeof Swal === 'undefined') {
            showAlert('danger', 'The confirmation dialog could not be loaded. Refresh the page and try again.');
            return;
        }
        Swal.fire({
            title: 'Replace the current catalog?',
            html: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Import',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            focusCancel: true,
            buttonsStyling: true
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }
            beginImport();
        });
    }

    function beginImport() {
        finished = false;
        setLocked(true);
        button.disabled = true;
        spinner.classList.remove('d-none');
        clearAlert();
        setStep('save');
        var planned = ready.slice();
        var offset = 0;
        var token = '';
        setProgress(0, planned.length, 'Starting import…', true);

        postBatch({ phase: 'start', headers: labels }).then(function (started) {
            token = started.token;
            function next() {
                if (offset >= planned.length) {
                    var saved = 0;
                    var index;
                    for (index = 0; index < planned.length; index++) {
                        if (planned[index].state === 'saved') {
                            saved++;
                        }
                    }
                    if (saved < 1) {
                        return postBatch({ phase: 'cancel', token: token }).catch(function () {}).then(function () {
                            throw new Error('No valid rows were saved. The current catalog was not changed.');
                        });
                    }
                    setProgress(planned.length, planned.length, 'Updating the catalog…', true);
                    return postBatch({ phase: 'finish', token: token });
                }
                var part = planned.slice(offset, offset + batchSize);
                var start = offset;
                offset += part.length;
                markSaving(part, 'saving');
                scheduleRender();
                setProgress(start, planned.length, 'Saving rows…', true);
                return postBatch({
                    phase: 'rows',
                    token: token,
                    rows: part.map(function (row) {
                        return row.cells;
                    })
                }).then(function (result) {
                    applyBatchResults(part, result.results || []);
                    setProgress(Math.min(start + part.length, planned.length), planned.length, 'Saving rows…', true);
                    scheduleRender();
                    return next();
                });
            }
            return next();
        }).then(function (result) {
            var message = (result && result.message) || 'Excel file imported successfully.';
            if (result && typeof result.stored === 'number') {
                message += ' ' + Number(result.stored).toLocaleString() + ' stones saved.';
                if (result.skipped) {
                    message += ' ' + Number(result.skipped).toLocaleString() + ' rows were skipped.';
                }
            }
            message += ' The catalog has been replaced.';
            resetTable();
            finished = true;
            setStep('choose');
            showAlert('success', message);
        }).catch(function (error) {
            if (finished) {
                return;
            }
            if (token) {
                postBatch({ phase: 'cancel', token: token }).catch(function () {});
            }
            markSaving(planned, 'ready');
            showAlert('danger', catalogMessage(error.message));
            statusText.textContent = 'Import stopped';
            bar.classList.remove('progress-bar-animated');
            scheduleRender();
        }).then(function () {
            setLocked(false);
            spinner.classList.add('d-none');
            if (!finished) {
                updateSummary();
            }
        });
    }

    input.addEventListener('change', function () {
        if (busy) {
            return;
        }
        var file = input.files && input.files[0];
        resetTable();
        clearAlert();
        setStep('choose');
        if (!file) {
            return;
        }
        input.value = '';
        extractFile(file);
    });

    summary.addEventListener('click', function (event) {
        var target = event.target.closest('[data-filter]');
        if (!target) {
            return;
        }
        setActiveFilter(target.getAttribute('data-filter'));
        scrollBox.scrollTop = 0;
        scheduleRender();
    });

    scrollBox.addEventListener('scroll', function () {
        if (lockScroll) {
            return;
        }
        scheduleRender();
    });

    button.addEventListener('click', runImport);
    setStep('choose');
})();
