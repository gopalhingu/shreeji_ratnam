@extends("layout.main")

@section("css")
    <link rel="stylesheet" href="{{ url('css/style.css') }}">
@endsection

@section("content")

<div>
	<div class="content">
		<section class="table-header grid">
			<div class="action-buttons">
				<button class="button reloadPage" title="Reload Page">
					<i class="fa-solid fa-rotate-right icon-size"></i>
					<!-- <span>Download Options</span> -->
				</button>
				<select class="form-select changeNumberOfPerPage" aria-label="Default select example">
					<option value="10" selected>10</option>
					<option value="20">20</option>
					<option value="30">30</option>
					<option value="40">40</option>
					<option value="50">50</option>
				</select>
			</div>
			<div class="action-button">
				<button class="button" title="Download Options" data-bs-toggle="modal" data-bs-target="#downloadModal">
					<i class="fa-solid fa-download icon-size"></i>
					<!-- <span>Download Options</span> -->
				</button>
				<button class="button" title="Contact us" data-bs-toggle="modal" data-bs-target="#contactModal">
					<i class="fa-solid fa-phone-volume icon-size"></i>
					<!-- <span>Contact us</span> -->
				</button>
				<button class="button" title="Filter Data" data-bs-toggle="modal" data-bs-target="#filterModal">
					<i class="fa-solid fa-filter icon-size"></i>
					<!-- <span>Filter Data</span> -->
				</button>
			</div>
		</section>
		<section>
			<div class="summary">
				<div class="summary-item summary-line summary-total-stock"></div>
				<div class="summary-item summary-line summary-total-carat"></div>
				<div class="summary-item summary-line summary-total-amount"></div>
				<div class="summary-items">
					<span>
					<a href="mailto:skdiamond9805@gmail.com">Email: skdiamond9805@gmail.com</a><br>
					<a href="tel:+916352743508">Call: +916352743508</a>
					</span>
				</div>
			</div>
		</section>
		<div class="card">
			<table id="data-table">
				<thead>
					<tr>
						<th>
							<div class="checkbox selectAll">
								<input type="checkbox" />
								<span class=""></span>
							</div>
						</th>
						<th class="sorting" data-column="id" data-order="asc">
							No.
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default hide sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="growth_type" data-order="asc">
							Type
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="stock_id" data-order="asc">
							Stock ID.
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="report_number" data-order="asc">
							Report #
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="lab" data-order="asc">
							Lab
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="shape" data-order="asc">
							Shape
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="ratio" data-order="asc">
							Carat
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="color" data-order="asc">
							Color
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="clarity" data-order="asc">
							Clarity
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="rap_amount" data-order="asc">
							Rap Rate
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="discounts" data-order="asc">
							Dis %
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="total_price" data-order="asc">
							$ Price
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="total_price" data-order="asc">
							$ Amount
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="cut" data-order="asc">
							Cut
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="polish" data-order="asc">
							Polish
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="symmetry" data-order="asc">
							Symmetry
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>
                        <th class="sorting" data-column="fluorescence_intensity" data-order="asc">
							Flo.
							<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</span>
						</th>						
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		<section class="table-footer grid">
			<span>Displaying <span id="fromRec">1</span> - <span id="toRec">10</span> of <span id="totalRec">00</span></span>
			<div class="paging grid">
				<span>Page <input type="number" class="changePage" min="1" max="" value="1" onkeypress="return /\d/i.test(event.key)" /> of <span id="totalPage">1</span></span>
				<div class="button icon previousPage">
					<i class="fa-solid fa-angle-left"></i>
				</div>
				<div class="button icon nextPage">
					<i class="fa-solid fa-angle-right"></i>
				</div>
			</div>
		</section>
	</div>

	<!-- download Modal -->
	<div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">List Download Format</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="contactform">
						<i class="fa-solid fa-file-csv" style="font-size: xx-large;"></i><span style="margin-left: 10px;">CSV</span>
					</div>
					<hr>
					<div class="contactform">
						<i class="fa-solid fa-file-excel" style="font-size: xx-large;"></i><span style="margin-left: 10px;">EXCEL</span>
					</div>
					<hr>
					<div class="contactform">
						<i class="fa-solid fa-file-csv" style="font-size: xx-large;"></i> <span style="margin-left: 10px;">& </span> <i class="fa-solid fa-file-excel" style="font-size: xx-large; margin-left: 10px;"></i><span style="margin-left: 10px;">Both</span>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>

	<!-- contact Modal -->
	<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Contact us</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="contactform">
						<i class="fa-brands fa-whatsapp" style="margin-top: 5px; font-size: larger;"></i><span style="margin-left: 10px;">Lav meruliya</span>
					</div>
					<hr>
					<div class="contactform">
						<i class="fa-brands fa-whatsapp" style="margin-top: 5px; "></i><span style="margin-left: 10px;">Vasant Narola</span>
					</div>
					<hr>
					<div class="contactform">
						<i class="fa-solid fa-phone-volume" style="margin-top: 5px;"></i><span style="margin-left: 10px;">Call:- Lav meruliya</span>
					</div>
					<hr>
					<div class="contactform">
						<i class="fa-solid fa-phone-volume" style="margin-top: 5px;"></i><span style="margin-left: 10px;">Call:- Vasant Narola</span>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>

	<!-- filter Modal -->
	<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Filter Option</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
            <form id="caratFilter" class="row g-3">
                <div class="col-md-6 position-relative">
                    <input type="text" class="form-control" id="minCarat" placeholder=" " />
                    <label for="minCarat" class="form-label label-on-border">Min Carat</label>
                </div>
                <div class="col-md-6 position-relative">
                    <input type="text" class="form-control" id="maxCarat" placeholder=" " />
                    <label for="maxCarat" class="form-label label-on-border">Max Carat</label>
                </div>
				<div class="col-md-12">
					<div id="shapeList" class="border p-2 rounded" style="min-height: 50px;">
						<span id="placeholderMessage" class="text-muted">Please choose one or more diamond shape</span>
					</div>
				</div>
				<!-- Color List Display -->
				<div class="col-md-12">
					<div id="colorList" class="border p-2 rounded" style="min-height: 50px;">
						<span id="colorPlaceholderMessage" class="text-muted">Please choose one or more diamond color</span>
					</div>
				</div>
				<!-- Clarity List Display -->
				<div class="col-md-12">
					<div id="clarityList" class="border p-2 rounded" style="min-height: 50px;">
						<span id="clarityPlaceholderMessage" class="text-muted">Please choose one or more tags</span>
					</div>
				</div>
            </form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Clear Filter</button>
					<button type="button" class="btn btn-primary filter-button">Filter</button>
				</div>
			</div>
		</div>
	</div>
	
	<!--Shape filter Modal -->
	<div class="modal fade" id="shapeModal" tabindex="-1" aria-labelledby="shapeModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="shapeModalLabel">Select Diamond Shapes</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div id="fullShapeList">
						@foreach ($shapes as $key=>$value)
							<div class="form-check me-3">
								<input class="form-check-input" type="checkbox" value="{{ $value }}" id="shape{{ $key }}">
								<label class="form-check-label" for="shape{{ $key }}">{{ $value }}</label>
							</div>
						@endforeach
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary apply-shape-filter">Apply</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Color Modal -->
	<div class="modal fade" id="colorModal" tabindex="-1" aria-labelledby="colorModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="colorModalLabel">Select Diamond Colors</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					{{-- <div id="fullColorList" class="row"></div> --}}
					@foreach ($colors as $key=>$value)
						<div class="form-check me-3">
							<input class="form-check-input" type="checkbox" value="{{ $value }}" id="shape{{ $key }}">
							<label class="form-check-label" for="shape{{ $key }}">{{ $value }}</label>
						</div>
					@endforeach

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary apply-color-filter">Apply Filter</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Clarity Modal -->
	<div class="modal fade" id="clarityModal" tabindex="-1" aria-labelledby="clarityModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="clarityModalLabel">Select Diamond Clarity</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					@foreach ($clarities as $key=>$value)
						<div class="form-check me-3">
							<input class="form-check-input" type="checkbox" value="{{ $value }}" id="shape{{ $key }}">
							<label class="form-check-label" for="shape{{ $key }}">{{ $value }}</label>
						</div>
					@endforeach
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary apply-clarity-filter">Apply Filter</button>
				</div>
			</div>
		</div>
	</div>
	
