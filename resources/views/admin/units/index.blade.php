@extends('layouts.app')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-multiple"></i>
        </span>
        Units List
    </h3>

    <a href="{{ route('units.create') }}" class="btn btn-gradient-primary btn-sm">
        + Add Unit
    </a>
</div>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">

        <div class="card">
            <div class="card-body">

                <h4 class="card-title">All Units</h4>

                <div class="table-responsive">
                    <table class="table table-bordered">

                        <thead>
                            <tr>
                                <th> Unit Name </th>
                                <th> Description </th>
                                <th> In-Charge officer </th>
                                <th> Status </th>
                                <th width="150"> Action </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($units as $unit)
                            <tr>
                                <td>{{ $unit->unit_name }}</td>
                                <td>{{ $unit->description }}</td>
                                <td>{{ $unit->incharge }}</td>
                                <td>
                                    @if($unit->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>

                                <td>

                                    <a href="{{ route('units.edit',$unit->id) }}"
                                       class="btn btn-sm btn-gradient-info">
                                        Edit
                                    </a>

                                    <form id="delete-form-{{ $unit->id }}"
                                          action="{{ route('units.destroy',$unit->id) }}"
                                          method="POST"
                                          style="display:inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="button"
                                                class="btn btn-sm btn-gradient-danger"
                                                onclick="confirmDelete({{ $unit->id }})">
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection


@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDelete(id)
{
    Swal.fire({
        title: 'Are you sure?',
        text: "User will be deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if(result.isConfirmed)
        {
            document.getElementById('delete-form-'+id).submit();
        }

    });
}
</script>

@endsection
