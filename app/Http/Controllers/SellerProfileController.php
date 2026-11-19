<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class SellerProfileController extends Controller
{
    public function show(User $seller): View
    {
        abort_if($seller->isAdmin(), 404);

        $seller->loadCount([
            'products as active_products_count' => fn ($query) => $query->purchasable(),
            'reviewsReceived',
        ]);
        $seller->loadAvg('reviewsReceived', 'rating');

        $products = $seller->products()
            ->purchasable()
            ->with('category')
            ->latest()
            ->paginate(12);

        return view('sellers.show', compact('seller', 'products'));
    }
}
