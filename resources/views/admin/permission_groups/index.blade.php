@extends('layouts.app')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-security"></i>
        </span>
        Permission Group List
    </h3>

    <button class="btn btn-gradient-primary shadow-sm"
            data-bs-toggle="modal"
            data-bs-target="#groupModal">
        <i class="mdi mdi-plus"></i> Add Permission Group
    </button>
</div>

<div class="card">
<div class="card-body">

<table class="table table-bordered table-striped">

<thead class="bg-dark text-white">
<tr>
    <th width="80">#</th>
    <th>Group Name</th>
    <th width="200">Action</th>
</tr>
</thead>

<tbody>

@forelse($groups as $key => $group)

<tr>
    <td>{{ $key + 1 }}</td>

    <td>
        <span class="badge badge-primary px-3 py-2">
            {{ $group->group_name }}
        </span>
    </td>

    <td>

        <!-- EDIT BUTTON -->
        <button type="button"
                class="btn btn-sm btn-gradient-info"
                data-bs-toggle="modal"
                data-bs-target="#editGroupModal{{ $group->id }}">
            Edit
        </button>

        <!-- DELETE BUTTON -->
        <button type="button"
                class="btn btn-sm btn-gradient-danger"
                data-bs-toggle="modal"
                data-bs-target="#deleteGroupModal{{ $group->id }}">
            Delete
        </button>

    </td>

</tr>


<!-- ===== EDIT MODAL ===== -->
<div class="modal fade" id="editGroupModal{{ $group->id }}" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<form action="{{ route('permission-groups.update',$group->id) }}" method="POST">
@csrf
@method('PUT')

<div class="modal-header bg-info text-white">
    <h5>Edit Permission Group</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <label>Group Name</label>
    <input type="text"
           name="group_name"
           value="{{ $group->group_name }}"
           class="form-control"
           required>
</div>

<div class="modal-footer">
    <button class="btn btn-info">Update</button>
</div>

</form>

</div>
</div>
</div>


<!-- ===== DELETE MODAL ===== -->
<div class="modal fade" id="deleteGroupModal{{ $group->id }}" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<form action="{{ route('permission-groups.destroy',$group->id) }}" method="POST">
@csrf
@method('DELETE')

<div class="modal-header bg-danger text-white">
    <h5>Delete Permission Group</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body text-center">
    <p>Are you sure delete</p>
    <h5>{{ $group->group_name }}</h5>
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
        No Permission Groups Found
    </td>
</tr>

@endforelse

</tbody>

</table>

</div>
</div>


<!-- ===== ADD MODAL ===== -->
<div class="modal fade" id="groupModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<form action="{{ route('permission-groups.store') }}" method="POST">
@csrf

<div class="modal-header btn-gradient-primary text-white">
    <h5>Add Permission Group</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <label>Group Name</label>
    <input type="text"
           name="group_name"
           class="form-control"
           required>
</div>

<div class="modal-footer">
    <button class="btn btn-gradient-primary">Save</button>
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

