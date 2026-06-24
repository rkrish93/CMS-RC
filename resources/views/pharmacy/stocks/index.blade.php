@extends('layouts.app')

@section('title', 'Pharmacy Stock')

@section('page-actions')
    @can('pharmacy-stocks-create')
        <button class="btn btn-gradient-primary" data-bs-toggle="modal" data-bs-target="#createStockModal">
            <i class="mdi mdi-plus me-1"></i> Add Stock
        </button>
    @endcan
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search medicine or batch">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Generic</th>
                        <th>Batch</th>
                        <th>Qty</th>
                        <th>Reorder</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr>
                            <td>{{ $stock->medicine_name }}</td>
                            <td>{{ $stock->generic_name ?? '-' }}</td>
                            <td>{{ $stock->batch_no }}</td>
                            <td class="{{ $stock->quantity <= $stock->reorder_level ? 'text-danger fw-bold' : '' }}">{{ $stock->quantity }}</td>
                            <td>{{ $stock->reorder_level }}</td>
                            <td>{{ optional($stock->expiry_date)->format('Y-m-d') ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $stock->is_active ? 'success' : 'secondary' }}">
                                    {{ $stock->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('pharmacy-stocks-edit')
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#editStockModal{{ $stock->id }}">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                @endcan
                                @can('pharmacy-stocks-delete')
                                    <form action="{{ route('pharmacy-stocks.destroy', $stock->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this stock item?')">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>

                        <div class="modal fade" id="editStockModal{{ $stock->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('pharmacy-stocks.update', $stock->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Stock Item</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            @include('pharmacy.stocks.partials.form', ['stock' => $stock, 'useOld' => true])
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-gradient-primary">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No stock items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $stocks->links() }}
    </div>
</div>

<div class="modal fade" id="createStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('pharmacy-stocks.store') }}" method="POST" autocomplete="off" data-create-stock-form="1">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Stock Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('pharmacy.stocks.partials.form', ['useOld' => false])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-gradient-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const createModal = document.getElementById('createStockModal');
    if (!createModal) {
        return;
    }

    createModal.addEventListener('show.bs.modal', function () {
        const form = createModal.querySelector('form');
        if (form) {
            form.reset();
        }
    });

    createModal.addEventListener('shown.bs.modal', function () {
        const form = createModal.querySelector('form[data-create-stock-form="1"]');
        if (!form) {
            return;
        }

        const setValue = (name, value) => {
            const el = form.querySelector(`[name="${name}"]`);
            if (el) {
                el.value = value;
            }
        };

        setValue('medicine_name', '');
        setValue('generic_name', '');
        setValue('batch_no', '');
        setValue('unit', '');
        setValue('expiry_date', '');
        setValue('quantity', '0');
        setValue('reorder_level', '10');

        const active = form.querySelector('[name="is_active"]');
        if (active) {
            active.checked = true;
        }
    });
});
</script>
@endpush
