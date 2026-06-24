@extends('layouts.app')

@section('title','Vitals')

@section('content')

@can('vitals-view')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>

        
        <p class="text-muted">Recent vitals recorded from consultations.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('vitals.index') }}" class="row g-2 align-items-end mb-3">
            <div class="col-md-6 col-lg-4">
                <label for="patient-search" class="form-label">Patient Name</label>
                <input
                    type="text"
                    id="patient-search"
                    name="search"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="Search patient name">
            </div>

            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">
                    Search
                </button>
            </div>

            @if($search !== '')
                <div class="col-md-auto">
                    <a href="{{ route('vitals.index') }}" class="btn btn-light">
                        Clear
                    </a>
                </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>BP</th>
                        <th>Temp (°C)</th>
                        <th>Pulse</th>
                        <th>Recorded At</th>
                    </tr>
                </thead>
             <tbody>

@forelse($vitals as $v)

<tr>

    <td>
        {{ optional($v->patient)->first_name }}
        {{ optional($v->patient)->last_name }}
    </td>

    <td>{{ $v->bp ?? '—' }}</td>

    <td>{{ $v->temp ?? '—' }}</td>

    <td>{{ $v->pulse ?? '—' }}</td>

    <td>
        {{ $v->created_at->format('Y-m-d H:i') }}
    </td>

</tr>

@empty

<tr>

    <td colspan="5"
        class="text-center text-muted">

        {{ $search !== '' ? 'No vitals found for this patient name.' : 'No vitals recorded.' }}

    </td>

</tr>

@endforelse

</tbody>
            </table>
        </div>

        @if($vitals->hasPages())
            <div class="d-flex justify-content-end mt-3">
                {{ $vitals->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endcan

@cannot('vitals-view')
    @if(auth()->user()->hasRole('Admin'))
        {{-- Admins can still view via role --}}
    @else
        <div class="alert alert-warning">You do not have permission to view vitals.</div>
    @endif
@endcannot

@endsection
