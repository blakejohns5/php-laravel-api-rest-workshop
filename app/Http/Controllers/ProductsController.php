<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    
    public function store(Request $request) 
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'sometimes|nullable|string|min:5',
            'price_in_cents' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id'
        ]);
        $product = new Product();
        $product->name = $validated['name'];
        $product->description = $validated['description'] ?? null;
        $product->price_in_cents = $validated['price_in_cents'];
        $product->category_id = $validated['category_id'];
        $product->save();

        $product->load('category:id,name');
        
        return response()->json([
            'success' => true,
            // 'validated' => $validated,
            'product' => $product,
        ]);
    }
}
