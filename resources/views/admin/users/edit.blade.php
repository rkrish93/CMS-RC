@extends('layouts.app')

@section('content')

<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-account-edit"></i>
        </span>
        Edit User
    </h3>
</div>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">

        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Update User Information</h4>

                <form method="POST"
                      action="{{ route('users.update',$user->id) }}"
                      enctype="multipart/form-data">

                    @csrf
                    @method('PUT')

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text"
                                       name="fname"
                                       value="{{ $user->fname }}"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text"
                                       name="lname"
                                       value="{{ $user->lname }}"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email"
                                       name="email"
                                       value="{{ $user->email }}"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text"
                                       name="phone"
                                       value="{{ $user->phone }}"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>New Password (optional)</label>
                                <input type="password"
                                       name="password"
                                       class="form-control"
                                       placeholder="Leave blank if no change">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>User Image</label>

                                <input type="file"
                                       name="image"
                                       class="form-control mb-2">

                                @if($user->image)
                                    <img src="{{ asset('assets/images/profiles/'.$user->image) }}"
                                         width="60"
                                         style="border-radius:50%">
                                @endif

                            </div>
                        </div>

                    </div>

                    <button type="submit"
                            class="btn btn-gradient-primary me-2">
                        Update User
                    </button>

                    <a href="{{ route('users.index') }}"
                       class="btn btn-light">
                        Cancel
                    </a>

                </form>

            </div>
        </div>

    </div>
</div>

@endsection
