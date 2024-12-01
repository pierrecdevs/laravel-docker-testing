<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::get();
        if (count($products)) {
            return ProductResource::collection($products);
        } else {
            return response()->json(['message' => 'No products'], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'required',
            'price' => 'required|integer',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'message' => 'invalid payload',
                'error' => $validator->messages()
            ], 422);
        } else {
            $product = Product::create([
                'name' => $request->name,
                'price' => $request->price,
                'description' => $request->description,
                'image' => $request->image,
                'permissions' => $request->permissions,
            ]);

            return response()->json([
                'message' => 'created',
                'data' => new ProductResource($product),
            ], 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'required',
            'price' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'invalid payload',
                'error' => $validator->messages()
            ], 422);
        } else {
            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'image' => $request->image,
                'permissions' => $request->permissions,
            ]);

            return response()->json([
                'message' => 'updated',
                'data' => new ProductResource($product),
            ], 204);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return response()->json([
                'message' => 'deleted'
            ], 204);
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'error',
                'error' => $ex
            ], 500);
        }
    }
}
