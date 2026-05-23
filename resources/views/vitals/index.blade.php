@extends('layouts.app')

@section('title','Vitals')

@section('content')

@can('vitals-view')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Vitals</h2>
        <p class="text-muted">Recent vitals recorded from consultations.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
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
                    @forelse($consultations as $c)
                        @php $v = $c->vitals ?? []; @endphp
                        <tr>
                            <td>{{ optional($c->patient)->first_name }} {{ optional($c->patient)->last_name }}</td>
                            <td>{{ $v['bp'] ?? '—' }}</td>
                            <td>{{ $v['temp'] ?? '—' }}</td>
                            <td>{{ $v['pulse'] ?? '—' }}</td>
                            <td>{{ $c->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No vitals recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $consultations->links() }}
        </div>
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
