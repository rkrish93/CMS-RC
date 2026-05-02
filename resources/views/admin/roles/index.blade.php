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
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 14px;
    }

    .permission-group-panel {
        padding: 14px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }

    .permission-group-title {
        margin-bottom: 10px;
        color: #152033;
        font-weight: 800;
    }
</style>
@endpush
