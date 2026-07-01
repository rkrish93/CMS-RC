@extends('layouts.app')

@section('title', 'Medicines')

@section('page-actions')
    @can('products-create')
        <a href="{{ route('products.create') }}" class="btn btn-gradient-primary shadow-sm">
            <i class="mdi mdi-plus me-1"></i> Add Medicine
        </a>
    @endcan
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h4 class="card-title mb-1">Medicine Master</h4>
                <p class="text-muted mb-0">Manage all medicines in the system.</p>
            </div>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search code, medicine or generic name">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100">
                    <i class="mdi mdi-magnify me-1"></i> Search
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Medicine Code</th>
                        <th>Medicine Name</th>
                        <th>Generic Name</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><span class="badge bg-light text-dark">{{ $product->product_code }}</span></td>
                            <td class="fw-semibold">{{ $product->medicine_name }}</td>
                            <td>{{ $product->generic_name ?? '-' }}</td>
                            <td>{{ ucfirst($product->unit) }}</td>
                            <td>
                                <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('products-edit')
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-info">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                @endcan
                                @can('products-delete')
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this medicine?')">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No medicines found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $products->links() }}
    </div>
</div>

@endsection

@push('styles')
<style>
    .table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #475467;
    }
</style>
@endpush
