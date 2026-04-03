<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PurchasePayment;
use App\Models\purchases;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Using Eloquent query instead of ->get() for better memory management with DataTables
            $data = Company::select(['id', 'name', 'email', 'phone', 'address', 'opening_balance'])->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('ledger', function ($row) {
                    // This builds the button for the 'ledger' column in JS
                    return '<a href="' . route('companies.ledger', $row->id) . '" class="btn btn-sm btn-outline-info rounded-pill px-3">
                            <i class="bi bi-journal-text me-1"></i> Ledger
                        </a>';
                })
                ->addColumn('action', function ($row) {
                    return '
        <div class="d-flex align-items-center justify-content-center gap-1">
            <button class="btn btn-light text-primary rounded-circle me-2 editBtn" 
                data-id="' . $row->id . '" 
                data-name="' . $row->name . '" 
                data-email="' . $row->email . '" 
                data-phone="' . $row->phone . '" 
                data-address="' . $row->address . '"
                data-opening_balance="' . $row->opening_balance . '"
                title="Edit Company">
                <i class="bi bi-pencil-square"></i>
            </button>

            <form action="' . route('companies.destroy', $row->id) . '" method="POST" class="d-inline">
                ' . csrf_field() . ' ' . method_field("DELETE") . '
                <button class="btn btn-light text-danger rounded-circle" 
                    type="submit" 
                    onclick="return confirm(\'Are you sure?\')"
                    title="Delete Company">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>';
                })
                ->rawColumns(['ledger', 'action'])
                ->make(true);
        }
        return view('companies.index');
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
        $request->validate(['name' => 'required|string|max:255']);

        try {
            Company::updateOrCreate(
                ['id' => $request->company_id],
                [
                    'name'    => $request->name,
                    'email'   => $request->email,
                    'phone'   => $request->phone,
                    'address' => $request->address,
                    'opening_balance' => $request->opening_balance,
                    'is_active' => 1,
                ]
            );
            return redirect()->back()->with('success', 'Company saved successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: Could not save company details.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        try {
            $company->delete();
            return redirect()->back()->with('success', 'Company deleted successfully!');
        } catch (\Exception $e) {
            // This catches errors if the company is linked to Purchases (Foreign Key Constraint)
            return redirect()->back()->with('error', 'Error: This company is linked to purchase records and cannot be deleted.');
        }
    }

    public function ledger($id)
    {
        $company = Company::findOrFail($id);

        // 1. Get Purchases (Increases what you owe)
        $purchases = purchases::where('company_id', $id)
            ->select(
                'id',
                'purchase_date as date',
                'purchase_no as reference',
                DB::raw('0 as debit'),
                'total_amount as credit',
                DB::raw('"Purchase" as type')
            )->get();

        // 2. Get Purchase Payments (Decreases what you owe)
        $payments = PurchasePayment::where('company_id', $id)
            ->select(
                'id',
                'payment_date as date',
                DB::raw('NULL as reference'),
                'amount as debit',
                DB::raw('0 as credit'),
                DB::raw('"Payment" as type')
            )->get();

        // 3. Merge and Sort by Date
        $ledger = $purchases->concat($payments)->sortBy('date');

        // 4. Calculate Running Balance
        // Formula: Previous Balance + Credit (Purchase) - Debit (Payment)
        $runningBalance = $company->opening_balance;

        $ledger->transform(function ($item) use (&$runningBalance) {
            $runningBalance += ($item->credit - $item->debit);
            $item->balance = $runningBalance;
            return $item;
        });

        return view('companies.ledger', compact('company', 'ledger'));
    }
}
