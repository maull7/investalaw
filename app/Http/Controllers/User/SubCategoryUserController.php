<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RegulationCategory;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubCategoryUserController extends Controller
{
    public function index(Request $request): View
    {
        $query = SubCategory::with('category')->orderBy('name');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->input('search').'%');
        }

        $subCategories = $query->paginate(20)->withQueryString();
        $categories = RegulationCategory::orderBy('name')->get();

        return view('sub-categories.user.index', compact('subCategories', 'categories'));
    }

    public function show(SubCategory $subCategory): View
    {
        $subCategory->load('category');
        $regulations = $subCategory->regulations()->latest('effective_date')->paginate(10)->withQueryString();

        return view('sub-categories.user.show', compact('subCategory', 'regulations'));
    }
}
