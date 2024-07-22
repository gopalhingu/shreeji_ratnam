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
				<div class="summary-item summary-line">Total Stock <br> 3587</div>
				<div class="summary-item summary-line">Total Carat <br> 3677.05</div>
				<div class="summary-item summary-line">Total Amount <br> 663118.56</div>
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
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default hide sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="growth_type" data-order="asc">
							Type
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="stock_id" data-order="asc">
							Stock ID.
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="report_number" data-order="asc">
							Report #
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="lab" data-order="asc">
							Lab
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="shape" data-order="asc">
							Shape
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="ratio" data-order="asc">
							Carat
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="color" data-order="asc">
							Color
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="clarity" data-order="asc">
							Clarity
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="rap_amount" data-order="asc">
							Rap Rate
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="discounts" data-order="asc">
							Dis %
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="total_price" data-order="asc">
							$ Price
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="total_price" data-order="asc">
							$ Amount
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="cut" data-order="asc">
							Cut
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="polish" data-order="asc">
							Polish
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="symmetry" data-order="asc">
							Symmetry
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>
                        <th class="sorting" data-column="fluorescence_intensity" data-order="asc">
							Flo.
							<div class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
								<i class="fa-solid fa-sort default sorting_icon"></i>
								<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
								<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
							</div>
						</th>						
					</tr>
				</thead>
				<tbody>
					<tr class="">
						<td>
							<div class="checkbox">
								<input type="checkbox" />
								<span class=""></span>
							</div>
						</td>
						<td>1</td>
						<td>sbardwell0</td>
						<td>Sarajane</td>
						<td>Bardwell</td>
						<td>sbardwell</td>
						<td>+62 347</td>
						<td>Pagedangan</td>
						<td>2</td>
						<td>3</td>
						<td>4</td>
						<td>5</td>
						<td>6</td>
						<td>7</td>
						<td>8</td>
						<td>9</td>
						<td>10</td>
						<td>11</td>
					</tr>
					<tr class="selected">
						<td>
							<div class="checkbox">
								<input type="checkbox" checked />
								<span class="checkmark"></span>
							</div>
						</td>
						<td>1</td>
						<td>sbardwell0</td>
						<td>Sarajane</td>
						<td>Bardwell</td>
						<td>sbardwell</td>
						<td>+62 347</td>
						<td>Pagedangan</td>
						<td>2</td>
						<td>3</td>
						<td>4</td>
						<td>5</td>
						<td>6</td>
						<td>7</td>
						<td>8</td>
						<td>9</td>
						<td>10</td>
						<td>11</td>
					</tr>
				</tbody>
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
		<div class="modal-dialog modal-fullscreen">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Filter Option</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					Your content here...
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Clear Filter</button>
					<button type="button" class="btn btn-primary">Filter</button>
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

        $(document).ready(function() {
            
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
			$.ajax({
				url: '{{ route("diamond.data") }}',
				method: 'GET',
				data: {
					page: page,
					perPage: perPage,
					sortBy: sortBy,
					sortDirection: sortDirection
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