@extends('layouts.app')

@section('title', 'Roles')

@section('page-actions')
    <button class="btn btn-gradient-primary shadow-sm"
            type="button"
            data-bs-toggle="modal"
            data-bs-target="#roleModal">
        <i class="mdi mdi-plus me-1"></i> Add Role
    </button>
@endsection

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="card admin-table-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h4 class="card-title mb-1">Role Directory</h4>
                <p class="text-muted mb-0">Manage role names and assigned permissions.</p>
            </div>
            <span class="badge bg-primary-subtle text-primary">{{ $roles->count() }} Roles</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle admin-table">
                <thead>
                    <tr>
                        <th width="80">#</th>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th width="170" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $role->name }}</div>
                                <small class="text-muted">{{ $role->permissions->count() }} permissions assigned</small>
                            </td>
                            <td>
                                <div class="permission-chip-wrap">
                                    @forelse($role->permissions->take(8) as $permission)
                                        <span class="permission-chip">{{ $permission->name }}</span>
                                    @empty
                                        <span class="text-muted">No permissions assigned</span>
                                    @endforelse

                                    @if($role->permissions->count() > 8)
                                        <span class="permission-chip is-muted">+{{ $role->permissions->count() - 8 }} more</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <button type="button"
                                        class="btn btn-sm btn-outline-info"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editRoleModal{{ $role->id }}">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteRoleModal{{ $role->id }}">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">No roles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($roles as $role)
    <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Edit Role</h5>
                            <small class="text-muted">Update role details and permission access.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label">Role Name</label>
                            <input type="text"
                                   name="name"
                                   value="{{ $role->name }}"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="permission-grid">
                            @foreach($permissionGroups as $group)
                                <section class="permission-group-panel">
                                    <div class="permission-group-title">{{ $group->group_name }}</div>

                                    @forelse($group->permissions as $permission)
                                        <div class="form-check mb-2">
                                            <input type="checkbox"
                                                   class="form-check-input"
                                                   name="permission[]"
                                                   value="{{ $permission->id }}"
                                                   id="editperm{{ $permission->id }}{{ $role->id }}"
                                                   {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="editperm{{ $permission->id }}{{ $role->id }}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    @empty
                                        <small class="text-muted">No permissions in this group.</small>
                                    @endforelse
                                </section>
                            @endforeach
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-gradient-primary">Update Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteRoleModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('roles.destroy', $role->id) }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header">
                        <h5 class="modal-title">Delete Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-1">This will remove the role:</p>
                        <h5 class="mb-0">{{ $role->name }}</h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger">Delete Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Add Role</h5>
                        <small class="text-muted">Create a role and assign permissions.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Role Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="permission-grid">
                        @foreach($permissionGroups as $group)
                            <section class="permission-group-panel">
                                <div class="permission-group-title">{{ $group->group_name }}</div>

                                @forelse($group->permissions as $permission)
                                    <div class="form-check mb-2">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               name="permission[]"
                                               value="{{ $permission->id }}"
                                               id="perm{{ $permission->id }}">
                                        <label class="form-check-label" for="perm{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                @empty
                                    <small class="text-muted">No permissions in this group.</small>
                                @endforelse
                            </section>
                        @endforeach
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-gradient-primary">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .admin-table-card .card-title {
        font-size: 17px;
    }

    .admin-table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #475467;
    }

    .permission-chip-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .permission-chip {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 4px 9px;
        border: 1px solid #dbeafe;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
    }

    .permission-chip.is-muted {
        border-color: #e5e7eb;
        background: #f8fafc;
        color: #667085;
    }

    .permission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        align-items: start;
    }

    .permission-group-panel {
        padding: 16px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .permission-group-panel:hover {
        border-color: #d1d5db;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .permission-group-title {
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f0f0f0;
        color: #152033;
        font-weight: 800;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .permission-group-panel .form-check {
        padding: 0.75rem 0.75rem;
        margin: 6px 0;
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        background-color: #fafafa;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
    }

    .permission-group-panel .form-check:last-child {
        margin-bottom: 0;
    }

    .permission-group-panel .form-check:hover {
        background-color: #f0f9ff;
        border-color: #bfdbfe;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
    }

    .permission-group-panel .form-check-input:checked + .form-check-label {
        color: #3b82f6;
        font-weight: 600;
    }

    .modal-dialog-scrollable .modal-body {
        max-height: calc(100vh - 210px);
        overflow-y: auto;
        padding: 1.5rem;
    }

    .form-check {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0.75rem;
        padding: 0.65rem 0;
        cursor: pointer;
        margin: 0;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        min-width: 18px;
        min-height: 18px;
        margin-top: 0;
        margin-bottom: 0;
        cursor: pointer;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        background-color: #ffffff;
        transition: all 0.2s ease;
        flex-shrink: 0;
        accent-color: #3b82f6;
    }

    .form-check-input:hover {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-check-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        outline: none;
    }

    .form-check-input:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m6 10 3 3 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 100%;
    }

    .form-check-input:checked:hover {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    .form-check-label {
        margin-bottom: 0;
        margin-left: 0;
        font-size: 14px;
        color: #374151;
        font-weight: 500;
        cursor: pointer;
        user-select: none;
        line-height: 1.4;
        display: flex;
        align-items: center;
    }

    .form-check-input:disabled + .form-check-label {
        color: #9ca3af;
        cursor: not-allowed;
    }
</style>
@endpush
