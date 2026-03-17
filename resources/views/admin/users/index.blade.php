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
                                <th> Email </th>
                                <th> Phone </th>
                                <th width="150"> Action </th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($users as $user)
                            <tr>

                                <td>
                                    @if($user->image)
                                        <img src="{{ asset('users/'.$user->image) }}"
                                             width="50"
                                             height="50"
                                             style="border-radius:50%">
                                    @else
                                        <img src="{{ asset('assets/images/faces/face1.jpg') }}"
                                             width="50"
                                             height="50"
                                             style="border-radius:50%">
                                    @endif
                                </td>

                                <td>
                                    {{ $user->fname }} {{ $user->lname }}
                                </td>

                                <td>
                                    {{ $user->email }}
                                </td>

                                <td>
                                    {{ $user->phone }}
                                </td>

                                <td>

                                    <a href="{{ route('users.edit',$user->id) }}"
                                       class="btn btn-sm btn-gradient-info">
                                        Edit
                                    </a>

                                    <form action="{{ route('users.destroy',$user->id) }}"
                                          method="POST"
                                          style="display:inline">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-gradient-danger"
                                                onclick="return confirm('Delete user?')">
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
