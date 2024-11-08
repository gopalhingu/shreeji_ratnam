@extends("layout.main")

@section("css")
    <link rel="stylesheet" href="{{ url('public/css/style.css') }}?t={{ date('ymd') }}">
	<style>
		.update-data {
			width: auto;
		}
	</style>
@endsection

@section("content")

@php
	$columns = array_keys($columnWithValue);
	// array_shift($columns);
	array_pop($columns);
	array_pop($columns);
	$columns = json_encode($columns);
	$claritiesFinal = ['IF', 'VVS1', 'VVS2', 'VS1', 'VS2', 'SI1', 'SI2', 'SI3', 'I1', 'I2', 'I3', 'I4'];
	$claritiesArray = $clarities->toArray();
@endphp
<div>
	<div class="content">
		<section class="table-header grid">
			<div class="action-buttons">
				<button class="button reloadPage" title="Reload Page" data-toggle="tooltip" data-placement="top">
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
				<button class="button" title="Download Options" data-bs-toggle="modal" data-bs-target="#downloadModal" data-toggle="tooltip" data-placement="top">
					<i class="fa-solid fa-download icon-size"></i>
				</button>
				<button class="button contact-button" title="Contact us" data-bs-toggle="modal" data-bs-target="#contactModal" data-toggle="tooltip" data-placement="top">
					<i class="fa-solid fa-phone-volume icon-size"></i>
				</button>
				<button class="button whatsapp-button" title="Whawhatsapp" data-bs-toggle="modal" data-bs-target="#whatsappModal" style="display:none" data-toggle="tooltip" data-placement="top">
					<i class="fa-solid fa-brands fa-whatsapp icon-size"></i>
				</button>
				<button class="button" title="Filter Data" data-bs-toggle="modal" data-bs-target="#filterModal" data-toggle="tooltip" data-placement="top">
					<i class="fa-solid fa-filter icon-size"></i>
				</button>
			</div>
		</section>
		<section>
			<div class="summary">
				<div class="summary-item summary-line summary-total-stock"></div>
				@if (!Auth::user())
					<div class="summary-items"></div>
					<div class="summary-items summary-line"></div>
				@endif
				<div class="summary-item summary-line summary-total-carat" style="display: {{ Auth::user() ? 'block' : 'none' }}"></div>
				<div class="summary-item summary-line summary-total-amount" style="display: {{ Auth::user() ? 'block' : 'none' }}"></div>
				<div class="summary-items">
					<span>
					<a href="mailto:shreejiratnam@yahoo.com">Email: shreejiratnam@yahoo.com</a><br>
					<a href="tel:+919662868968">Call: +919662868968</a> </br>
					<a href="tel:+918238615800">Call: +918238615800</a>
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
						@php
							$exception = ['created_at', 'updated_at'];
						@endphp
						@foreach ($columnWithValue as $key => $value)
							@if (in_array($key, $exception))
								@php
									continue;
								@endphp
							@endif
							@if ($key == "id")
								<th class="sorting-hide" data-column="{{ $key }}" data-order="asc">
									No.
									<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
										{{-- <i class="fa-solid fa-sort default hide sorting_icon"></i> --}}
										{{-- <i class="fa-solid fa-sort-up ascending sorting_icon"></i> --}}
										{{-- <i class="fa-solid fa-sort-down decending hide sorting_icon"></i> --}}
									</span>
								</th>
							@elseif($key == "stock_id")
								<th class="sorting" data-column="{{ $key }}" data-order="asc">
									{{ $value }}
									<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
										<i class="fa-solid fa-sort default sorting_icon"></i>
										<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
										<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
									</span>
								</th>
							@else
								<th class="sorting" data-column="{{ $key }}" data-order="asc">
									{{ $value }}
									<span class="sort-icons" style="margin-left: 5px; margin-top: 2px;">
										<i class="fa-solid fa-sort default sorting_icon"></i>
										<i class="fa-solid fa-sort-up ascending hide sorting_icon"></i>
										<i class="fa-solid fa-sort-down decending hide sorting_icon"></i>
									</span>
								</th>
							@endif
						@endforeach
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
					<div class="download_wrapper">
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
					<a href="https://api.whatsapp.com/send/?phone=%2B919662868968&text&type=phone_number&app_absent=0" target="_blank">
						<i class="fa-solid fa-brands fa-whatsapp" style="margin-top: 5px;"></i>
						<div class="contact-info">
							<span class="contact-name">Shailesh Narola</span>
							<span class="contact-number">+91 9662868968</span>
						</div>
					</a>
					</div>
					<hr>
					<div class="contactform">
						<a href="https://api.whatsapp.com/send/?phone=%2B918238615800&text&type=phone_number&app_absent=0" target="_blank">
							<i class="fa-solid fa-brands fa-whatsapp" ></i>
							<div class="contact-info">
								<span class="contact-name">Kaushal Narola</span>
								<span class="contact-number">+91 8238615800</span>
							</div>
						</a>
					</div>
					<hr>
					<div class="contactform">
						<a href="https://api.whatsapp.com/send/?phone=%2B919727408511&text&type=phone_number&app_absent=0" target="_blank">
							<i class="fa-solid fa-brands fa-whatsapp" ></i>
							<div class="contact-info">
								<span class="contact-name">Drashti Narola</span>
								<span class="contact-number">+91 9727408511</span>
							</div>
						</a>
					</div>
					<hr>
					<div class="contactform">
						<a href="tel:+919662868968">
							<i class="fa-solid fa-phone-volume" ></i>
							<div class="contact-info">
								<span class="contact-name">Shailesh Narola</span>
								<span class="contact-number">Call:- +91 9662868968</span>
							</div>
						</a>
					</div>
					<hr>
					<div class="contactform">
						<a href="tel:+918238615800">
							<i class="fa-solid fa-phone-volume" ></i>
							<div class="contact-info">
								<span class="contact-name">Kaushal Narola</span>
								<span class="contact-number">Call:- +91 8238615800</span>
							</div>
						</a>
					</div>
					<hr>
					<div class="contactform">
						<a href="tel:+9727408511">
							<i class="fa-solid fa-phone-volume" ></i>
							<div class="contact-info">
								<span class="contact-name">Drashti Narola</span>
								<span class="contact-number">Call:- +91 9727408511</span>
							</div>
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
						<!-- Min Carat Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="minCarat" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="minCarat">Min Carat</label>
						</div>
						<!-- Max Carat Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="maxCarat" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="maxCarat">Max Carat</label>
						</div>

						<!-- Status List Display -->
						<div class="col-md-6">
							<div id="statusList" class="border p-2 rounded focusable" tabindex="0" style="min-height: 50px;">
								<span class="text-muted"><b>Status</b></span><br>
								<span class="text-muted">Please choose one or more</span>
							</div>
						</div>
						<!-- Location List Display -->
						<div class="col-md-6">
							<div id="locationList" class="border p-2 rounded focusable" tabindex="0" style="min-height: 50px;">
								<span class="text-muted"><b>Location</b></span><br>
								<span class="text-muted">Please choose one or more</span>
							</div>
						</div>
						<!-- Shape List Display -->
						<div class="col-md-6">
							<div id="shapeList" class="border p-2 rounded" tabindex="0" style="min-height: 50px;">
								<span class="text-muted"><b>Shape</b></span><br>
								<span class="text-muted">Please choose one or more</span>
							</div>
						</div>
						<!-- Color List Display -->
						<div class="col-md-6">
							<div id="colorList" class="border p-2 rounded" style="min-height: 50px;">
								<span class="text-muted"><b>Color</b></span><br>
								<span class="text-muted">Please choose one or more</span>
							</div>
						</div>
						<!-- Clarity List Display -->
						<div class="col-md-6">
							<div id="clarityList" class="border p-2 rounded" style="min-height: 50px;">
								<span class="text-muted"><b>Clarity</b></span><br>
								<span class="text-muted">Please choose one or more</span>
							</div>
						</div>
						<!-- Cut List Display -->
						<div class="col-md-6">
							<div id="cutList" class="border p-2 rounded" style="min-height: 50px;">
								<span class="text-muted"><b>Cut</b></span><br>
								<span class="text-muted">Please choose one or more</span>
							</div>
						</div>
						<!-- Polish List Display -->
						<div class="col-md-6">
							<div id="polishList" class="border p-2 rounded" style="min-height: 50px;">
								<span class="text-muted"><b>Polish</b></span><br>
								<span class="text-muted">Please choose one or more</span>
							</div>
						</div>
						<!-- Symmetry List Display -->
						<div class="col-md-6">
							<div id="symmetryList" class="border p-2 rounded" style="min-height: 50px;">
								<span class="text-muted"><b>Symmetry</b></span><br>
								<span class="text-muted">Please choose one or more</span>
							</div>
						</div>
						<!-- Lab List Display -->
						<div class="{{ isset(Auth::user()->id) ? 'col-md-6' : 'col-md-12' }}">
							<div id="labList" class="border p-2 rounded" style="min-height: 50px;">
								<span class="text-muted"><b>Lab</b></span><br>
								<span class="text-muted">Please choose one or more</span>
							</div>
						</div>
						@if (isset(Auth::user()->id))
							<!-- Reference Filter -->
							<div class="col-md-6">
								<div id="referenceList" class="border p-2 rounded" style="min-height: 50px;">
									<span class="text-muted"><b>Reference</b></span><br>
									<span class="text-muted">Please choose one or more</span>
								</div>
							</div>
						@endif
						<!-- Min Length Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="minLength" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="minLength">Min Length</label>
						</div>
						<!-- Max Length Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="maxLength" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="maxLength">Max Length</label>
						</div>
						<!-- Min Width Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="minWidth" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="minWidth">Min Width</label>
						</div>
						<!-- Max Width Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="maxWidth" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="maxWidth">Max Width</label>
						</div>
						<!-- Min Height Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="minHeight" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="minHeight">Min Height</label>
						</div>
						<!-- Max Height Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="maxHeight" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="maxHeight">Max Height</label>
						</div>
						<!-- Min Depth Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="minDepth" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="minDepth">Min Depth</label>
						</div>
						<!-- Max Depth Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="maxDepth" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="maxDepth">Max Depth</label>
						</div>
						<!-- Min Ratio Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="minRatio" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="minRatio">Min Ratio</label>
						</div>
						<!-- Max Ratio Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="maxRatio" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="maxRatio">Max Ratio</label>
						</div>
						<!-- Min Table Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="minTable" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="minTable">Min Table</label>
						</div>
						<!-- Max Table Filter -->
						<div class="col-6 form-floating">
							<input type="text" class="form-control" id="maxTable" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder=" " />
							<label for="maxTable">Max Table</label>
						</div>

						<!-- Stock ID Filter -->
						<div class="col-md-6 form-floating">
							<input type="text" class="form-control" id="stockId" placeholder=" " />
							<label for="stockId">Stock ID</label>
						</div>
						<!-- Report Number Filter -->
						<div class="col-md-6 form-floating">
							<input type="text" class="form-control" id="reportNumber" placeholder=" " />
							<label for="reportNumber">Report Number</label>
						</div>
						<!-- Type Filter -->
						<div class="col-md-12 form-floating">
							<input type="text" class="form-control" id="type" placeholder=" " />
							<label for="type">Growth Type</label>
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

	<!-- Status Modal -->
	<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="statusModalLabel">Select Diamond Status</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div id="fullStatusList">
						@foreach ($status as $key=>$value)
							<div class="form-check custom_checkbox me-2">
								<input class="form-check-input" type="checkbox" value="{{ $value }}" id="status{{ $key }}">
								<label class="form-check-label" for="status{{ $key }}">{{ $value }}</label>
							</div>
						@endforeach
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary apply-status-filter">Apply Filter</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Location Modal -->
	<div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="locationModalLabel">Select Diamond Location</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div id="fullLocationList">
						@foreach ($location as $key=>$value)
							<div class="form-check custom_checkbox me-2">
								<input class="form-check-input" type="checkbox" value="{{ $value }}" id="location{{ $key }}">
								<label class="form-check-label" for="location{{ $key }}">{{ $value }}</label>
							</div>
						@endforeach
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary apply-location-filter">Apply Filter</button>
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
					<!-- <div class="shape_select_wrapper">
						<div class="form-check custom_checkbox me-2">
							<input class="form-check-input" type="checkbox" value="Round" id="round">
							<label class="form-check-label" for="round"><img src="" alt=""> Round</label>
						</div>
					</div> -->
					<div id="fullShapeList">
						<div class="shape_select_wrapper">
							@foreach ($shapes as $key=>$value)
								@php
									$iconPath = asset('public/image/shape/' . strtoupper($value) . '.svg');
								@endphp
								<div class="form-check custom_checkbox me-2">
									<input class="form-check-input" type="checkbox" value="{{ $value }}" id="shape{{ $key }}">
									<label class="form-check-label" for="shape{{ $key }}"><img src="{{ $iconPath }}" alt="">  {{ $value }}</label>
								</div>
							@endforeach
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary apply-shape-filter">Apply Filter</button>
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
							<div class="form-check custom_checkbox me-2">
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
						@foreach ($claritiesFinal as $key=>$value)
							@if (in_array($value, $claritiesArray))
								<div class="form-check custom_checkbox me-2">
									<input class="form-check-input" type="checkbox" value="{{ $value }}" id="clarity{{ $key }}">
									<label class="form-check-label" for="clarity{{ $key }}">{{ $value }}</label>
								</div>
							@endif
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
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="cutModalLabel">Cut Filter</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<!-- Cut filter content goes here -->
					<div id="fullCutList">
						@foreach ($cuts as $key=>$value)
							<div class="form-check custom_checkbox me-2">
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
							<div class="form-check custom_checkbox me-2">
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
							<div class="form-check custom_checkbox me-2">
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
								<div class="form-check custom_checkbox me-2">
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
					<div class="contactform whatsappform">
						<a href="#">
							<i class="fa-solid fa-brands fa-whatsapp" style="margin-top: 5px;"></i>
							<div class="contact-info">
								<span class="contact-name">Shailesh</span>
								<span class="contact-number">+91 9662868968</span>
							</div>
						</a>
					</div>
					<hr>
					<div class="contactform whatsappform">
						<a href="#">
							<i class="fa-solid fa-brands fa-whatsapp" ></i>
							<div class="contact-info">
								<span class="contact-name">Kaushal</span>
								<span class="contact-number">+91 8238615800</span>
							</div>
						</a>
					</div>
					<hr>
					<div class="contactform whatsappform">
						<a href="#">
							<i class="fa-solid fa-brands fa-whatsapp" ></i>
							<div class="contact-info">
								<span class="contact-name">Drashti</span>
								<span class="contact-number">+91 9727408511</span>
							</div>
						</a>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>
	@if (isset(Auth::user()->id))
		<div class="modal fade" id="referenceModal" tabindex="-1" aria-labelledby="referenceModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="referenceModalLabel">Select Reference</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<form id="referenceFilter" class="row g-3">
							<div id="fullReferenceList" class="col-md-12">
								@foreach ($reference as $key=>$value)
									<div class="form-check custom_checkbox me-2">
										<input class="form-check-input" type="checkbox" value="{{ $value }}" id="reference{{ $key }}">
										<label class="form-check-label" for="reference{{ $key }}">{{ $value }}</label>
									</div>
								@endforeach
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary filter-clear-button" data-bs-dismiss="modal">Clear Filter</button>
						<button type="button" class="btn btn-primary apply-reference-filter">Apply Filter</button>
					</div>
				</div>
			</div>
		</div>
	@endif
</div>

<div id="loader" style="display: none;">
	<span class="loader_img"></span>
</div>
@endsection

@section("script")
	<script>
		const userId = {{ Auth::user()->id ?? 0 }};
		const urlData = '{{ route("diamond.data") }}';
		const urlUpdateData = '{{ route("diamond.update.data") }}';
		const urlExportCsv = '{{ route("diamond.export.csv") }}';
		const urlExportXlsx = '{{ route("diamond.export.xlsx") }}';
		const columns = <?php echo $columns; ?>;
	</script>
	<script src="{{ url('public/js/script.js') }}?t={{ date('ymd') }}"></script>
@endsection