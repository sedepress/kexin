<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\DeclarationCategory;
use App\Transformers\DeclarationCategoryTransformer;

class DeclarationCategoryController extends Controller
{
    public function index()
    {
        return $this->response->collection(DeclarationCategory::all(), new DeclarationCategoryTransformer());
    }

    public function store(Request $request, DeclarationCategory $declaration_category)
    {
        $declaration_category->fill($request->all());
        $declaration_category->save();

        return;
    }
}
