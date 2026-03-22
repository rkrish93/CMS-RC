@extends('layouts.app')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-key"></i>
        </span>
        Role List
    </h3>

    <button class="btn btn-gradient-primary shadow-sm"
            data-bs-toggle="modal"
            data-bs-target="#roleModal">
        <i class="mdi mdi-plus"></i> Add Role
    </button>
</div>

<div class="card">
<div class="card-body">

<table class="table table-bordered table-striped">

<thead class="bg-dark text-white">
<tr>
    <th width="80">#</th>
    <th>Role Name</th>
    <th>Permissions</th>
    <th width="200">Action</th>
</tr>
</thead>

<tbody>

@forelse($roles as $key => $role)

<tr>
    <td>{{ $key + 1 }}</td>

    <td>
        <span class="badge badge-success px-3 py-2">
            {{ $role->name }}
        </span>
    </td>
    <td>

        @foreach($role->permissions as $permission)
            <span class="badge bg-success me-1 mb-1">
                {{ $permission->name }}
            </span>
        @endforeach

    </td>

    <td>

        <!-- EDIT BUTTON -->
        <button type="button"
                class="btn btn-sm btn-gradient-info"
                data-bs-toggle="modal"
                data-bs-target="#editRoleModal{{ $role->id }}">
            Edit
        </button>

        <!-- DELETE BUTTON -->
        <button type="button"
                class="btn btn-sm btn-gradient-danger"
                data-bs-toggle="modal"
                data-bs-target="#deleteRoleModal{{ $role->id }}">
            Delete
        </button>

    </td>

</tr>


<!-- ===== EDIT ROLE MODAL ===== -->
<div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">

<form action="{{ route('roles.update',$role->id) }}" method="POST">
@csrf
@method('PUT')

<div class="modal-header bg-gradient-info text-white">
    <h5 class="modal-title">Edit Role</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    <!-- ROLE NAME -->
    <div class="mb-3">
        <label class="form-label">Role Name</label>
        <input type="text"
               name="name"
               value="{{ $role->name }}"
               class="form-control"
               required>
    </div>

    <hr>

    <h5 class="mb-3">Update Permissions</h5>

    <div class="row">

        @foreach($permissionGroups as $group)
        <div class="col-md-4 mb-3">

            <div class="card border shadow-sm">
                <div class="card-header bg-light fw-bold">
                    {{ $group->group_name }}
                </div>

                <div class="card-body">

                    @foreach($group->permissions as $permission)

                    <div class="form-check mb-1">
                        <input type="checkbox"
                               class="form-check-input"
                               name="permission[]"
                               value="{{ $permission->id }}"
                               id="editperm{{ $permission->id }}{{ $role->id }}"
                               {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>

                        <label class="form-check-label"
                               for="editperm{{ $permission->id }}{{ $role->id }}">
                            {{ $permission->name }}
                        </label>
                    </div>

                    @endforeach

                </div>
            </div>

        </div>
        @endforeach

    </div>

</div>

<div class="modal-footer">
    <button class="btn btn-info px-4">Update Role</button>
</div>

</form>

</div>
</div>
</div>


<!-- ===== DELETE MODAL ===== -->
<div class="modal fade" id="deleteRoleModal{{ $role->id }}" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<form action="{{ route('roles.destroy',$role->id) }}" method="POST">
@csrf
@method('DELETE')

<div class="modal-header bg-danger text-white">
    <h5>Delete Role</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body text-center">
    <p>Are you sure delete</p>
    <h5>{{ $role->name }}</h5>
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
    <td colspan="3" class="text-center">
        No Roles Found
    </td>
</tr>

@endforelse

</tbody>

</table>

</div>
</div>


<!-- ===== ADD MODAL ===== -->
<div class="modal fade" id="roleModal" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">

<form action="{{ route('roles.store') }}" method="POST">
@csrf

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title">Add Role</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    <!-- ROLE NAME -->
    <div class="mb-3">
        <label class="form-label">Role Name</label>
        <input type="text"
               name="name"
               class="form-control"
               required>
    </div>

    <hr>

    <h5 class="mb-3">Assign Permissions</h5>

    <div class="row">

        @foreach($permissionGroups as $group)
        <div class="col-md-4 mb-3">

            <div class="card border shadow-sm">
                <div class="card-header bg-light fw-bold">
                    {{ $group->group_name }}
                </div>

                <div class="card-body">

                    @foreach($group->permissions as $permission)

                    <div class="form-check mb-1">
                        <input type="checkbox"
                               class="form-check-input"
                               name="permission[]"
                               value="{{ $permission->id }}"
                               id="perm{{ $permission->id }}">

                        <label class="form-check-label"
                               for="perm{{ $permission->id }}">
                            {{ $permission->name }}
                        </label>
                    </div>

                    @endforeach

                </div>
            </div>

        </div>
        @endforeach

    </div>

</div>

<div class="modal-footer">
    <button class="btn btn-primary px-4">Save Role</button>
</div>

</form>

</div>
</div>
</div>
@endsection

<style>
    .form-select {
    height: 45px;
    /* border-radius: 6px; */
}
</style>
