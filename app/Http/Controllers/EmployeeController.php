<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Employee::latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('salary', fn($row) => 'TK ' . number_format($row->salary, 2))
                ->addColumn('action', function ($row) {
                    return '
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-light text-primary editBtn" data-id="' . $row->id . '"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-light text-danger deleteBtn" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>
                </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('employees.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $id = $request->employee_id; // Get ID from hidden input

        // 1. Smart Validation
        $request->validate([
            'name'  => 'required|string|max:255',
            // 'unique:table,column,except_id'
            'phone' => 'required|string|unique:employees,phone,' . $id,
            'email' => 'nullable|email|unique:employees,email,' . $id,
        ], [
            // Custom error messages
            'phone.unique' => 'This phone number is already registered to another employee.',
            'email.unique' => 'This email address is already in use.',
        ]);

        try {
            $data = [
                'name'         => $request->name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'designation'  => $request->designation,
                'salary'       => $request->salary,
                'joining_date' => $request->joining_date,
                'nid_number'   => $request->nid_number,
                'address'      => $request->address,
            ];

            Employee::updateOrCreate(['id' => $id], $data);

            return response()->json([
                'success' => true,
                'message' => 'Employee details saved successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // This MUST return JSON for the frontend to read it
        return response()->json($employee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // 1. Find the employee
            $employee = Employee::findOrFail($id);

            // 2. Perform the delete
            // If you are using SoftDeletes, this will just set the deleted_at column
            $employee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Employee "' . $employee->name . '" has been removed from the system.'
            ]);
        } catch (\Exception $e) {
            // Handle cases where the employee might be linked to other data (like Payroll)
            return response()->json([
                'success' => false,
                'message' => 'Could not delete employee. They may be linked to existing payroll records.'
            ], 500);
        }
    }
}
