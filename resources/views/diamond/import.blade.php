@extends("layout.main")

@section("css")
@endsection

@section("content")
    <div class="row justify-content-center mt-4">
        <div class="col-sm-8">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
    <form action="{{ route('diamond.import.save') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row justify-content-center mt-4">
            <div class="col-sm-3"></div>
            <div class="col-sm-6">
                <div class="p-2">
                    <div class="form-group p-1">
                        <label for="">Import File: </label>
                        <input type="file" class="form-control" name="import_file" accept="">
                        @if($errors->has('import_file'))
                            <div class="text-danger">{{ $errors->first('import_file') }}</div>
                        @endif
                    </div>
                 
                    <div class="form-group p-1">
                        <button type="submit" class="btn btn-outline-primary" name="submit"><b>Submit</b></button>
                    </div>
                </div>
            </div>
            <div class="col-sm-3"></div>
        </div>
    </form>
@endsection

@section("script")
@endsection