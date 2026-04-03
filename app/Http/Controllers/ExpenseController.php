<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Expense::with('category')->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('category_name', fn($row) => $row->category->name)
                ->editColumn('amount', fn($row) =>  number_format($row->amount, 2))
                ->editColumn('expense_date', fn($row) => date('d M, Y', strtotime($row->expense_date)))
                ->addColumn('action', function ($row) {
                    return '
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-light text-danger deleteBtn" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>
                </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        // Calculate total for current month
        $monthlyTotal = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');
        $categories = ExpenseCategories::all();
        return view('expenses.index', compact('categories', 'monthlyTotal'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $expense_categories = ExpenseCategories::all();
        return view('expenses.create', compact('expense_categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount'              => 'required|numeric|min:0.01',
            'expense_date'        => 'required|date',
            'payment_method'      => 'required|string',
            'note'                => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // 2. Create Expense Record
            $expense = Expense::create([
                'expense_category_id' => $request->expense_category_id,
                'amount'              => $request->amount,
                'expense_date'        => $request->expense_date,
                'payment_method'      => $request->payment_method,
                'note'                => $request->note,
                'user_id'             => Auth::id(), // Track who recorded the expense
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expense of TK' . number_format($expense->amount, 2) . ' recorded successfully.',
                'data'    => $expense
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record expense: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $expense = Expense::findOrFail($id);
            $expense->delete();

            return response()->json([
                'success' => true,
                'message' => 'Expense record deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: Could not delete the record.'
            ], 500);
        }
    }
}
