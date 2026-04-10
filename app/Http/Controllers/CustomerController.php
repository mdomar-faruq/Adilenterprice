<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SalesDueCustomer;
use App\Models\SalesReturn;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // List all customers via AJAX
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $customers = Customer::latest()->get();
            return DataTables::of($customers)
                ->addIndexColumn()
                ->addColumn('ledger', function ($row) {
                    // This builds the button for the 'ledger' column in JS
                    return '<a href="' . route('customers.ledger', $row->id) . '" class="btn btn-sm btn-outline-info rounded-pill px-3">
                            <i class="bi bi-journal-text me-1"></i> Ledger
                        </a>';
                })
                ->addColumn('action', function ($row) {
                    return '
                    <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                        <button class="btn btn-sm btn-white text-primary edit-customer" data-id="' . $row->id . '"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-white text-danger delete-customer" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>
                    </div>';
                })
                ->rawColumns(['ledger', 'action', 'status']) // This allows HTML to render
                ->make(true);
        }
        return view('customers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validation (Keep this outside try-catch so Laravel handles it normally)
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $request->customer_id,
            'email' => 'nullable|email',
        ]);

        // Start the Transaction
        DB::beginTransaction();

        try {
            // 2. The Logic
            Customer::updateOrCreate(
                ['id' => $request->customer_id],
                [
                    'name'            => $request->name,
                    'phone'           => $request->phone,
                    'email'           => $request->email,
                    'address'         => $request->address,
                    'credit_limit'    => $request->credit_limit ?? 0,
                    'opening_balance' => $request->opening_balance ?? 0,
                    'is_active'       => $request->has('is_active') ? 1 : 0,
                ]
            );

            // 3. Commit the changes
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Customer processed successfully!'
            ]);
        } catch (\Exception $e) {
            // 4. Rollback if anything fails
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return response()->json(Customer::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $customer = Customer::findOrFail($id);

            // Check if customer has transactions before deleting
            // if ($customer->sales()->exists()) {
            //     return response()->json(['status' => 'error', 'message' => 'Cannot delete customer with existing sales.'], 422);
            // }

            $customer->delete();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Customer deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'System error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ledger($id)
    {
        $customer = Customer::findOrFail($id);

        // 1. Get Invoices (Debits)
        $invoices = SalesDueCustomer::where('customer_id', $id)
            ->with('sale')
            ->get()
            ->map(function ($item) {
                return (object)[
                    'date'      => $item->created_at,
                    'type'      => 'Invoice',
                    'reference' => '#' . $item->sale->invoice_no,
                    'debit'     => $item->due_amount,
                    'credit'    => 0,
                ];
            });

        // 2. Get Payments (Credits)
        $payments = Payment::where('customer_id', $id)
            ->get()
            ->map(function ($item) {
                return (object)[
                    'date'      => $item->payment_date,
                    'type'      => 'Payment',
                    'reference' => $item->payment_method . ($item->transaction_no ? ' - ' . $item->transaction_no : ''),
                    'debit'     => 0,
                    'credit'    => $item->amount,
                ];
            });

        // 3. Merge, Sort, and Calculate Running Balance
        $merged = $invoices->concat($payments)->sortBy('date');

        $runningBalance = $customer->opening_balance;
        $ledger = $merged->map(function ($row) use (&$runningBalance) {
            $runningBalance += ($row->debit - $row->credit);
            $row->balance = $runningBalance;
            return $row;
        });

        return view('customers.ledger', compact('customer', 'ledger'));
    }
}
