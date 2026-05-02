@extends('layouts.app')

@section('title', 'Users')

@section('page-actions')
    @can('users-create')
        <a href="{{ route('users.create') }}" class="btn btn-gradient-primary shadow-sm">
            <i class="mdi mdi-account-plus me-1"></i> Add User
        </a>
    @endcan
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h4 class="card-title mb-1">User Directory</h4>
                <p class="text-muted mb-0">Manage staff accounts, roles, and access status.</p>
            </div>
            <span class="badge bg-primary">{{ $users->count() }} Users</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Designation</th>
                        <th>Unit</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th width="150" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($user->image)
                                        <img src="{{ asset('assets/images/profiles/' . $user->image) }}"
                                             alt="{{ $user->name }}"
                                             class="user-avatar">
                                    @else
                                        <span class="user-avatar placeholder-avatar">
                                            <i class="mdi mdi-account"></i>
                                        </span>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->name ?: 'N/A' }}</div>
                                        <small class="text-muted">{{ $user->nic ?: 'No NIC' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->designation ?: 'N/A' }}</td>
                            <td>{{ $user->unit->unit_name ?? 'N/A' }}</td>
                            <td>{{ $user->email ?: 'N/A' }}</td>
                            <td>{{ $user->phone ?: 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $user->status == 1 ? 'success' : 'danger' }}">
                                    {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('users-view')
                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-outline-success" title="View">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                @endcan

                                @can('users-edit')
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-outline-info" title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                @endcan

                                @can('users-delete')
                                    <form id="delete-form-{{ $user->id }}"
                                          action="{{ route('users.destroy', $user->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete"
                                                onclick="confirmDelete({{ $user->id }})">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No users found.</td>
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
        title: 'Delete user?',
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

    .user-avatar {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 50%;
        object-fit: cover;
    }

    .placeholder-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        font-size: 22px;
    }
</style>
@endpush
