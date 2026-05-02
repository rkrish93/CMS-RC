<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Exception;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless($request->user()?->can('patients-view'), 403);

        $search = $request->search;

        $patients = Patient::when($search, function ($query, $search) {
            $query->where('patient_code', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('nic', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");

        })->latest()->get();
        return view('admin.patients.index', compact('patients','search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('patients-create'), 403);

        return view('admin.patients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless($request->user()?->can('patients-create'), 403);

        try {
        // VALIDATION (Clinic Standard)
        $request->validate([

        'title' => 'nullable|max:10',

        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',

        'gender' => 'required|in:Male,Female,Other',

        'dob' => 'required|date|before:today',
        'age' => 'nullable|integer|min:0|max:120',

        'nic' => 'required|string|max:12|unique:patients,nic',

        'phone' => 'required|string|max:15',

        'blood_group' => 'nullable|in:A+,A-,B+,B-,O+,O-,AB+,AB-',

        'patient_type' => 'nullable|in:OPD,Clinic,Emergency',

        'address' => 'nullable|string|max:255',

        'province' => 'nullable|string|max:100',
        'district' => 'nullable|string|max:100',
        'gs_division' => 'nullable|string|max:100',

        'emergency_name' => 'nullable|string|max:100',
        'emergency_phone' => 'nullable|string|max:15',
        'relationship' => 'nullable|string|max:50',

        'allergies' => 'nullable|string',
        'chronic_conditions' => 'nullable|string',

    ]);

    // AUTO PATIENT CODE GENERATE
    $lastPatient = Patient::latest('id')->first();

    if ($lastPatient && $lastPatient->patient_code) {

        $num = (int) substr($lastPatient->patient_code, 3);
        $num++;

    } else {
        $num = 1;
    }

    $patientCode = 'CL-' . str_pad($num, 5, '0', STR_PAD_LEFT);

    //  STORE DATA
    Patient::create([

        'patient_code' => $patientCode,

        'title' => $request->title,
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'gender' => $request->gender,
        'dob' => $request->dob,
        'age' => $request->age,
        'nic' => $request->nic,
        'phone' => $request->phone,
        'blood_group' => $request->blood_group,
        'patient_type' => $request->patient_type,
        'address' => $request->address,
        'province' => $request->province,
        'district' => $request->district,
        'gs_division' => $request->gs_division,
        'emergency_name' => $request->emergency_name,
        'emergency_phone' => $request->emergency_phone,
        'relationship' => $request->relationship,
        'allergies' => $request->allergies,
        'chronic_conditions' => $request->chronic_conditions,

    ]);

    return redirect()->route('patients.index')
            ->with('success','Patient Registered Successfully');

    } catch (Exception $e) {
        dd($e->getMessage());
    }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort_unless(auth()->user()?->can('patients-view'), 403);

        $patient = Patient::findOrFail($id);
    return view('admin.patients.view', compact('patient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort_unless(auth()->user()?->can('patients-edit'), 403);

        $patient = Patient::findOrFail($id);
        return view('admin.patients.edit', compact('patient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         abort_unless($request->user()?->can('patients-edit'), 403);

         $patient = Patient::findOrFail($id);

    $request->validate([

        'title' => 'nullable|max:10',

        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',

        'gender' => 'required|in:Male,Female,Other',

        'dob' => 'required|date|before:today',
        'age' => 'nullable|integer|min:0|max:120',

        'nic' => 'required|string|max:12|unique:patients,nic,' . $patient->id,

        'phone' => 'required|string|max:15',

        'blood_group' => 'nullable|in:A+,A-,B+,B-,O+,O-,AB+,AB-',

        'patient_type' => 'nullable|in:OPD,Clinic,Emergency',

        'address' => 'nullable|string|max:255',

        'province' => 'nullable|string|max:100',
        'district' => 'nullable|string|max:100',
        'gs_division' => 'nullable|string|max:100',

        'emergency_name' => 'nullable|string|max:100',
        'emergency_phone' => 'nullable|string|max:15',
        'relationship' => 'nullable|string|max:50',

        'allergies' => 'nullable|string',
        'chronic_conditions' => 'nullable|string',

    ]);

    $patient->update($request->all());

    return redirect()->route('patients.index')
            ->with('success','Patient Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        abort_unless(auth()->user()?->can('patients-delete'), 403);

        $patient = Patient::findOrFail($id);
        $patient->delete();

        return redirect()->route('patients.index');
    }

    public function ajaxSearch(Request $request)
{
    abort_unless($request->user()?->can('patients-view'), 403);

    $search = $request->search;

    $patients = Patient::where('patient_code','like',"%{$search}%")
        ->orWhere('first_name','like',"%{$search}%")
        ->orWhere('last_name','like',"%{$search}%")
        ->orWhere('nic','like',"%{$search}%")
        ->orWhere('phone','like',"%{$search}%")
        ->latest()
        ->limit(20)
        ->get();

    return response()->json($patients);
}
}
