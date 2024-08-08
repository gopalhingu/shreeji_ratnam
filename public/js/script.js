
let postData = {};

let defaultFilter = {
    currentPage: 1,
    currentPerPage: 10,
    currentSortColumn: 'id',
    currentSortDirection: 'asc',
    totalPage: 0,
};

let singleFilter = {
    minCarat: '',
    maxCarat: '',
    minLength: '',
    maxLength: '',
    minWidth: '',
    maxWidth: '',
    minHeight: '',
    maxHeight: '',
    minDepth: '',
    maxDepth: '',
    minRatio: '',
    maxRatio: '',
    minTable: '',
    maxTable: '',
    stockId: '',
    reportNumber: '',
    type: '',
};

let multipleFilter = {
    shapeList: [],
    colorList: [],
    clarityList: [],
    cutList: [],
    polishList: [],
    symmetryList: [],
    labList: [],
};

let placeholder = {
    shapeList: '<span class="text-muted"><b>Shape</b></span><br><span class="text-muted">Please choose one or more</span>',
    colorList: '<span class="text-muted"><b>Color</b></span><br><span class="text-muted">Please choose one or more</span>',
    clarityList: '<span class="text-muted"><b>Clarity</b></span><br><span class="text-muted">Please choose one or more</span>',
    cutList: '<span class="text-muted"><b>Cut</b></span><br><span class="text-muted">Please choose one or more</span>',
    polishList: '<span class="text-muted"><b>Polish</b></span><br><span class="text-muted">Please choose one or more</span>',
    symmetryList: '<span class="text-muted"><b>Symmetry</b></span><br><span class="text-muted">Please choose one or more</span>',
    labList: '<span class="text-muted"><b>Lab</b></span><br><span class="text-muted">Please choose one or more</span>',
};

let checkInput = [
    "fullShapeList",
    "fullColorList",
    "fullClarityList",
    "fullCutList",
    "fullPolishList",
    "fullSymmetryList",
    "fullLabList",
];

// Utility function to clear all filters
function clearFilters() {

    // Set Default Value
    defaultFilter['currentPage'] = 1;
    defaultFilter['currentPerPage'] = 10;
    defaultFilter['currentSortColumn'] = 'id';
    defaultFilter['currentSortDirection'] = 'asc';
    defaultFilter['totalPage'] = 0;
    
    // Clear single filter
    $.each(singleFilter, function(k, v) {
        singleFilter[k] = '';
    });

    // Clear input fields
    $.each(singleFilter, function(k, v) {
        $("#"+k).val('');
    });

    // Clear multiple filter
    $.each(multipleFilter, function(k, v) {
        multipleFilter[k] = '';
    });

    // Clear Array tags
    $.each(placeholder, function(k, v) {
        $("#"+k).empty().html(v);
    });

    // Uncheck all checkboxes in filter lists
    $.each(checkInput, function(k, v) {
        $('#'+v+' input').prop('checked', false);
    });

    // Trigger filter with updated settings
    fetchData();
}

// Attach event listener to Clear Filter button
$(document).on('click', '.filter-clear-button', function () {
    clearFilters();
});

function showModal(modalId) {
    $(modalId).modal('show');
}

$('#shapeList').on('click', function () { showModal('#shapeModal'); });
$('#colorList').on('click', function () { showModal('#colorModal'); });
$('#clarityList').on('click', function () { showModal('#clarityModal'); });
$('#cutList').on('click', function () { showModal('#cutModal'); });
$('#polishList').on('click', function () { showModal('#polishModal'); });
$('#symmetryList').on('click', function () { showModal('#symmetryModal'); });
$('#labList').on('click', function () { showModal('#labModal'); });

function applyFilter(modalId, fullListId, placeholderMessage, tagClass, id) {

    const selectedItems = $(fullListId + ' input:checked').map(function () {
        return $(this).val();
    }).get();

    multipleFilter[id] = selectedItems;

    $("#"+id).empty();
    if (selectedItems.length === 0) {
        $("#"+id).html(placeholder[id]);
    } else {
        $('#'+placeholderMessage).remove();

        $.each(selectedItems, function (index, item) {
            $("#"+id).append(
                '<span class="badge bg-primary me-2 mb-1">' + item +
                ' <span class="' + tagClass + '" style="cursor:pointer;">&times;</span></span>'
            );
        });
    }
    $(modalId).modal('hide');
    fetchData();
}

