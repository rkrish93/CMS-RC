@extends('layouts.app')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-lock"></i>
        </span>
        Permission List
    </h3>

    <button class="btn btn-gradient-primary shadow-sm"
            data-bs-toggle="modal"
            data-bs-target="#permissionModal">
        <i class="mdi mdi-plus"></i> Add Permission
    </button>
</div>

<div class="card">
<div class="card-body">

<table class="table table-bordered table-striped">

<thead class="bg-dark text-white">
<tr>
    <th width="80">#</th>
    <th>Permission Name</th>
    <th>Group</th>
    <th width="200">Action</th>
</tr>
</thead>

<tbody>

@forelse($permissions as $key => $permission)

<tr>
    <td>{{ $key + 1 }}</td>

    <td>
        <span class="badge badge-success px-3 py-2">
            {{ $permission->name }}
        </span>
    </td>

    <td>
        <span class="badge badge-primary">
            {{ $permission->group->group_name ?? '' }}
        </span>
    </td>

    <td>

        <!-- EDIT BUTTON -->
        <button type="button"
                class="btn btn-sm btn-gradient-info"
                data-bs-toggle="modal"
                data-bs-target="#editPermissionModal{{ $permission->id }}">
            Edit
        </button>

        <!-- DELETE BUTTON -->
        <button type="button"
                class="btn btn-sm btn-gradient-danger"
                data-bs-toggle="modal"
                data-bs-target="#deletePermissionModal{{ $permission->id }}">
            Delete
        </button>

    </td>

</tr>


<!-- ===== EDIT MODAL ===== -->
<div class="modal fade" id="editPermissionModal{{ $permission->id }}" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<form action="{{ route('permissions.update',$permission->id) }}" method="POST">
@csrf
@method('PUT')

<div class="modal-header bg-info text-white">
    <h5>Edit Permission</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    <div class="mb-2">
        <label>Permission Name</label>
        <input type="text"
               name="name"
               value="{{ $permission->name }}"
               class="form-control"
               required>
    </div>

    <div>
        <label>Group</label>
        <select name="group_id" class="form-control" required>
            @foreach($groups as $group)
                <option value="{{ $group->id }}"
                    {{ $permission->group_id == $group->id ? 'selected' : '' }}>
                    {{ $group->group_name }}
                </option>
            @endforeach
        </select>
    </div>

</div>

<div class="modal-footer">
    <button class="btn btn-info">Update</button>
</div>

</form>

</div>
</div>
</div>


<!-- ===== DELETE MODAL ===== -->
<div class="modal fade" id="deletePermissionModal{{ $permission->id }}" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<form action="{{ route('permissions.destroy',$permission->id) }}" method="POST">
@csrf
@method('DELETE')

<div class="modal-header bg-danger text-white">
    <h5>Delete Permission</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body text-center">
    <p>Are you sure delete</p>
    <h5>{{ $permission->name }}</h5>
</div>

<div class="modal-footer justify-content-center">

    <button type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">
        Cancel
    </button>

    <button class="btn btn-danger">
        Yes Delete
    </button>

</div>

</form>

</div>
</div>
</div>


@empty

<tr>
    <td colspan="4" class="text-center">
        No Permissions Found
    </td>
</tr>

@endforelse

</tbody>

</table>

</div>
</div>


<!-- ===== ADD MODAL ===== -->
<div class="modal fade" id="permissionModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<form action="{{ route('permissions.store') }}" method="POST">
@csrf

<div class="modal-header bg-primary text-white">
    <h5>Add Permission</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    <div class="mb-2">
        <label>Permission Name</label>
        <input type="text"
               name="name"
               class="form-control"
               required>
    </div>

    <div>
        <label>Group</label>
        <select name="group_id" class="form-control" required>
            <option value="">Select Group</option>
            @foreach($groups as $group)
                <option value="{{ $group->id }}">
                    {{ $group->group_name }}
                </option>
            @endforeach
        </select>
    </div>

</div>

<div class="modal-footer">
    <button class="btn btn-primary">Save</button>
</div>

</form>

</div>
</div>
</div>

@endsection

<style>
.form-control,
.form-select {
    height: 45px;
    border-radius: 6px;
}
</style>
