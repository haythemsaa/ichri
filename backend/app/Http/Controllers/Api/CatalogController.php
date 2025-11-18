<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class CatalogController extends Controller
{
    /**
     * Get all categories
     */
    public function categories()
    {
        $categories = Category::active()
            ->with('children')
            ->roots()
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get all brands
     */
    public function brands()
    {
        $brands = Brand::active()
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    /**
     * Get all products with filtering and search
     */
    public function products(Request $request)
    {
        $products = QueryBuilder::for(Product::class)
            ->allowedFilters([
                'name',
                'sku',
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('brand_id'),
                AllowedFilter::exact('is_on_sale'),
                AllowedFilter::exact('is_featured'),
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['name', 'unit_price', 'created_at', 'views_count', 'orders_count'])
            ->with(['category', 'brand', 'images'])
            ->active()
            ->inStock()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get product by ID or slug
     */
    public function show($identifier)
    {
        $product = Product::with(['category', 'brand', 'images', 'reviews'])
            ->where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->active()
            ->firstOrFail();

        // Increment views
        $product->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $term = $request->input('q');

        if (!$term || strlen($term) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Le terme de recherche doit contenir au moins 2 caractères'
            ], 422);
        }

        $products = Product::active()
            ->inStock()
            ->search($term)
            ->with(['category', 'brand', 'images'])
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count()
        ]);
    }

    /**
     * Get featured products
     */
    public function featured()
    {
        $products = Product::active()
            ->inStock()
            ->featured()
            ->with(['category', 'brand', 'images'])
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get products on sale
     */
    public function onSale()
    {
        $products = Product::active()
            ->inStock()
            ->onSale()
            ->with(['category', 'brand', 'images'])
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get bestsellers
     */
    public function bestsellers()
    {
        $products = Product::active()
            ->inStock()
            ->where('is_bestseller', true)
            ->orderByDesc('orders_count')
            ->with(['category', 'brand', 'images'])
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
