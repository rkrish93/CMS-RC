@extends('layouts.app')

@section('title', 'Clinic Settings')

@section('page-actions')
<a href="{{ route('dashboard') }}" class="btn btn-light">
    <i class="mdi mdi-arrow-left me-1"></i> Dashboard
</a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success border border-success-subtle mb-4">
        <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger border border-danger-subtle mb-4">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-8 col-xl-7">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div class="p-3 rounded-3 bg-primary-subtle text-primary fs-3">
                        <i class="mdi mdi-clock-outline"></i>
                    </div>
                    <div>
                        <h4 class="card-title fw-bold mb-1 text-dark">Clinic Appointment Hours & Slot Settings</h4>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label for="clinic_open_time" class="form-label fw-bold text-dark fs-14">
                                <i class="mdi mdi-clock-start me-1 text-primary"></i> Clinic Opening Time
                            </label>
                            <input
                                type="time"
                                id="clinic_open_time"
                                name="clinic_open_time"
                                class="form-control form-control-lg fs-15 rounded-3 @error('clinic_open_time') is-invalid @enderror"
                                value="{{ old('clinic_open_time', $clinicOpenTime) }}"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label for="clinic_close_time" class="form-label fw-bold text-dark fs-14">
                                <i class="mdi mdi-clock-end me-1 text-danger"></i> Clinic Closing Time
                            </label>
                            <input
                                type="time"
                                id="clinic_close_time"
                                name="clinic_close_time"
                                class="form-control form-control-lg fs-15 rounded-3 @error('clinic_close_time') is-invalid @enderror"
                                value="{{ old('clinic_close_time', $clinicCloseTime) }}"
                                required>
                        </div>

                        <div class="col-md-12">
                            <label for="slot_duration_minutes" class="form-label fw-bold text-dark fs-14">
                                <i class="mdi mdi-timer-outline me-1 text-purple"></i> Slot Duration (Minutes)
                            </label>
                            <div class="input-group input-group-lg">
                                <input
                                    type="number"
                                    id="slot_duration_minutes"
                                    name="slot_duration_minutes"
                                    class="form-control fs-15 rounded-start-3 @error('slot_duration_minutes') is-invalid @enderror"
                                    value="{{ old('slot_duration_minutes', $slotDurationMinutes) }}"
                                    min="5"
                                    max="120"
                                    required>
                                <span class="input-group-text bg-light text-dark fw-semibold rounded-end-3 fs-14">minutes</span>
                            </div>
                        </div>
                    </div>

                  

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-light px-4 py-2 rounded-3 fw-semibold">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm">
                            <i class="mdi mdi-content-save-outline fs-5"></i>
                            <span>Save Clinic Settings</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
