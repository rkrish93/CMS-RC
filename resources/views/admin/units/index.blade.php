@extends('layouts.app')

@section('title', 'Units')

@section('page-actions')
    <a href="{{ route('units.create') }}" class="btn btn-gradient-primary shadow-sm">
        <i class="mdi mdi-plus me-1"></i> Add Unit
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h4 class="card-title mb-1">Clinic Units</h4>
                <p class="text-muted mb-0">Manage departments and responsible officers.</p>
            </div>
            <span class="badge bg-primary">{{ $units->count() }} Units</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle admin-table">
                <thead>
                    <tr>
                        <th>Unit Name</th>
                        <th>Description</th>
                        <th>In-Charge Officer</th>
                        <th>Status</th>
                        <th width="150" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                        <tr>
                            <td class="fw-bold text-dark">{{ $unit->unit_name }}</td>
                            <td>{{ $unit->description ?: 'N/A' }}</td>
                            <td>{{ $unit->incharge_name ?: 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $unit->status == 1 ? 'success' : 'danger' }}">
                                    {{ $unit->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('units.edit', $unit->id) }}" class="btn btn-sm btn-outline-info" title="Edit">
                                    <i class="mdi mdi-pencil"></i>
                                </a>

                                <form id="delete-form-{{ $unit->id }}"
                                      action="{{ route('units.destroy', $unit->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            onclick="confirmDelete({{ $unit->id }})">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">No units found.</td>
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
function confirmDelete(id) {
    Swal.fire({
        title: 'Delete unit?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endsection

@push('styles')
<style>
    .admin-table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #475467;
    }
</style>
@endpush
