@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="row justify-content-center mt-4">
                    <div class="col-sm-10">
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
                    </div>
                </div>
                <form action="{{ route('diamond.import.save') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row justify-content-center mt-4">
                        <div class="col-sm-1"></div>
                        <div class="col-sm-10">
                            <div class="p-2">
                                <div class="form-group p-1">
                                    <label for="">Import File: </label>
                                    <input type="file" class="form-control" name="import_file" accept=".xlsx, .xls">
                                    @if($errors->has('import_file'))
                                        <div class="text-danger">{{ $errors->first('import_file') }}</div>
                                    @endif
                                </div>
                             
                                <div class="form-group p-1 text-center">
                                    <button type="submit" class="btn btn-outline-primary" name="submit"><b>Submit</b></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-1"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
