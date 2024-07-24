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
				</button>
				<button class="button contact-button"" title="Contact us" data-bs-toggle="modal" data-bs-target="#contactModal">
					<i class="fa-solid fa-phone-volume icon-size"></i>
				</button>
				<button class="button whatsapp-button" title="Whawhatsapp" data-bs-toggle="modal" data-bs-target="#whatsappModal" style="display:none">
					<i class="fa-solid fa-brands fa-whatsapp icon-size"></i>
				</button>
				<button class="button" title="Filter Data" data-bs-toggle="modal" data-bs-target="#filterModal">
					<i class="fa-solid fa-filter icon-size"></i>
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
					<div class="contactform" id="downloadCsv">
						<i class="fa-solid fa-file-csv" style="font-size: xx-large;"></i><span style="margin-left: 10px;">CSV</span>
					</div>
					<hr>
					<div class="contactform" id="downloadExcel">
						<i class="fa-solid fa-file-excel" style="font-size: xx-large;"></i><span style="margin-left: 10px;">EXCEL</span>
					</div>
					<hr>
					<div class="contactform" id="downloadBoth">
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
						<a href="https://api.whatsapp.com/send/?phone=%2B916352743508&text&type=phone_number&app_absent=0" target="_blank"> 
							<i class="fa-brands fa-whatsapp" style="margin-top: 5px; font-size: larger;"></i><span style="margin-left: 10px;">Lav meruliya</span>
						</a>
					</div>
					<hr>
					<div class="contactform">
						<a href="https://api.whatsapp.com/send/?phone=%2B916352743508&text&type=phone_number&app_absent=0" target="_blank">
							<i class="fa-brands fa-whatsapp" style="margin-top: 5px; "></i><span style="margin-left: 10px;">Vasant Narola</span>
						</a>
					</div>
					<hr>
					<div class="contactform">
						<a href="tel:+916352743508">
							<i class="fa-solid fa-phone-volume" style="margin-top: 5px;"></i><span style="margin-left: 10px;">Call:- Lav meruliya</span>
						</a>
					</div>
					<hr>
					<div class="contactform">
						<a href="tel:+916352743508">
							<i class="fa-solid fa-phone-volume" style="margin-top: 5px;"></i><span style="margin-left: 10px;">Call:- Vasant Narola</span>
						</a>
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
				<div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
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
							<div id="shapeList" class="border p-2 rounded focusable" tabindex="0" style="min-height: 50px;">
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
								<span id="clarityPlaceholderMessage" class="text-muted">Please choose one or more diamond clarity</span>
							</div>
						</div>
						<!-- Cut List Display -->
						<div class="col-md-12">
							<div id="cutList" class="border p-2 rounded" style="min-height: 50px;">
								<span id="cutPlaceholderMessage" class="text-muted">Please choose one or more diamond cut</span>
							</div>
						</div>

						<!-- Polish List Display -->
						<div class="col-md-12">
							<div id="polishList" class="border p-2 rounded" style="min-height: 50px;">
								<span id="polishPlaceholderMessage" class="text-muted">Please choose one or more diamond polish</span>
							</div>
						</div>

						<!-- Symmetry List Display -->
						<div class="col-md-12">
							<div id="symmetryList" class="border p-2 rounded" style="min-height: 50px;">
								<span id="symmetryPlaceholderMessage" class="text-muted">Please choose one or more diamond symmetry</span>
							</div>
						</div>

						<!-- Lab List Display -->
						<div class="col-md-12">
							<div id="labList" class="border p-2 rounded" style="min-height: 50px;">
								<span id="labPlaceholderMessage" class="text-muted">Please choose one or more diamond lab</span>
							</div>
						</div>

						<div class="col-md-12 position-relative">
							<input type="text" class="form-control" id="stockId" placeholder=" " />
							<label for="stockId" class="form-label label-on-border">Stock ID</label>
						</div>
						<div class="col-md-12 position-relative">
							<input type="text" class="form-control" id="reportNumber" placeholder=" " />
							<label for="reportNumber" class="form-label label-on-border">Report Number</label>
						</div>
						<div class="col-md-12 position-relative">
							<input type="text" class="form-control" id="type" placeholder=" " />
							<label for="type" class="form-label label-on-border">Type</label>
						</div>

					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary filter-clear-button" data-bs-dismiss="modal">Clear Filter</button>
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
					<div id="fullColorList">
						@foreach ($colors as $key=>$value)
							<div class="form-check me-3">
								<input class="form-check-input" type="checkbox" value="{{ $value }}" id="color{{ $key }}">
								<label class="form-check-label" for="color{{ $key }}">{{ $value }}</label>
							</div>
						@endforeach
					</div>
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
					<div id="fullClarityList">
						@foreach ($clarities as $key=>$value)
							<div class="form-check me-3">
								<input class="form-check-input" type="checkbox" value="{{ $value }}" id="clarity{{ $key }}">
								<label class="form-check-label" for="clarity{{ $key }}">{{ $value }}</label>
							</div>
						@endforeach
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary apply-clarity-filter">Apply Filter</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Cut Modal -->
	<div class="modal fade" id="cutModal" tabindex="-1" aria-labelledby="cutModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="cutModalLabel">Cut Filter</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<!-- Cut filter content goes here -->
					<div id="fullCutList">
						@foreach ($cuts as $key=>$value)
							<div class="form-check me-3">
								<input class="form-check-input" type="checkbox" value="{{ $value }}" id="cut{{ $key }}">
								<label class="form-check-label" for="cut{{ $key }}">{{ $value }}</label>
							</div>
						@endforeach
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary apply-cut-filter">Apply Filter</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Polish Modal -->
	<div class="modal fade" id="polishModal" tabindex="-1" aria-labelledby="polishModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="polishModalLabel">Polish Filter</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<!-- Polish filter content goes here -->
					<div id="fullPolishList">
						@foreach ($polish as $key=>$value)
							<div class="form-check me-3">
								<input class="form-check-input" type="checkbox" value="{{ $value }}" id="polish{{ $key }}">
								<label class="form-check-label" for="polish{{ $key }}">{{ $value }}</label>
							</div>
						@endforeach
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary apply-polish-filter">Apply Filter</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Symmetry Modal -->
	<div class="modal fade" id="symmetryModal" tabindex="-1" aria-labelledby="symmetryModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="symmetryModalLabel">Symmetry Filter</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<!-- Symmetry filter content goes here -->
					<div id="fullSymmetryList">
						@foreach ($symmetries as $key=>$value)
							<div class="form-check me-3">
								<input class="form-check-input" type="checkbox" value="{{ $value }}" id="symmetries{{ $key }}">
								<label class="form-check-label" for="symmetries{{ $key }}">{{ $value }}</label>
							</div>
						@endforeach
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary apply-symmetry-filter">Apply Filter</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="labModal" tabindex="-1" aria-labelledby="labModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="labModalLabel">Select Lab</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form id="labFilter" class="row g-3">
						<div id="fullLabList" class="col-md-12">
							@foreach ($labs as $key=>$value)
								<div class="form-check me-3">
									<input class="form-check-input" type="checkbox" value="{{ $value }}" id="lab{{ $key }}">
									<label class="form-check-label" for="lab{{ $key }}">{{ $value }}</label>
								</div>
							@endforeach
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary filter-clear-button" data-bs-dismiss="modal">Clear Filter</button>
					<button type="button" class="btn btn-primary apply-lab-filter">Apply Filter</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="whatsappModal" tabindex="-1" aria-labelledby="whatsappModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="whatsappModalLabel">WhatsApp Contacts</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="whatsappform">
						<i class="fa-brands fa-whatsapp" style="margin-top: 5px; font-size: larger;"></i><span style="margin-left: 10px;">Lav meruliya</span>
					</div>
					<hr>
					<div class="whatsappform">
						<i class="fa-brands fa-whatsapp" style="margin-top: 5px; font-size: larger;"></i><span style="margin-left: 10px;">Vasant Narola</span>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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

		// Utility function to clear all filters
		function clearFilters() {
			// Clear input fields
			$('#minCarat').val('');
			$('#maxCarat').val('');

			$('#stockId').val('');

			$('#reportNumber').val('');

			$('#type').val('');

			// Clear shape tags
			$('#shapeList').empty().html('<span id="placeholderMessage" class="text-muted">Please choose one or more diamond shape</span>');
			
			// Clear color tags
			$('#colorList').empty().html('<span id="colorPlaceholderMessage" class="text-muted">Please choose one or more diamond color</span>');
			
			// Clear clarity tags
			$('#clarityList').empty().html('<span id="clarityPlaceholderMessage" class="text-muted">Please choose one or more diamond clarity</span>');

			// Clear cut tags
			$('#cutList').empty().html('<span id="cutPlaceholderMessage" class="text-muted">Please choose one or more diamond cut</span>');

			// Clear polish tags
			$('#polishList').empty().html('<span id="polishPlaceholderMessage" class="text-muted">Please choose one or more diamond polish</span>');

			// Clear symmetry tags
			$('#symmetryList').empty().html('<span id="symmetryPlaceholderMessage" class="text-muted">Please choose one or more diamond symmetry</span>');

			// Uncheck all checkboxes in filter lists
			$('#fullShapeList input').prop('checked', false);
			$('#fullColorList input').prop('checked', false);
			$('#fullClarityList input').prop('checked', false);
			$('#fullCutList input').prop('checked', false);
			$('#fullPolishList input').prop('checked', false);
			$('#fullSymmetryList input').prop('checked', false);

			// Trigger filter with updated settings
			fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);
		}

		// Attach event listener to Clear Filter button
		$(document).on('click', '.filter-clear-button', function() {
			clearFilters();
		});

		function applyFilter(modalId, listId, fullListId, placeholderMessage,tagClass) {
			const selectedItems = $(fullListId + ' input:checked').map(function() {
				return $(this).val();
			}).get();

			// Clear existing items
			$(listId).empty();

			if (selectedItems.length === 0) {
				// Show placeholder message if no items are selected
				$(listId).html('<span id="' + placeholderMessage + '" class="text-muted">Please choose one or more tags</span>');
			} else {
				// Hide placeholder message if items are selected
				$('#' + placeholderMessage).remove();

				// Append each item as a tag
				$.each(selectedItems, function(index, item) {
					$(listId).append(
						'<span class="badge bg-primary me-2 mb-1">' + item +
						' <span class="' + tagClass + '" style="cursor:pointer;">&times;</span></span>'
					);
				});
			}

			// Trigger filter with the selected items
			fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);
			$(modalId).modal('hide');
		}

		function showModal(modalId) {
			$(modalId).modal('show');
		}

		function removeTag(tag, listId, fullListId, placeholderMessage) {
			var item = $(tag).closest('.badge').text().trim().slice(0, -2);
			$(tag).closest('.badge').remove();
			$(fullListId + ' input[value="' + item + '"]').prop('checked', false);

			// Show placeholder message if no tags are left
			if ($(listId + ' .badge').length === 0) {
				$(listId).html('<span id="' + placeholderMessage + '" class="text-muted">Please choose one or more tags</span>');
			}

			// Trigger filter with updated items
			fetchData(currentPage, currentPerPage, currentSortColumn, currentSortDirection);
		}

		$('#shapeList').on('click', function() { showModal('#shapeModal'); });
		$('#colorList').on('click', function() { showModal('#colorModal'); });
		$('#clarityList').on('click', function() { showModal('#clarityModal'); });
		$('#cutList').on('click', function() { showModal('#cutModal'); });
		$('#polishList').on('click', function() { showModal('#polishModal'); });
		$('#symmetryList').on('click', function() { showModal('#symmetryModal'); });
		$('#labList').on('click', function() { showModal('#labModal'); });

		$(document).on('click', '.apply-shape-filter', function() {
			applyFilter('#shapeModal', '#shapeList', '#fullShapeList', 'placeholderMessage', 'remove-tag');
		});

		$(document).on('click', '.apply-color-filter', function() {
			applyFilter('#colorModal', '#colorList', '#fullColorList', 'colorPlaceholderMessage', 'remove-color-tag');
		});

		$(document).on('click', '.apply-clarity-filter', function() {
			applyFilter('#clarityModal', '#clarityList', '#fullClarityList', 'clarityPlaceholderMessage', 'remove-clarity-tag');
		});

		$(document).on('click', '.apply-cut-filter', function() {
			applyFilter('#cutModal', '#cutList', '#fullCutList', 'cutPlaceholderMessage', 'remove-cut-tag');
		});

		$(document).on('click', '.apply-polish-filter', function() {
			applyFilter('#polishModal', '#polishList', '#fullPolishList', 'polishPlaceholderMessage', 'remove-polish-tag');
		});

		$(document).on('click', '.apply-symmetry-filter', function() {
			applyFilter('#symmetryModal', '#symmetryList', '#fullSymmetryList', 'symmetryPlaceholderMessage', 'remove-symmetry-tag');
		});

		$(document).on('click', '.apply-lab-filter', function() {
			applyFilter('#labModal', '#labList', '#fullLabList', 'labPlaceholderMessage', 'remove-lab-tag');
		});

		$(document).on('click', '.remove-tag', function() {
			removeTag(this, '#shapeList', '#fullShapeList', 'placeholderMessage');
		});

		// Remove color tag on click
		$(document).on('click', '.remove-color-tag', function() {
			removeTag(this, '#colorList', '#fullColorList', 'colorPlaceholderMessage');
		});

		// Remove clarity tag on click
		$(document).on('click', '.remove-clarity-tag', function() {
			removeTag(this, '#clarityList', '#fullClarityList', 'clarityPlaceholderMessage');
		});

		$(document).on('click', '.remove-cut-tag', function() {
			removeTag(this, '#cutList', '#fullCutList', 'cutPlaceholderMessage');
		});

		$(document).on('click', '.remove-polish-tag', function() {
			removeTag(this, '#polishList', '#fullPolishList', 'polishPlaceholderMessage');
		});

		$(document).on('click', '.remove-symmetry-tag', function() {
			removeTag(this, '#symmetryList', '#fullSymmetryList', 'symmetryPlaceholderMessage');
		});

		$(document).on('click', '.remove-lab-tag', function() {
			removeTag(this, '#labList', '#fullLabList', 'labPlaceholderMessage');
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

        $(document).ready(function() {

			$('.whatsappform').on('click', function() {
				// Get the contact name and phone number
				var contactName = $(this).text().trim();
				var phoneNumber = contactName === "Lav meruliya" ? "+917567364426" : "+919876543210";

				// Get selected stock IDs
				var selectedStockIDs = [];
				$('.selectSingle input[type="checkbox"]:checked').each(function() {
					var stockID = $(this).closest('tr').find('.stock-id').text();
					selectedStockIDs.push(stockID);
				});

				// Format the stock IDs into the message
				var stockIDText = selectedStockIDs.join('\n');

				const encodedText = encodeURIComponent(stockIDText);

				// Create the WhatsApp URL
				const whatsappURL = `https://api.whatsapp.com/send/?phone=%2B916352743508&text=I%20Want%20To%20Select%20This%20Stock%20IDs%3A%0A${encodedText}&type=phone_number&app_absent=0`;

				// Redirect to WhatsApp URL
				window.open(whatsappURL, '_blank');
			});

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

                clearFilters();
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
				toggleButtons();
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
				toggleButtons();
            });

			function toggleButtons() {
				const anyChecked = $('.selectSingle input:checked').length > 0 || $('.selectAll input:checked').length > 0;

				if (anyChecked) {
					$('.contact-button').hide();
					$('.whatsapp-button').show();
				} else {
					$('.contact-button').show();
					$('.whatsapp-button').hide();
				}
			}

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

			var stockId = $('#stockId').val();
			var reportNumber = $('#reportNumber').val();
			var type = $('#type').val();

			const selectedShapes = $('#shapeList .badge').map(function() {
				return $(this).text().trim().slice(0, -2);
			}).get();

			// Get selected colors
			const selectedColors = $('#colorList .badge').map(function() {
				return $(this).text().trim().slice(0, -2); // Extract the color text, removing the '×'
			}).get();

			// Get selected colors
			const selectedclarities = $('#clarityList .badge').map(function() {
				return $(this).text().trim().slice(0, -2); // Extract the color text, removing the '×'
			}).get();

			const selectedCuts = $('#cutList .badge').map(function() {
				return $(this).text().trim().slice(0, -2); // Extract the cut text, removing the '×'
			}).get();

			// Get selected polishes
			const selectedPolishes = $('#polishList .badge').map(function() {
				return $(this).text().trim().slice(0, -2); // Extract the polish text, removing the '×'
			}).get();

			// Get selected symmetries
			const selectedSymmetries = $('#symmetryList .badge').map(function() {
				return $(this).text().trim().slice(0, -2); // Extract the symmetry text, removing the '×'
			}).get();

			const selectedLabs = $('#labList .badge').map(function() {
				return $(this).text().trim().slice(0, -2);
			}).get();
			
			$.ajax({
				url: '{{ route("diamond.data") }}',
				method: 'POST',
				data: {
					page: page,
					perPage: perPage,
					sortBy: sortBy,
					sortDirection: sortDirection,
					minCarat: minCarat,
            		maxCarat: maxCarat,
					shapes: selectedShapes,
					colors: selectedColors,
					clarities: selectedclarities,
					cuts: selectedCuts,
					polishes: selectedPolishes,
					symmetries: selectedSymmetries,
					labs: selectedLabs,
					stockId,
					reportNumber,
					type
				},
				success: function(response) {
					var rows = '';
					$.each(response.data, function(index, item) {
						rows += '<tr class="">';
						rows += '<td><div class="checkbox selectSingle"><input type="checkbox" data-id="' + item.id + '" /><span class=""></span></div></td>';
						rows += '<td>' + item.id + '</td>';
						rows += '<td>' + item.growth_type + '</td>';
						rows += '<td class="stock-id">' + item.stock_id + '</td>';
						rows += '<td>' + item.report_number + '</td>';
						rows += '<td>' + item.lab + '</td>';
						rows += '<td>' + item.shape + '</td>';
						rows += '<td>' + item.weight + '</td>';
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


		// Fetch data for export
		function fetchDataForExport() {
			var minCarat = $('#minCarat').val() || 0;
			var maxCarat = $('#maxCarat').val() || 0;
			var stockId = $('#stockId').val();
			var reportNumber = $('#reportNumber').val();
			var type = $('#type').val();

			const selectedShapes = $('#shapeList .badge').map(function() {
				return $(this).text().trim().slice(0, -2);
			}).get();

			const selectedColors = $('#colorList .badge').map(function() {
				return $(this).text().trim().slice(0, -2);
			}).get();

			const selectedClarity = $('#clarityList .badge').map(function() {
				return $(this).text().trim().slice(0, -2);
			}).get();

			const selectedCuts = $('#cutList .badge').map(function() {
				return $(this).text().trim().slice(0, -2);
			}).get();

			const selectedPolishes = $('#polishList .badge').map(function() {
				return $(this).text().trim().slice(0, -2);
			}).get();

			const selectedSymmetries = $('#symmetryList .badge').map(function() {
				return $(this).text().trim().slice(0, -2);
			}).get();

			const selectedLabs = $('#labList .badge').map(function() {
				return $(this).text().trim().slice(0, -2);
			}).get();
			
			return $.ajax({
				url: '{{ route("diamond.data") }}',
				method: 'POST',
				data: {
					page: 1, // Page number for export can be set to 1 as we're exporting all data
					perPage: 1084, // Large number to ensure all data is retrieved
					sortBy: currentSortColumn,
					sortDirection: currentSortDirection,
					minCarat: minCarat,
					maxCarat: maxCarat,
					shapes: selectedShapes,
					colors: selectedColors,
					clarities: selectedClarity,
					cuts: selectedCuts,
					polishes: selectedPolishes,
					symmetries: selectedSymmetries,
					labs: selectedLabs,
					stockId,
					reportNumber,
					type
				},
			});
		}

		// Export data to CSV
		function exportToCSV() {
			fetchDataForExport().done(function(response) {
				let csvContent = "data:text/csv;charset=utf-8,";
				const headers = "ID, Growth Type, Stock ID, Report Number, Lab, Shape, Weight, Color, Clarity, Rap Amount, Discounts, Total Price, Cut, Polish, Symmetry, Fluorescence Intensity\n";
				csvContent += headers;

				response.data.forEach(item => {
					const row = [
						item.id,
						item.growth_type,
						item.stock_id,
						item.report_number,
						item.lab,
						item.shape,
						item.weight,
						item.color,
						item.clarity,
						item.rap_amount,
						item.discounts,
						item.total_price,
						item.cut,
						item.polish,
						item.symmetry,
						item.fluorescence_intensity
					].join(",");
					csvContent += row + "\n";
				});

				const encodedUri = encodeURI(csvContent);
				const link = document.createElement("a");
				link.setAttribute("href", encodedUri);
				link.setAttribute("download", "data.csv");
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			});
		}

		// Export data to Excel
		function exportToExcel() {
			fetchDataForExport().done(function(response) {
				const workbook = XLSX.utils.book_new();
				const ws_data = [
					["ID", "Growth Type", "Stock ID", "Report Number", "Lab", "Shape", "Weight", "Color", "Clarity", "Rap Amount", "Discounts", "Total Price", "Cut", "Polish", "Symmetry", "Fluorescence Intensity"]
				];

				response.data.forEach(item => {
					ws_data.push([
						item.id,
						item.growth_type,
						item.stock_id,
						item.report_number,
						item.lab,
						item.shape,
						item.weight,
						item.color,
						item.clarity,
						item.rap_amount,
						item.discounts,
						item.total_price,
						item.cut,
						item.polish,
						item.symmetry,
						item.fluorescence_intensity
					]);
				});

				const ws = XLSX.utils.aoa_to_sheet(ws_data);
				XLSX.utils.book_append_sheet(workbook, ws, "Sheet1");

				XLSX.writeFile(workbook, "data.xlsx");
			});
		}

    </script>
@endsection