$(document).on('click', '.apply-shape-filter', function () {
    applyFilter('#shapeModal', '#fullShapeList', 'placeholderMessage', 'remove-tag', 'shapeList');
});

$(document).on('click', '.apply-color-filter', function () {
    applyFilter('#colorModal', '#fullColorList', 'colorPlaceholderMessage', 'remove-color-tag', 'colorList');
});

$(document).on('click', '.apply-clarity-filter', function () {
    applyFilter('#clarityModal', '#fullClarityList', 'clarityPlaceholderMessage', 'remove-clarity-tag', 'clarityList');
});

$(document).on('click', '.apply-cut-filter', function () {
    applyFilter('#cutModal', '#fullCutList', 'cutPlaceholderMessage', 'remove-cut-tag', 'cutList');
});

$(document).on('click', '.apply-polish-filter', function () {
    applyFilter('#polishModal', '#fullPolishList', 'polishPlaceholderMessage', 'remove-polish-tag', 'polishList');
});

$(document).on('click', '.apply-symmetry-filter', function () {
    applyFilter('#symmetryModal', '#fullSymmetryList', 'symmetryPlaceholderMessage', 'remove-symmetry-tag', 'symmetryList');
});

$(document).on('click', '.apply-lab-filter', function () {
    applyFilter('#labModal', '#fullLabList', 'labPlaceholderMessage', 'remove-lab-tag', 'labList');
});

function removeTag(tag, id, fullListId, placeholderMessage) {
    var item = $(tag).closest('.badge').text().trim().slice(0, -2);
    $(tag).closest('.badge').remove();
    $(fullListId + ' input[value="' + item + '"]').prop('checked', false);

    if ($(id + ' .badge').length === 0) {
        $("#"+id).html(placeholder[id]);
    }
    fetchData();
}

$(document).on('click', '.remove-tag', function () {
    removeTag(this, 'shapeList', '#fullShapeList', 'placeholderMessage');
});

$(document).on('click', '.remove-color-tag', function () {
    removeTag(this, 'colorList', '#fullColorList', 'colorPlaceholderMessage');
});

$(document).on('click', '.remove-clarity-tag', function () {
    removeTag(this, 'clarityList', '#fullClarityList', 'clarityPlaceholderMessage');
});

$(document).on('click', '.remove-cut-tag', function () {
    removeTag(this, 'cutList', '#fullCutList', 'cutPlaceholderMessage');
});

$(document).on('click', '.remove-polish-tag', function () {
    removeTag(this, 'polishList', '#fullPolishList', 'polishPlaceholderMessage');
});

$(document).on('click', '.remove-symmetry-tag', function () {
    removeTag(this, 'symmetryList', '#fullSymmetryList', 'symmetryPlaceholderMessage');
});

$(document).on('click', '.remove-lab-tag', function () {
    removeTag(this, 'labList', '#fullLabList', 'labPlaceholderMessage');
});

document.querySelectorAll('.focusable').forEach(element => {
    element.addEventListener('click', () => {
        element.focus();
    });
});

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('downloadCsv').addEventListener('click', function () {
        exportToCSV();
    });

    document.getElementById('downloadExcel').addEventListener('click', function () {
        exportToExcel();
    });

    document.getElementById('downloadBoth').addEventListener('click', function () {
        exportToCSV();
        exportToExcel();
    });
});

