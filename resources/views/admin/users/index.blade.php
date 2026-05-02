@extends('layouts.app')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-multiple"></i>
        </span>
        User List
    </h3>

    <a href="{{ route('users.create') }}" class="btn btn-gradient-primary btn-sm">
        + Add User
    </a>
</div>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">

        <div class="card">
            <div class="card-body">

                <h4 class="card-title">All Users</h4>

                <div class="table-responsive">
                    <table class="table table-bordered">

                        <thead>
                            <tr>
                                <th> Image </th>
                                <th> Name </th>
                                <th> Designation </th>
                                <th> Unit </th>
                                <th> Email </th>
                                <th> Phone </th>
                                <th> NIC </th>
                                <th> Joining Date </th>
                                <th> Status </th>
                                <th width="150"> Action </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($users as $user)
                            <tr>

                                <td>
                                    @if($user->image)
                                        <img src="{{ asset('assets/images/profiles/'.$user->image) }}"
                                             width="50" height="50"
                                             style="border-radius:50%">
                                    @else
                                        <img src="{{ asset('assets/images/faces/face1.jpg') }}"
                                             width="50" height="50"
                                             style="border-radius:50%">
                                    @endif
                                </td>

                                <td>{{ $user->fname }} {{ $user->lname }}</td>
                                <td>{{ $user->designation }}</td>
                                <td>{{ $user->unit->unit_name ?? '' }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->nic }}</td>
                                <td>{{ $user->join_date }}</td>
                                <td>
                                    @if($user->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>

                                <td>

                                    <a href="{{ route('users.show',$user->id) }}"
                                       class="btn btn-sm btn-gradient-primary me-1">
                                        View
                                    </a>

                                    <a href="{{ route('users.edit',$user->id) }}"
                                       class="btn btn-sm btn-gradient-info me-1">
                                        Edit
                                    </a>

                                    <form id="delete-form-{{ $user->id }}"
                                          action="{{ route('users.destroy',$user->id) }}"
                                          method="POST"
                                          style="display:inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="button"
                                                class="btn btn-sm btn-gradient-danger"
                                                onclick="confirmDelete({{ $user->id }})">
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection


@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDelete(id)
{
    Swal.fire({
        title: 'Are you sure?',
        text: "User will be deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if(result.isConfirmed)
        {
            document.getElementById('delete-form-'+id).submit();
        }

    });
}
</script>

@endsection
