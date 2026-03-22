@extends('layouts.app')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <h3 class="page-title mb-0">
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

<div class="card">
<div class="card-body">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4 class="card-title mb-0">All Patients</h4>

        <div class="input-group" style="width:400px">
            <input type="text"
                   id="searchPatient"
                   class="form-control"
                   placeholder="Search Code / Name / NIC / Phone"
                   autofocus>

            <button type="button"
                    id="resetSearch"
                    class="btn btn-outline-secondary">
                <span class="page-title-icon text-red me-1">
                    <i class="mdi mdi-autorenew mdi-18px"></i>
                </span>
            </button>
        </div>

    </div>

    <div class="table-responsive">

        <table class="table table-bordered table-hover">

            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>NIC</th>
                    <th>Phone</th>
                    <th width="170">Action</th>
                </tr>
            </thead>

            <tbody id="patientTable">
                @forelse($patients as $patient)
                <tr>
                    <td>{{ $patient->patient_code }}</td>
                    <td>{{ $patient->title }} {{ $patient->first_name }} {{ $patient->last_name }}</td>
                    <td>{{ $patient->gender }}</td>
                    <td>{{ $patient->age }}</td>
                    <td>{{ $patient->nic }}</td>
                    <td>{{ $patient->phone }}</td>
                    <td>

                        <a href="{{ route('patients.show',$patient->id) }}"
                           class="btn btn-sm btn-gradient-success">View</a>

                        <a href="{{ route('patients.edit',$patient->id) }}"
                           class="btn btn-sm btn-gradient-info">Edit</a>

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
                @empty
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        No Patients Available
                    </td>
                </tr>
                @endforelse
            </tbody>

        </table>

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
        title: 'Delete Patient?',
        text: "This action cannot be undone",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes Delete'
    }).then((result)=>{
        if(result.isConfirmed){
            document.getElementById('delete-form-'+id).submit();
        }
    });
}
</script>

<script>

const searchBox = document.getElementById('searchPatient');
const resetBtn = document.getElementById('resetSearch');
let typingTimer;

searchBox.addEventListener('keyup', function(){

    clearTimeout(typingTimer);

    typingTimer = setTimeout(()=>{

        fetch("{{ route('patients.ajax.search') }}?search="+this.value)

        .then(res=>res.json())
        .then(data=>renderPatients(data));

    },400);

});

resetBtn.addEventListener('click', function(){

    searchBox.value = '';
    searchBox.focus();

    fetch("{{ route('patients.ajax.search') }}")
        .then(res=>res.json())
        .then(data=>renderPatients(data));
});

function renderPatients(data)
{
    let html = '';

    if(data.length === 0){
        html = `<tr>
                    <td colspan="7" class="text-center text-danger">
                        No Patients Found
                    </td>
                </tr>`;
    }

    data.forEach(p=>{
        html += `
        <tr>
            <td>${p.patient_code ?? ''}</td>
            <td>${p.title ?? ''} ${p.first_name ?? ''} ${p.last_name ?? ''}</td>
            <td>${p.gender ?? ''}</td>
            <td>${p.age ?? ''}</td>
            <td>${p.nic ?? ''}</td>
            <td>${p.phone ?? ''}</td>
            <td>
                <a href="/patients/${p.id}" class="btn btn-sm btn-gradient-success">View</a>
                <a href="/patients/${p.id}/edit" class="btn btn-sm btn-gradient-info">Edit</a>
                <a href="/patients/${p.id}/destroy" class="btn btn-sm btn-gradient-danger">Edit</a>
            </td>
        </tr>`;
    });

    document.getElementById('patientTable').innerHTML = html;
}

</script>

@endsection