$(document).ready(function () {

    $('.whatsappform').on('click', function () {
      
        // Get selected stock IDs
        var selectedStockIDs = [];
        $('.selectSingle input[type="checkbox"]:checked').each(function () {
            var stockID = $(this).closest('tr').find('.stock-id').text();
            selectedStockIDs.push(stockID);
        });
        var stockIDText = selectedStockIDs.join('\n');

        const phone_number = $(this).find('.contact-info').find('.contact-number').text().replace(/\D/g, '');
        const encodedText = encodeURIComponent(stockIDText);
        const whatsappURL = `https://api.whatsapp.com/send/?phone=%2B${phone_number}&text=I%20Want%20To%20Select%20This%20Stock%20IDs%3A%0A${encodedText}&type=phone_number&app_absent=0`;
        window.open(whatsappURL, '_blank');
    });

    $(document).on('click', '.filter-button', function (e) {
        e.preventDefault();
        defaultFilter['currentPage'] = 1;
        fetchData();
        $('#filterModal').modal('hide');
    });

    // Initial data fetch
    $(".previousPage").addClass("hide");
    fetchData();

    $(document).on('click', '.sorting', function (e) {
        e.preventDefault();

        defaultFilter['currentSortColumn'] = $(this).data('column');
        defaultFilter['currentSortDirection'] = ($(this).data("order") == 'asc') ? 'desc' : 'asc';

        $(".sorting_icon").addClass("hide");
        $(".sorting_icon.default").removeClass("hide");

        $(this).data("order", defaultFilter['currentSortDirection']);
        $(this).find(".sorting_icon").addClass("hide");

        if (defaultFilter['currentSortDirection'] == 'asc') {
            $(this).find(".ascending").removeClass("hide");
        }
        if (defaultFilter['currentSortDirection'] == 'desc') {
            $(this).find(".decending").removeClass("hide");
        }

        fetchData();
    });

    $(document).on('click', '.reloadPage', function (e) {
        e.preventDefault();

        $(".changeNumberOfPerPage").val(defaultFilter['currentPerPage']);
        clearFilters();
    });

    $(document).on('change', '.changeNumberOfPerPage', function (e) {
        e.preventDefault();

        defaultFilter['currentPage'] = 1;
        defaultFilter['currentPerPage'] = $(this).val();
        fetchData();
    });

    $(document).on('click', '.selectAll', function (e) {
        e.preventDefault();

        var $checkbox = $(this).find('input[type="checkbox"]');
        var $checkmark = $(this).find('span');

        $checkmark.removeClass("minus");

        var $checkboxSingle = $('.selectSingle').find('input[type="checkbox"]');
        var $checkmarkSingle = $('.selectSingle span');
        var $row = $checkmarkSingle.closest('tr');

        if ($checkbox.is(':checked')) {
            $checkbox.prop('checked', false);
            $checkmark.removeClass("checkmark");

            $checkboxSingle.prop('checked', false);
            $checkmarkSingle.removeClass("checkmark");
            $row.removeClass("selected");
        } else {
            $checkbox.prop('checked', true);
            $checkmark.addClass("checkmark");

            $checkboxSingle.prop('checked', true);
            $checkmarkSingle.addClass("checkmark");
            $row.addClass("selected");
        }
        toggleButtons();
    });

    $(document).on('click', '.selectSingle', function (e) {
        e.preventDefault();

        var $checkbox = $(this).find('input[type="checkbox"]');
        var $checkmark = $(this).find('span');
        var $row = $(this).closest('tr');

        if ($checkbox.is(':checked')) {
            $checkbox.prop('checked', false);
            $checkmark.removeClass("checkmark");
            $row.removeClass("selected");
        } else {
            $checkbox.prop('checked', true);
            $checkmark.addClass("checkmark");
            $row.addClass("selected");
        }

        checkSingleSelect();
        toggleButtons();
    });

    function toggleButtons() {
        const anyChecked = $('.selectSingle input:checked').length > 0;
        if (anyChecked) {
            $('.contact-button').hide();
            $('.whatsapp-button').show();
        } else {
            $('.contact-button').show();
            $('.whatsapp-button').hide();
        }
    }

    $(document).on('click', '.previousPage', function (e) {
        e.preventDefault();

        defaultFilter['currentPage']--;
        fetchData();

        if (defaultFilter['currentPage'] <= 1) {
            $(this).addClass("hide");
        }
        if (defaultFilter['currentPage'] < defaultFilter['totalPage']) {
            $(".nextPage").removeClass("hide");
        }
    });

    $(document).on('click', '.nextPage', function (e) {
        e.preventDefault();

        defaultFilter['currentPage']++;
        fetchData();

        if (defaultFilter['currentPage'] > 1) {
            $(".previousPage").removeClass("hide");
        }
        if (defaultFilter['currentPage'] >= defaultFilter['totalPage']) {
            $(this).addClass("hide");
        }
    });

    $(document).on('change', '.changePage', function (e) {
        e.preventDefault();

        defaultFilter['currentPage'] = $(this).val();

        if(defaultFilter['currentPage'] > defaultFilter['totalPage']) {
            alert("Please Enter less than total page");
            defaultFilter['currentPage'] = '';
            $(this).val('');
            return;
        }

        fetchData();

        if (defaultFilter['currentPage'] <= 1) {
            $(".previousPage").addClass("hide");
        }
        if (defaultFilter['currentPage'] >= defaultFilter['totalPage']) {
            $(".nextPage").addClass("hide");
        }
        if (defaultFilter['currentPage'] > 1) {
            $(".previousPage").removeClass("hide");
        }
        if (defaultFilter['currentPage'] < defaultFilter['totalPage']) {
            $(".nextPage").removeClass("hide");
        }
    });

});

