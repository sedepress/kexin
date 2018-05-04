<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\DeclarationCategoryRequest;
use App\Models\DeclarationCategory;
use App\Transformers\DeclarationCategoryTransformer;

class DeclarationCategoryController extends Controller
{
    public function index()
    {
        return $this->response->collection(DeclarationCategory::all(), new DeclarationCategoryTransformer());
    }

    public function store(DeclarationCategoryRequest $request, DeclarationCategory $declaration_category)
    {
        $declaration_category->fill($request->all());
        $declaration_category->save();

        return $this->response->item($declaration_category, new DeclarationCategoryTransformer())
            ->setStatusCode(201);
    }

    public function update(DeclarationCategoryRequest $request, DeclarationCategory $declaration_category)
    {
        $declaration_category->update($request->all());

        return $this->response->item($declaration_category, new DeclarationCategoryTransformer());
    }

    public function delete(DeclarationCategory $declaration_category)
    {
        $declaration_category->delete();

        return $this->response->noContent();
    }
}
