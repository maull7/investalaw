<?php

namespace App\Http\Controllers;

use App\Models\RegulationCategory;
use App\Models\SubCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubCategoryController extends Controller
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

        return view('sub-categories.index', compact('subCategories', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'category_id' => ['required', 'exists:regulation_categories,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        SubCategory::create([
            'category_id' => $request->input('category_id'),
            'name' => $request->input('name'),
        ]);

        return redirect()->route('sub-categories.index')
            ->with('success', 'Sub category berhasil ditambahkan.');
    }

    public function update(Request $request, SubCategory $subCategory): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $subCategory->update(['name' => $request->input('name')]);

        return redirect()->route('sub-categories.index')
            ->with('success', 'Sub category berhasil diperbarui.');
    }

    public function toggle(SubCategory $subCategory): RedirectResponse
    {
        $subCategory->update(['is_active' => ! $subCategory->is_active]);

        return redirect()->route('sub-categories.index')
            ->with('success', 'Status sub category berhasil diperbarui.');
    }

    public function destroy(SubCategory $subCategory): RedirectResponse
    {
        $subCategory->delete();

        return redirect()->route('sub-categories.index')
            ->with('success', 'Sub category berhasil dihapus.');
    }
}
