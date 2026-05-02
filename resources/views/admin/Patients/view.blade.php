@extends('layouts.app')

@section('title', 'Patient Profile')

@section('page-actions')
    <div class="d-flex gap-2">
        @can('patients-edit')
            <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-gradient-info">
                <i class="mdi mdi-pencil me-1"></i> Edit
            </a>
        @endcan
        <a href="{{ route('patients.index') }}" class="btn btn-light">
            <i class="mdi mdi-arrow-left me-1"></i> Back
        </a>
    </div>
@endsection

@section('content')

@php
    $fullName = trim(($patient->title ? $patient->title . ' ' : '') . $patient->first_name . ' ' . $patient->last_name) ?: 'N/A';
    $dob = $patient->dob ? \Illuminate\Support\Carbon::parse($patient->dob)->format('d M Y') : 'N/A';

    $field = function ($value) {
        return filled($value) ? $value : 'N/A';
    };
@endphp

<div class="row">
    <div class="col-lg-4 grid-margin stretch-card">
        <div class="card patient-summary-card">
            <div class="card-body text-center">
                <div class="patient-avatar mb-3">
                    <i class="mdi mdi-account-heart-outline"></i>
                </div>

                <h4 class="card-title mb-1">{{ $fullName }}</h4>
                <p class="text-muted mb-3">{{ $patient->patient_code ?: 'No patient code' }}</p>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <span class="soft-badge">{{ $field($patient->gender) }}</span>
                    <span class="soft-badge">{{ $field($patient->patient_type) }}</span>
                    <span class="soft-badge">{{ $field($patient->blood_group) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Personal Information</h4>

                <div class="detail-grid">
                    <div class="detail-item">
                        <span>Patient Code</span>
                        <strong>{{ $field($patient->patient_code) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Full Name</span>
                        <strong>{{ $fullName }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Gender</span>
                        <strong>{{ $field($patient->gender) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Date of Birth</span>
                        <strong>{{ $dob }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Age</span>
                        <strong>{{ $patient->age ?? 'N/A' }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>NIC</span>
                        <strong>{{ $field($patient->nic) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Contact Information</h4>

                <div class="detail-list">
                    <div class="detail-item">
                        <span>Phone</span>
                        <strong>{{ $field($patient->phone) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Address</span>
                        <strong>{{ $field($patient->address) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Province</span>
                        <strong>{{ $field($patient->province) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>District</span>
                        <strong>{{ $field($patient->district) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>GS Division</span>
                        <strong>{{ $field($patient->gs_division) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Emergency Contact</h4>

                <div class="detail-list">
                    <div class="detail-item">
                        <span>Name</span>
                        <strong>{{ $field($patient->emergency_name) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Phone</span>
                        <strong>{{ $field($patient->emergency_phone) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span>Relationship</span>
                        <strong>{{ $field($patient->relationship) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h4 class="card-title">Medical Information</h4>

        <div class="detail-grid">
            <div class="detail-item">
                <span>Blood Group</span>
                <strong>{{ $field($patient->blood_group) }}</strong>
            </div>
            <div class="detail-item">
                <span>Patient Type</span>
                <strong>{{ $field($patient->patient_type) }}</strong>
            </div>
            <div class="detail-item detail-wide">
                <span>Allergies</span>
                <strong>{{ $field($patient->allergies) }}</strong>
            </div>
            <div class="detail-item detail-wide">
                <span>Chronic Conditions</span>
                <strong>{{ $field($patient->chronic_conditions) }}</strong>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .patient-avatar {
        width: 96px;
        height: 96px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #eff6ff;
        color: #2563eb;
        font-size: 52px;
    }

    .soft-badge {
        display: inline-flex;
        min-height: 28px;
        align-items: center;
        padding: 5px 10px;
        border: 1px solid #dbeafe;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 800;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
    }

    .detail-list {
        display: grid;
        gap: 14px;
    }

    .detail-item {
        padding: 12px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f8fafc;
    }

    .detail-item span {
        display: block;
        margin-bottom: 4px;
        color: #667085;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .detail-item strong {
        color: #152033;
        font-weight: 800;
    }

    .detail-wide {
        grid-column: 1 / -1;
    }
</style>
@endpush
