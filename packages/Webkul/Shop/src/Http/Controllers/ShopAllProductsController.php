<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Product\Repositories\ProductRepository;

class ShopAllProductsController extends Controller
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index(Request $request)
    {
        // Get all filters (sort, limit, category, etc.)
        $filters = $request->all();

        // Fetch all products using ProductRepository
        $products = $this->productRepository->getAll($filters);

        return view('shop::products.index', [
            'products' => $products,
        ]);
    }
}
