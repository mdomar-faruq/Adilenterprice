<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Eager load 'unit' to avoid N+1 query issues
            $data = Product::with('unit')->select('products.*');

            return DataTables::of($data)
                ->addIndexColumn()

                // 1. Logic for displaying the Unit Name
                ->editColumn('unit', function ($row) {
                    return $row->unit
                        ? '<span class="badge bg-light text-secondary border">' . $row->unit->name . '</span>'
                        : '<span class="text-muted small">N/A</span>';
                })

                // 2. FIX: Enable SEARCHING for the Unit Name
                ->filterColumn('unit', function ($query, $keyword) {
                    $query->whereHas('unit', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

                // 3. FIX: Enable SORTING for the Unit Name
                ->orderColumn('unit', function ($query, $order) {
                    $query->join('units', 'products.unit_id', '=', 'units.id')
                        ->orderBy('units.name', $order);
                })

                ->editColumn('purchase_price', function ($row) {
                    return 'TK ' . number_format($row->purchase_price, 2);
                })
                ->editColumn('sale_price', function ($row) {
                    return 'Tk ' . number_format($row->sale_price, 2);
                })

                ->addColumn('action', function ($row) {
                    // Edit Button
                    $btn = '<button class="btn btn-light text-primary rounded-circle me-2 shadow-sm" 
                                onclick=\'fillEditModal(' . json_encode($row) . ')\' 
                                data-bs-toggle="modal" data-bs-target="#editModal" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </button>';

                    // Delete Button (Form-based)
                    $btn .= '<form action="' . route('products.destroy', $row->id) . '" method="POST" class="d-inline" id="delete-form-' . $row->id . '">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="button" class="btn btn-light text-danger rounded-circle shadow-sm" 
                                        onclick="confirmDelete(' . $row->id . ')" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>';

                    return $btn;
                })
                ->rawColumns(['unit', 'action'])
                ->make(true);
        }

        $units = Unit::where('valid', 1)->get();
        return view('products.index', compact('units'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'percent' => 'required|numeric|min:0',
            'sale_price'     => 'required|numeric|min:0',
        ]);

        // --- Cleaning Logic ---
        // 1. trim() removes spaces from the very start and end
        // 2. preg_replace removes double/triple spaces inside the name
        // 3. ucwords() makes it "Look Professional" by capitalizing each word
        $cleanName = preg_replace('/\s+/', ' ', trim($request->name));
        $validated['name'] = ucwords(strtolower($cleanName));

        $validated['user_id'] = Auth::id();
        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'percent' => 'required|numeric|min:0',
            'sale_price'     => 'required|numeric|min:0',
        ]);

        // --- Cleaning Logic ---
        // 1. trim() removes spaces from the very start and end
        // 2. preg_replace removes double/triple spaces inside the name
        // 3. ucwords() makes it "Look Professional" by capitalizing each word
        $cleanName = preg_replace('/\s+/', ' ', trim($request->name));
        $validated['name'] = ucwords(strtolower($cleanName));

        $validated['user_id'] = Auth::id();
        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->back()->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: This product might be linked to other data.');
        }
    }

    public function export()
    {
        $fileName = 'products_export_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['#Code', 'Name', 'Purchase Price', 'Margin %', 'Sale Price'];

        // Using stream() with a cursor prevents memory crashes
        return response()->stream(function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            // cursor() fetches one row at a time from the database
            foreach (Product::with('unit')->cursor() as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->unit->name ?? '1', // default pcs
                    $product->purchase_price,
                    $product->percent . '%',
                    $product->sale_price
                ]);
            }
            fclose($file);
        }, 200, $headers);
    }
}
