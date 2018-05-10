<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function update(Request $request, Category $category)
    {
        $category->name = $request->name;
        $category->update();

        return $this->response->noContent();
    }
}
