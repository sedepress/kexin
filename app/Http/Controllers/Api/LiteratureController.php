<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Literature;
use App\Transformers\LiteratureTransformer;
use App\Http\Requests\Api\LiteratureRequest;

class LiteratureController extends Controller
{
    public function store(LiteratureRequest $request, Literature $literature)
    {
        $literature->fill($request->all());
        $literature->save();

        return $this->response->item($literature, new LiteratureTransformer())
            ->setStatusCode(201);
    }

    public function update(LiteratureRequest $request, Literature $literature)
    {
        $literature->update($request->all());
        return $this->response->item($literature, new LiteratureTransformer());
    }

    public function destroy(Literature $literature)
    {
        $literature->delete();
        return $this->response->noContent();
    }
}
