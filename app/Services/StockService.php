<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockService
{
 /**
  * Record a stock movement and update the product balance.
  * Use this for: sale, return, purchase, adjustment, initial.
  */
 public static function updateStock($productId, $quantity, $type, $referenceNo, $remarks = null)
 {
  return DB::transaction(function () use ($productId, $quantity, $type, $referenceNo, $remarks) {
   // Lock the product row to prevent race conditions (very important)
   $product = Product::lockForUpdate()->findOrFail($productId);

   $balanceBefore = (float) ($product->stock ?? 0);
   $qty = (float) $quantity;

   // Determine if we add or subtract
   // Purchases and Returns increase stock; Sales and Adjustments decrease it.
   if (in_array($type, ['purchase', 'return', 'initial'])) {
    $product->increment('stock', $qty);
    $balanceAfter = $balanceBefore + $qty;
   } else {
    // This handles 'sale' and 'adjustment'
    $product->decrement('stock', $qty);
    $balanceAfter = $balanceBefore - $qty;
   }

   // Log the movement for Month-End Audit
   // Using StockMovement instead of stock_movements (Standard Laravel naming)
   return StockMovement::create([
    'product_id'     => $productId,
    'type'           => $type,
    'quantity'       => $qty,
    'balance_before' => $balanceBefore,
    'balance_after'  => $balanceAfter,
    'reference_no'   => $referenceNo,
    'remarks'        => $remarks,
    'user_id'        => Auth::id() ?? 1,
   ]);
  });
 }

 /**
  * Specifically for Deletions: Reverses the original effect.
  * If you delete a Sale, stock goes BACK UP.
  * If you delete a Purchase, stock goes BACK DOWN.
  */
 public static function reverseStock($productId, $quantity, $originalType, $referenceNo)
 {
  return DB::transaction(function () use ($productId, $quantity, $originalType, $referenceNo) {
   $product = Product::lockForUpdate()->findOrFail($productId);

   $balanceBefore = (float) $product->stock;
   $qty = (float) $quantity;

   // REVERSE LOGIC: 
   // If original was an addition (purchase/return), subtraction is needed now.
   if (in_array($originalType, ['purchase', 'return', 'initial'])) {
    $product->decrement('stock', $qty);
    $balanceAfter = $balanceBefore - $qty;
   } else {
    // If original was a reduction (sale), addition is needed now.
    $product->increment('stock', $qty);
    $balanceAfter = $balanceBefore + $qty;
   }

   return StockMovement::create([
    'product_id'     => $productId,
    'type'           => 'adjustment', // Reversals are usually categorized as adjustments
    'quantity'       => $qty,
    'balance_before' => $balanceBefore,
    'balance_after'  => $balanceAfter,
    'reference_no'   => $referenceNo,
    'remarks'        => "Reversal of $originalType: Record Deleted",
    'user_id'        => Auth::id() ?? 1,
   ]);
  });
 }
}
