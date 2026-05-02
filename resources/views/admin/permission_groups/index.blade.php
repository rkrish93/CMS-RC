@extends('layouts.app')

@section('title', 'Permission Groups')

@section('page-actions')
    <button class="btn btn-gradient-primary shadow-sm"
            type="button"
            data-bs-toggle="modal"
            data-bs-target="#groupModal">
        <i class="mdi mdi-plus me-1"></i> Add Group
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
                <h4 class="card-title mb-1">Permission Group Directory</h4>
                <p class="text-muted mb-0">Group permissions into clear administration areas.</p>
            </div>
            <span class="badge bg-primary-subtle text-primary">{{ $groups->count() }} Groups</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle admin-table">
                <thead>
                    <tr>
                        <th width="80">#</th>
                        <th>Group</th>
                        <th>Permissions</th>
                        <th width="150" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $group)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $group->group_name }}</div>
                                <small class="text-muted">Permission group</small>
                            </td>
                            <td>
                                <span class="soft-badge">{{ $group->permissions_count ?? 0 }} permissions</span>
                            </td>
                            <td class="text-end">
                                <button type="button"
                                        class="btn btn-sm btn-outline-info"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editGroupModal{{ $group->id }}">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteGroupModal{{ $group->id }}">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">No permission groups found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($groups as $group)
    <div class="modal fade" id="editGroupModal{{ $group->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('permission-groups.update', $group->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Permission Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">Group Name</label>
                        <input type="text"
                               name="group_name"
                               value="{{ $group->group_name }}"
                               class="form-control"
                               required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-gradient-primary">Update Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteGroupModal{{ $group->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('permission-groups.destroy', $group->id) }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header">
                        <h5 class="modal-title">Delete Permission Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-1">This will remove the permission group:</p>
                        <h5 class="mb-0">{{ $group->group_name }}</h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger">Delete Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="groupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('permission-groups.store') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add Permission Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label">Group Name</label>
                    <input type="text" name="group_name" class="form-control" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-gradient-primary">Save Group</button>
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
        border: 1px solid #e0e7ff;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 12px;
        font-weight: 800;
    }
</style>
@endpush
