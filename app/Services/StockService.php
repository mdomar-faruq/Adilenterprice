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

 /**
  * Specifically for updating the damage_stock column
  */
 public static function updateDamageStock($productId, $quantity, $isIncrement, $referenceNo)
 {
  return DB::transaction(function () use ($productId, $quantity, $isIncrement, $referenceNo) {
   $product = Product::lockForUpdate()->findOrFail($productId);

   if ($isIncrement) {
    $product->increment('damage_stock', $quantity);
   } else {
    $product->decrement('damage_stock', $quantity);
   }

   // Log this movement as a 'damage_adjustment'
   return StockMovement::create([
    'product_id'     => $productId,
    'type'           => 'damage_adjustment',
    'quantity'       => $quantity,
    'balance_after'  => $product->damage_stock, // Track current damage balance
    'reference_no'   => $referenceNo,
    'remarks'        => $isIncrement ? "Damage Added" : "Damage Removed/Returned",
    'user_id'        => Auth::id() ?? 1,
   ]);
  });
 }

 /**
  * Specifically for updating the stock column, items that are broken.
  */
 public static function recordAccidentalDamage($productId, $quantity, $ref, $remarks = "Accidental Damage")
 {
  return DB::transaction(function () use ($productId, $quantity, $ref, $remarks) {
   $product = Product::lockForUpdate()->findOrFail($productId);
   // $ref = 'ADJ-' . time(); // Unique Reference

   // 1. Remove from Sellable Stock
   // This uses your existing decrement logic
   self::updateStock($productId, $quantity, 'adjustment', $ref, $remarks);

   // 2. Add to Damage Stock
   // true = increment
   self::updateDamageStock($productId, $quantity, true, $ref);

   return true;
  });
 }
}
