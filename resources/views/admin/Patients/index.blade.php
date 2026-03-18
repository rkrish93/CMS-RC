@extends('layouts.app')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-multiple"></i>
        </span>
        Patients List
    </h3>

    <a href="{{ route('patients.create') }}" class="btn btn-gradient-primary btn-sm">
        + Add Patient
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

                <h4 class="card-title">All Patients</h4>

                <div class="table-responsive">
                    <table class="table table-bordered">

                        <thead>
                            <tr>
                                <th> Patient Code </th>
                                <th> Full Name </th>
                                <th> Gender </th>
                                <th> Age </th>
                                <th> NIC No </th>
                                <th> Phone </th>
                                <th width="150"> Action </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($patients as $patient)
                            <tr>

                                <td>{{ $patient->patient_code}} </td>
                                <td>{{ $patient->title }} {{ $patient->first_name }} {{ $patient->last_name }}</td>
                                <td>{{ $patient->gender }}</td>
                                <td>{{ $patient->age }}</td>
                                <td>{{ $patient->nic }}</td>
                                <td>{{ $patient->phone }}</td>

                                <td>
                                    <a href="{{ route('patients.show',$patient->id) }}"
                                       class="btn btn-sm btn-gradient-success">
                                        View
                                    </a>
                                    <a href="{{ route('patients.edit',$patient->id) }}"
                                       class="btn btn-sm btn-gradient-info">
                                        Edit
                                    </a>

                                    <form id="delete-form-{{ $patient->id }}"
                                          action="{{ route('patients.destroy',$patient->id) }}"
                                          method="POST"
                                          style="display:inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="button"
                                                class="btn btn-sm btn-gradient-danger"
                                                onclick="confirmDelete({{ $patient->id }})">
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
        text: "Patient will be deleted!",
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
