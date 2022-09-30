<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::with('products')->orderBy('name')->get(); // ASC by default
       
        return response() -> json([
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:80|unique:categories,name',  
        ]);
        $category = new Category();
        // $category->name = $request->name;
        $category->name = $validated['name'];
        $category->save();

        // return 'well done!';
        return response()->json([
            'category' => $category,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $category = Category::findOrFail($id);
        $category->load('products'); 
        
        return response()->json([
          'id' => $id,
          'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:80|unique:categories,name,' . $category->id,
        ]);
        $category->name = $validated['name'];
        $category->save();
        return response()->json([
            'success' => true,
            'category' => $category,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json([
            'success' => true,
        ]);
    }
}