function fetchData() {

    $.each(singleFilter, function(k, v) {
        singleFilter[k] = $("#"+k).val();
    });
    $('#loader').show();
    postData = {
        ...defaultFilter,
        ...singleFilter,
        ...multipleFilter,
    };
    $.ajax({
        url: urlData,
        method: 'POST',
        data: postData,
        success: function (response) {
            var i = response.from;
            var rows = '';
            $.each(response.data, function (index, item) {
                rows += '<tr class="">';
                rows += '<td><div class="checkbox selectSingle"><input type="checkbox" data-id="' + item.id + '" /><span class=""></span></div></td>';
                rows += '<td>' + i + '</td>';
                $.each(columns, function(k, v) { 
                    if(v == 'stock_id') {
                        rows += '<td class="stock-id">' + ((item[v] != null) ? item[v] : '-') + '</td>';
                    } else {
                        rows += '<td>' + ((item[v] != null) ? item[v] : '-') + '</td>';
                    }
                });
                rows += '</tr>';
                i++;
            });

            if (response.data.length == 0) {
                rows += '<tr class=""><td class="text-left" colspan="' + (columns.length + 2) + '">No Record Found</td></tr>';
            }

            $('#data-table tbody').html(rows);
            $(".summary-item.summary-line").eq(0).html('Total Stock <br>' + response.total_stock);
            $(".summary-item.summary-line").eq(1).html('Total Carat <br>' + Number(response.total_carat).toFixed(2));
            $(".summary-item.summary-line").eq(2).html('Total Amount <br>' + Number(response.total_amount).toFixed(2));

            $("#fromRec").html(response.from);
            $("#toRec").html(response.to);
            $("#totalRec").html(response.total);

            defaultFilter['totalPage'] = response.last_page;
            $(".changePage").val(response.current_page);
            $(".changePage").attr("max", response.last_page);
            $("#totalPage").html(response.last_page);

            if (defaultFilter['currentPage'] <= 1) {
                $(".previousPage").addClass("hide");
            }
            if (defaultFilter['currentPage'] >= defaultFilter['totalPage']) {
                $(".nextPage").addClass("hide");
            }
            if (defaultFilter['currentPage'] > 1) {
                $(".previousPage").removeClass("hide");
            }
            if (defaultFilter['currentPage'] < defaultFilter['totalPage']) {
                $(".nextPage").removeClass("hide");
            }
            $('#loader').hide();
        }
    });
}

function checkSingleSelect() {
    var $selectAllCheckbox = $(".selectAll").find('input[type="checkbox"]');
    var $selectAllCheckmark = $(".selectAll").find('span');
    var allChecked = true;

    $(".selectSingle").each(function () {
        var $checkbox = $(this).find('input[type="checkbox"]');

        if ($checkbox.is(':checked')) {
            allChecked = false;
            return false;
        }
    });

    if (allChecked) {
        $selectAllCheckbox.prop('checked', false);
        $selectAllCheckmark.removeClass("checkmark");
        $selectAllCheckmark.removeClass("minus");
    } else {
        $selectAllCheckbox.prop('checked', true);
        $selectAllCheckmark.addClass("checkmark");
        $selectAllCheckmark.addClass("minus");
    }
}

// Export data to CSV
function exportToCSV() {
    $('#loader').show();
    $.ajax({
        type: 'POST',
        url: exportCsv,
        data: postData,
        xhrFields: {
            responseType: 'blob'
        },
        success: function(blob) {
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = "export.csv";
            link.click();
            $("#downloadModal").modal('hide');
            $('#loader').hide();
        },
        error: function(response) {
            console.error("An error occurred while exporting the data.");
            console.error(xhr.responseText);
        }
    });
}

// Export data to Excel
function exportToExcel() {
    $('#loader').show();
    $.ajax({
        type: 'POST',
        url: urlExportXlsx,
        data: postData,
        xhrFields: {
            responseType: 'blob'
        },
        success: function(response) {
            var blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = 'export.xlsx';
            link.click();
            $("#downloadModal").modal('hide');
            $('#loader').hide();
        },
        error: function(xhr, status, error) {
            console.error("An error occurred while exporting the data.");
            console.error(xhr.responseText);
        }
    });
}
