@extends('layout.admin')
@section('content')
<section class="content-header"><h1>Edit Data Admin</h1></section>

<div class="container-fluid">
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">Edit Admin</h3></div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('admin.update', $admin->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <input type="hidden" name="nip" class="form-control" value="{{ old('nip', $admin->nip) }}" readonly>
                </div>
                <div class="form-group">
                    <label>Tipe Admin</label>
                    <select name="tipe_admin" class="form-control" required>
                        <option value="Admin Master" {{ old('tipe_admin', $admin->tipe_admin)=='Admin Master'?'selected':'' }}>Admin Master</option>
                        <option value="Admin" {{ old('tipe_admin', $admin->tipe_admin)=='Admin'?'selected':'' }}>Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-info">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
$(function(){
    $("#nama").autocomplete({
        source: "{{ route('pegawai.searchByName') }}",
        select: function(event, ui){
            $("#nama").val(ui.item.nama);
            $("#nip").val(ui.item.nip);
            $("#jk").val(ui.item.jk);
        }
    });
    $("#nip").autocomplete({
        source: "{{ route('pegawai.searchByNip') }}",
        select: function(event, ui){
            $("#nip").val(ui.item.nip);
            $("#nama").val(ui.item.nama);
            $("#jk").val(ui.item.jk);
        }
    });
});
</script>
@endsection
