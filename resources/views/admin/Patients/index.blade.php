@extends('layouts.app')

@section('title', 'Patients')

@section('page-actions')
    @can('patients-create')
        <a href="{{ route('patients.create') }}" class="btn btn-gradient-primary shadow-sm">
            <i class="mdi mdi-account-plus me-1"></i> Add Patient
        </a>
    @endcan
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card patient-table-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h4 class="card-title mb-1">Patient Directory</h4>
                <p class="text-muted mb-0">Search and manage registered clinic patients.</p>
            </div>

            <div class="input-group patient-search">
                <span class="input-group-text bg-white">
                    <i class="mdi mdi-magnify"></i>
                </span>
                <input type="text"
                       id="searchPatient"
                       class="form-control"
                       placeholder="Search code, name, NIC or phone"
                       value="{{ $search ?? '' }}"
                       autofocus>
                <button type="button" id="resetSearch" class="btn btn-outline-secondary" aria-label="Reset search">
                    <i class="mdi mdi-autorenew"></i>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle patient-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Patient</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>NIC</th>
                        <th>Phone</th>
                        <th width="150" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="patientTable">
                    @forelse($patients as $patient)
                        <tr>
                            <td>
                                <span class="code-pill">{{ $patient->patient_code ?: 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    {{ trim(($patient->title ? $patient->title . ' ' : '') . $patient->first_name . ' ' . $patient->last_name) ?: 'N/A' }}
                                </div>
                                <small class="text-muted">{{ $patient->patient_type ?: 'Patient' }}</small>
                            </td>
                            <td>{{ $patient->gender ?: 'N/A' }}</td>
                            <td>{{ $patient->age ?? 'N/A' }}</td>
                            <td>{{ $patient->nic ?: 'N/A' }}</td>
                            <td>{{ $patient->phone ?: 'N/A' }}</td>
                            <td class="text-end">
                                @can('patients-view')
                                    <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-sm btn-outline-success" title="View">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                @endcan

                                @can('patients-edit')
                                    <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-sm btn-outline-info" title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                @endcan

                                @can('patients-delete')
                                    <form id="delete-form-{{ $patient->id }}"
                                          action="{{ route('patients.destroy', $patient->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete"
                                                onclick="confirmDelete({{ $patient->id }})">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No patients found.</td>
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
const searchBox = document.getElementById('searchPatient');
const resetBtn = document.getElementById('resetSearch');
const canViewPatients = @json(auth()->user()?->can('patients-view'));
const canEditPatients = @json(auth()->user()?->can('patients-edit'));
const canDeletePatients = @json(auth()->user()?->can('patients-delete'));
const csrfToken = @json(csrf_token());
const patientsBaseUrl = @json(url('/patients'));
let typingTimer;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function (character) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[character];
    });
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Delete patient?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id)?.submit();
        }
    });
}

function fetchPatients(search = '') {
    fetch("{{ route('patients.ajax.search') }}?search=" + encodeURIComponent(search))
        .then(response => response.json())
        .then(data => renderPatients(data));
}

searchBox.addEventListener('keyup', function () {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => fetchPatients(this.value), 350);
});

resetBtn.addEventListener('click', function () {
    searchBox.value = '';
    searchBox.focus();
    fetchPatients();
});

function renderPatients(data) {
    if (data.length === 0) {
        document.getElementById('patientTable').innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-5">No patients found.</td>
            </tr>`;
        return;
    }

    const rows = data.map((patient) => {
        const fullName = `${patient.title ?? ''} ${patient.first_name ?? ''} ${patient.last_name ?? ''}`.trim() || 'N/A';
        let actions = '';

        if (canViewPatients) {
            actions += `<a href="${patientsBaseUrl}/${patient.id}" class="btn btn-sm btn-outline-success" title="View"><i class="mdi mdi-eye"></i></a> `;
        }

        if (canEditPatients) {
            actions += `<a href="${patientsBaseUrl}/${patient.id}/edit" class="btn btn-sm btn-outline-info" title="Edit"><i class="mdi mdi-pencil"></i></a> `;
        }

        if (canDeletePatients) {
            actions += `
                <form id="delete-form-${patient.id}" action="${patientsBaseUrl}/${patient.id}" method="POST" class="d-inline">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="confirmDelete(${patient.id})">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </form>`;
        }

        return `
            <tr>
                <td><span class="code-pill">${escapeHtml(patient.patient_code || 'N/A')}</span></td>
                <td>
                    <div class="fw-bold text-dark">${escapeHtml(fullName)}</div>
                    <small class="text-muted">${escapeHtml(patient.patient_type || 'Patient')}</small>
                </td>
                <td>${escapeHtml(patient.gender || 'N/A')}</td>
                <td>${escapeHtml(patient.age ?? 'N/A')}</td>
                <td>${escapeHtml(patient.nic || 'N/A')}</td>
                <td>${escapeHtml(patient.phone || 'N/A')}</td>
                <td class="text-end">${actions}</td>
            </tr>`;
    }).join('');

    document.getElementById('patientTable').innerHTML = rows;
}
</script>
@endsection

@push('styles')
<style>
    .patient-search {
        max-width: 430px;
        min-width: 280px;
    }

    .patient-table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #475467;
    }

    .code-pill {
        display: inline-flex;
        min-height: 28px;
        align-items: center;
        padding: 5px 10px;
        border: 1px solid #dbeafe;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 800;
    }
</style>
@endpush
