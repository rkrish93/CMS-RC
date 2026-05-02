@extends('layouts.app')

@section('title', 'Permissions')

@section('page-actions')
    <button class="btn btn-gradient-primary shadow-sm"
            type="button"
            data-bs-toggle="modal"
            data-bs-target="#permissionModal">
        <i class="mdi mdi-plus me-1"></i> Add Permission
    </button>
@endsection

@section('content')

@php
    $groupsById = $groups->keyBy('id');
@endphp

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
                <h4 class="card-title mb-1">Permission Directory</h4>
                <p class="text-muted mb-0">Organize access rules by permission group.</p>
            </div>
            <span class="badge bg-primary-subtle text-primary">{{ $permissions->count() }} Permissions</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle admin-table">
                <thead>
                    <tr>
                        <th width="80">#</th>
                        <th>Permission</th>
                        <th>Group</th>
                        <th width="150" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                        @php
                            $groupName = $groupsById->get($permission->group_id)->group_name ?? 'Ungrouped';
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $permission->name }}</div>
                                <small class="text-muted">Guard: {{ $permission->guard_name }}</small>
                            </td>
                            <td>
                                <span class="soft-badge">{{ $groupName }}</span>
                            </td>
                            <td class="text-end">
                                <button type="button"
                                        class="btn btn-sm btn-outline-info"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editPermissionModal{{ $permission->id }}">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deletePermissionModal{{ $permission->id }}">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">No permissions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($permissions as $permission)
    <div class="modal fade" id="editPermissionModal{{ $permission->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('permissions.update', $permission->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Permission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Permission Name</label>
                            <input type="text"
                                   name="name"
                                   value="{{ $permission->name }}"
                                   class="form-control"
                                   required>
                        </div>

                        <div>
                            <label class="form-label">Group</label>
                            <select name="group_id" class="form-select" required>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ $permission->group_id == $group->id ? 'selected' : '' }}>
                                        {{ $group->group_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-gradient-primary">Update Permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deletePermissionModal{{ $permission->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header">
                        <h5 class="modal-title">Delete Permission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-1">This will remove the permission:</p>
                        <h5 class="mb-0">{{ $permission->name }}</h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger">Delete Permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="permissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('permissions.store') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Permission Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div>
                        <label class="form-label">Group</label>
                        <select name="group_id" class="form-select" required>
                            <option value="">Select Group</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-gradient-primary">Save Permission</button>
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

    .soft-badge {
        display: inline-flex;
        min-height: 28px;
        align-items: center;
        padding: 5px 10px;
        border: 1px solid #ccfbf1;
        border-radius: 999px;
        background: #f0fdfa;
        color: #0f766e;
        font-size: 12px;
        font-weight: 800;
    }
</style>
@endpush
