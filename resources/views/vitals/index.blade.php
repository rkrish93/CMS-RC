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
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>BP</th>
                        <th>Temp (°C)</th>
                        <th>Pulse</th>
                        <th>SpO₂ (%)</th>
                        <th>Sugar (mg/dL)</th>
                        <th>Wt / Ht / BMI</th>
                        <th>Resp Rate</th>
                        <th>Recorded At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vitals as $v)
                        <tr>
                            <td class="fw-semibold">
                                {{ optional($v->patient)->first_name }}
                                {{ optional($v->patient)->last_name }}
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $v->bp ?? '—' }}</span></td>
                            <td>{{ $v->temp ? $v->temp . ' °C' : '—' }}</td>
                            <td>{{ $v->pulse ? $v->pulse . ' bpm' : '—' }}</td>
                            <td>
                                @if($v->oxygen_saturation)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $v->oxygen_saturation }}%</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $v->sugar ? $v->sugar . ' mg/dL' : '—' }}</td>
                            <td>
                                @php
                                    $wtHtBmi = [];
                                    if ($v->weight) $wtHtBmi[] = $v->weight . 'kg';
                                    if ($v->height) $wtHtBmi[] = $v->height . 'cm';
                                    if ($v->bmi) $wtHtBmi[] = 'BMI ' . $v->bmi;
                                @endphp
                                {{ count($wtHtBmi) > 0 ? implode(' / ', $wtHtBmi) : '—' }}
                            </td>
                            <td>{{ $v->respiratory_rate ? $v->respiratory_rate . ' /min' : '—' }}</td>
                            <td class="text-muted fs-13">
                                {{ $v->created_at->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
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
