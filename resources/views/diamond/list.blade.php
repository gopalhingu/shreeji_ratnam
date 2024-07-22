@extends("layout.main")

@section("css")
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
@endsection

@section("content")
    <div>
        <div class="row">
            <div class="col-lg-12">
                <div class="row text-center rounded-pill bg-warning p-2 m-2">
                    
                    <div class="col-md-1">
                        <b class="btn btn-outline-secondary">Reload</b>
                    </div>
                    <div class="col-md-9">
                        <b class="btn btn-outline-info">Logo</b>
                    </div>
                    <div class="col-md-2 float-right">
                        <b class="btn btn-outline-danger">Download</b>
                        <b class="btn btn-outline-primary">Contact Us</b>
                        <b class="btn btn-outline-success">Filter</b>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="row text-center rounded-pill bg-secondary p-2 m-2">
                    <div class="col-md-3">
                        <b>Total Stock</b>
                        <br>
                        <b></b>
                    </div>
                    <div class="col-md-3">
                        <b>Total Carat</b>
                        <br>
                        <b></b>
                    </div>
                    <div class="col-md-3">
                        <b>Total Amount</b>
                        <br>
                        <b></b>
                    </div>
                    <div class="col-md-3 text-info">
                        <b>Email: </b>
                        <br>
                        <b>Call: </b>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <table id="diamond_table" class="table table-striped table-hover">
                    <thead>
                        <th><input type="checkbox" class="select_all"></th>
                        <th>No.</th>
                        <th>Type</th>
                        <th>Stock ID.</th>
                        <th>Report #</th>
                        <th>Lab</th>
                        <th>Shape</th>
                        <th>Carat</th>
                        <th>Color</th>
                        <th>Clarity</th>
                        <th>Rap Rate</th>
                        <th>Dis %</th>
                        <th>$ Price</th>
                        <th>$ Amount</th>
                        <th>Cut</th>
                        <th>Polish</th>
                        <th>Symmetry</th>
                        <th>Flo.</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section("script")
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {

            var table = $('#diamond_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("diamond.data") }}',
                columns: [
                    { data: 'id', render: function (data) { return '<input type="checkbox" class="select_row" value="' + data + '">'; }, orderable: false, searchable: false },
                    { data: 'id' },
                    { data: 'growth_type' },
                    { data: 'stock_id' },
                    { data: 'report_number' },
                    { data: 'lab' },
                    { data: 'shape' },
                    { data: 'ratio' },
                    { data: 'color' },
                    { data: 'clarity' },
                    { data: 'rap_amount' },
                    { data: 'discounts' },
                    { data: 'total_price' },
                    { data: 'total_price' }, // Assuming $ Amount is the same as $ Price for demonstration
                    { data: 'cut' },
                    { data: 'polish' },
                    { data: 'symmetry' },
                    { data: 'fluorescence_intensity' }
                ]
            });
            
            /* $('#diamond_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("diamond.data") }}',
                    data: function (d) {
                        d.shape = $('#shape').val();
                        d.price_min = $('#price_min').val();
                        d.price_max = $('#price_max').val();
                    }
                },
                columns: [
                    { data: 'id' },
                    { data: 'stock_id' },
                    { data: 'shape' },
                    { data: 'total_price' },
                ]
            }); */

            /* $('#apply-filters').on('click', function() {
                table.ajax.reload();
                $('#filter-popup').hide();
            });

            $('#close-popup').on('click', function() {
                $('#filter-popup').hide();
            });

            // Code to open popup (you can trigger this from a button or link)
            $('.open-filter').on('click', function() {
                $('#filter-popup').show();
            }); */
            
            // Select all rows
            $('.select_all').on('click', function() {
                var rows = $('#diamond_table').DataTable().rows({ 'search': 'applied' }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });

            // Select row
            $('#diamond_table tbody').on('change', 'input[type="checkbox"]', function() {
                if (!this.checked) {
                    var el = $('.select_all').get(0);
                    if (el && el.checked && ('indeterminate' in el)) {
                        el.indeterminate = true;
                    }
                }
            });

        });
    </script>
@endsection