</div>
@endsection

@section("script")
    <script>
		let currentPage = 1;
		let currentPerPage = 10;
		let currentSortColumn = 'id';
		let currentSortDirection = 'asc';
		let totalPage = 0;

		$('#shapeList').on('click', function() {
			$('#shapeModal').modal('show');
		});

		$(document).on('click', '.apply-shape-filter', function() {
			const selectedShapes = $('#fullShapeList input:checked').map(function() {
				return $(this).val();
			}).get();

			// Clear existing shapes
			$('#shapeList').empty();

			if (selectedShapes.length === 0) {
				// Show placeholder message if no shapes are selected
				$('#shapeList').html('<span id="placeholderMessage" class="text-muted">Please choose one or more tags</span>');
			} else {
				// Hide placeholder message if shapes are selected
				$('#placeholderMessage').remove();

				// Append each shape as a tag
				$.each(selectedShapes, function(index, shape) {
					$('#shapeList').append(
						'<span class="badge bg-primary me-2 mb-1">' + shape + 
						' <span class="remove-tag" style="cursor:pointer;">&times;</span></span>'
					);
				});
			}

			// Trigger filter with the selected shapes
			fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);

			$('#shapeModal').modal('hide');
		});

		$(document).on('click', '.remove-tag', function() {
			$(this).closest('.badge').remove();

			// Show placeholder message if no tags are left
			if ($('#shapeList .badge').length === 0) {
				$('#shapeList').html('<span id="placeholderMessage" class="text-muted">Please choose one or more tags</span>');
			}

			// Trigger filter with updated shapes
			fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);
		});

		//Color filter js
		$(document).on('click', '#colorList', function() {
			// $.ajax({
			// 	url: '{{ route("diamond.colors") }}',
			// 	method: 'GET',
			// 	success: function(colors) {
			// 		var colorHtml = '';
			// 		var selectedColors = $('#colorList .badge').map(function() {
			// 			return $(this).text().trim().slice(0, -2); // Extract text without the '×' character
			// 		}).get();

			// 		$.each(colors, function(index, color) {
			// 			var checked = selectedColors.includes(color) ? 'checked' : '';
			// 			colorHtml += '<div class="form-check me-3">';
			// 			colorHtml += '<input class="form-check-input" type="checkbox" value="' + color + '" id="color' + index + '" ' + checked + '>';
			// 			colorHtml += '<label class="form-check-label" for="color' + index + '">' + color + '</label>';
			// 			colorHtml += '</div>';
			// 		});
			// 		$('#fullColorList').html(colorHtml);
			// 	}
			// });
			$('#colorModal').modal('show');
		});

		$(document).on('click', '.apply-color-filter', function() {
			const selectedColors = $('#fullColorList input:checked').map(function() {
				return $(this).val();
			}).get();

			// Clear existing colors
			$('#colorList').empty();

			if (selectedColors.length === 0) {
				// Show placeholder message if no colors are selected
				$('#colorList').html('<span id="colorPlaceholderMessage" class="text-muted">Please choose one or more tags</span>');
			} else {
				// Hide placeholder message if colors are selected
				$('#colorPlaceholderMessage').remove();

				// Append each color as a tag
				$.each(selectedColors, function(index, color) {
					$('#colorList').append(
						'<span class="badge bg-primary me-2 mb-1">' + color + 
						' <span class="remove-color-tag" style="cursor:pointer;">&times;</span></span>'
					);
				});
			}

			// Trigger filter with the selected colors
			fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);

			$('#colorModal').modal('hide');
		});

		// Remove color tag on click
		$(document).on('click', '.remove-color-tag', function() {
			$(this).closest('.badge').remove();

			// Show placeholder message if no tags are left
			if ($('#colorList .badge').length === 0) {
				$('#colorList').html('<span id="colorPlaceholderMessage" class="text-muted">Please choose one or more tags</span>');
			}

			// Trigger filter with updated colors
			fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);
		});

		//Clarity filter js
		$(document).on('click', '#clarityList', function() {
			$('#clarityModal').modal('show');
		});

		$(document).on('click', '.apply-clarity-filter', function() {
			const selectedClarities = $('#fullClarityList input:checked').map(function() {
				return $(this).val();
			}).get();

			// Clear existing clarities
			$('#clarityList').empty();

			if (selectedClarities.length === 0) {
				// Show placeholder message if no clarities are selected
				$('#clarityList').html('<span id="clarityPlaceholderMessage" class="text-muted">Please choose one or more tags</span>');
			} else {
				// Hide placeholder message if clarities are selected
				$('#clarityPlaceholderMessage').remove();

				// Append each clarity as a tag
				$.each(selectedClarities, function(index, clarity) {
					$('#clarityList').append(
						'<span class="badge bg-primary me-2 mb-1">' + clarity + 
						' <span class="remove-clarity-tag" style="cursor:pointer;">&times;</span></span>'
					);
				});
			}

			// Trigger filter with the selected clarities
			fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);

			$('#clarityModal').modal('hide');
		});

		// Remove clarity tag on click
		$(document).on('click', '.remove-clarity-tag', function() {
			$(this).closest('.badge').remove();

			// Show placeholder message if no tags are left
			if ($('#clarityList .badge').length === 0) {
				$('#clarityList').html('<span id="clarityPlaceholderMessage" class="text-muted">Please choose one or more tags</span>');
			}

			// Trigger filter with updated clarities
			fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);
		});

        $(document).ready(function() {

          $(document).on('click', '.filter-button', function(e) {
			e.preventDefault();
			currentPage = 1; // Reset to first page on filter
			fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);
			$('#filterModal').modal('hide');
          });
            
			// Initial data fetch
            fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);

			$(".previousPage").addClass("hide");

            $(document).on('click', '.sorting', function(e) {
                e.preventDefault();

                currentSortColumn = $(this).data('column');
                currentSortDirection = ($(this).data("order") == 'asc') ? 'desc' : 'asc';

				$(this).data("order", currentSortDirection);
				$(this).find(".sorting_icon").addClass("hide");

				if(currentSortDirection == 'asc') {
					$(this).find(".ascending").removeClass("hide");
				}
				if(currentSortDirection == 'desc') {
					$(this).find(".decending").removeClass("hide");
				}

                fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);
            });

			$(document).on('click', '.reloadPage', function(e) {
                e.preventDefault();

                currentPage = 1;
				currentPerPage = 10;
				currentSortColumn = 'id';
				currentSortDirection = 'asc';

				$(".changeNumberOfPerPage").val(currentPerPage);
				$(".sorting").find(".sorting_icon").addClass("hide");
				$(".sorting").find(".default").removeClass("hide");

                fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);
            });
            
			$(document).on('change', '.changeNumberOfPerPage', function(e) {
                e.preventDefault();

				currentPage = 1;
                currentPerPage = $(this).val();
                fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);
            });

			$(document).on('click', '.selectAll', function(e) {
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
            });

			$(document).on('click', '.selectSingle', function(e) {
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
            });

			$(document).on('click', '.previousPage', function(e) {
                e.preventDefault();

				currentPage--;
				fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);

				if(currentPage <= 1) {
					$(this).addClass("hide");
				}
				if(currentPage < totalPage) {
					$(".nextPage").removeClass("hide");
				}
            });

			$(document).on('click', '.nextPage', function(e) {
                e.preventDefault();

				currentPage++;
				fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);

				if(currentPage > 1) {
					$(".previousPage").removeClass("hide");
				}
				if(currentPage >= totalPage) {
					$(this).addClass("hide");
				}
            });

			$(document).on('change', '.changePage', function(e) {
                e.preventDefault();

                currentPage = $(this).val();
                fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);

				if(currentPage <= 1) {
					$(".previousPage").addClass("hide");
				}
				if(currentPage >= totalPage) {
					$(".nextPage").addClass("hide");
				}
				if(currentPage > 1) {
					$(".previousPage").removeClass("hide");
				}
				if(currentPage < totalPage) {
					$(".nextPage").removeClass("hide");
				}
            });

        });

		function fetchData(page, perPage, sortBy, sortDirection) {
			var minCarat = $('#minCarat').val();
			var maxCarat = $('#maxCarat').val();

			if(!minCarat) {
				minCarat = 0;
			}

			if(!maxCarat) {
				maxCarat = 0;
			}

			const selectedShapes = $('#shapeList .badge').map(function() {
				return $(this).text().trim().slice(0, -2);
			}).get();

			 // Get selected colors
			 const selectedColors = $('#colorList .badge').map(function() {
				return $(this).text().trim().slice(0, -2); // Extract the color text, removing the '×'
			}).get();

			$.ajax({
				url: '{{ route("diamond.data") }}',
				method: 'GET',
				data: {
					page: page,
					perPage: perPage,
					sortBy: sortBy,
					sortDirection: sortDirection,
					minCarat: minCarat,
            		maxCarat: maxCarat,
					shapes: selectedShapes,
					colors: selectedColors
				},
				success: function(response) {
					var rows = '';
					$.each(response.data, function(index, item) {
						rows += '<tr class="">';
						rows += '<td><div class="checkbox selectSingle"><input type="checkbox" data-id="' + item.id + '" /><span class=""></span></div></td>';
						rows += '<td>' + item.id + '</td>';
						rows += '<td>' + item.growth_type + '</td>';
						rows += '<td>' + item.stock_id + '</td>';
						rows += '<td>' + item.report_number + '</td>';
						rows += '<td>' + item.lab + '</td>';
						rows += '<td>' + item.shape + '</td>';
						rows += '<td>' + item.ratio + '</td>';
						rows += '<td>' + item.color + '</td>';
						rows += '<td>' + item.clarity + '</td>';
						rows += '<td>' + item.rap_amount + '</td>';
						rows += '<td>' + item.discounts + '</td>';
						rows += '<td>' + item.total_price + '</td>';
						rows += '<td>' + item.total_price + '</td>';
						rows += '<td>' + item.cut + '</td>';
						rows += '<td>' + item.polish + '</td>';
						rows += '<td>' + item.symmetry + '</td>';
						rows += '<td>' + item.fluorescence_intensity + '</td>';
						rows += '</tr>';
					});

					$('#data-table tbody').html(rows);
					$(".summary-item.summary-line").eq(0).html('Total Stock <br>' + response.total_stock);
					$(".summary-item.summary-line").eq(1).html('Total Carat <br>' + Number(response.total_carat).toFixed(2));
					$(".summary-item.summary-line").eq(2).html('Total Amount <br>' + Number(response.total_amount).toFixed(2));

					$("#fromRec").html(response.from);
					$("#toRec").html(response.to);
					$("#totalRec").html(response.total);

					totalPage = response.last_page;
					$(".changePage").val(response.current_page);
					$(".changePage").attr("max", response.last_page);
					$("#totalPage").html(response.last_page);
				}
			});
		}

		function checkSingleSelect() {
			var $selectAllCheckbox = $(".selectAll").find('input[type="checkbox"]');
			var $selectAllCheckmark = $(".selectAll").find('span');
			var allChecked = true;

			$(".selectSingle").each(function() {
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

    </script>
@endsection