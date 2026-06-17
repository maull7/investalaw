<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegulationCategory\StoreRegulationCategoryRequest;
use App\Http\Requests\RegulationCategory\UpdateRegulationCategoryRequest;
use App\Models\CategoryFile;
use App\Models\RegulationCategory;
use App\Models\SubCategory;
use App\Repositories\RegulationCategoryRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegulationCategoryController extends Controller
{
    public function __construct(
        private readonly RegulationCategoryRepository $categoryRepository
    ) {}

    public function index(): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_categories'), 403);

        $categories = $this->categoryRepository->all();

        return view('regulation-categories.index', compact('categories'));
    }

    public function create(): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_categories'), 403);

        return view('regulation-categories.create');
    }

    public function store(StoreRegulationCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $category = $this->categoryRepository->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['sub_categories'])) {
            $subNames = array_filter($validated['sub_categories'], fn ($name) => ! empty(trim($name)));
            foreach ($subNames as $name) {
                SubCategory::create([
                    'category_id' => $category->id,
                    'name' => trim($name),
                ]);
            }
        }

        return redirect()->route('regulation-categories.show', $category)
            ->with('success', 'Category berhasil ditambahkan.');
    }

    public function show(RegulationCategory $regulationCategory): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_categories'), 403);

        $regulationCategory->load(['files', 'subCategories', 'regulations.type', 'regulations.documents']);
        $category = $regulationCategory;

        return view('regulation-categories.show', compact('category'));
    }

    public function edit(RegulationCategory $regulationCategory): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_categories'), 403);

        return view('regulation-categories.edit', compact('regulationCategory'));
    }

    public function update(UpdateRegulationCategoryRequest $request, RegulationCategory $regulationCategory): RedirectResponse
    {
        $this->categoryRepository->update($regulationCategory, $request->validated());

        return redirect()->route('regulation-categories.show', $regulationCategory)
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(RegulationCategory $regulationCategory): RedirectResponse
    {
        abort_unless(request()->user()->isAdmin(), 403);

        foreach ($regulationCategory->files as $file) {
            Storage::disk('public')->delete($file->file_path);
        }

        $this->categoryRepository->delete($regulationCategory);

        return redirect()->route('regulation-categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function uploadFile(Request $request, RegulationCategory $regulationCategory): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('manage_categories'), 403);

        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['file', 'mimes:pdf', 'max:10240'],
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('category-files', 'public');

            CategoryFile::create([
                'category_id' => $regulationCategory->id,
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
            ]);
        }

        return redirect()->route('regulation-categories.show', $regulationCategory)
            ->with('success', count($request->file('files')).' file(s) uploaded successfully.');
    }

    public function deleteFile(CategoryFile $file): RedirectResponse
    {
        abort_unless(request()->user()->hasPermission('manage_categories'), 403);

        $category = $file->category;

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return redirect()->route('regulation-categories.show', $category)
            ->with('success', 'File deleted successfully.');
    }

    public function viewFile(CategoryFile $file): StreamedResponse
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_categories'), 403);

        return Storage::disk('public')->response($file->file_path, null, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function storeSubCategory(Request $request, RegulationCategory $regulationCategory): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('manage_sub_categories'), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        SubCategory::create([
            'category_id' => $regulationCategory->id,
            'name' => $request->input('name'),
        ]);

        return redirect()->route('regulation-categories.show', $regulationCategory)
            ->with('success', 'Sub category berhasil ditambahkan.');
    }

    public function updateSubCategory(Request $request, SubCategory $subCategory): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('manage_sub_categories'), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $subCategory->update(['name' => $request->input('name')]);

        return redirect()->route('regulation-categories.show', $subCategory->category)
            ->with('success', 'Sub category berhasil diperbarui.');
    }

    public function toggleSubCategory(SubCategory $subCategory): RedirectResponse
    {
        abort_unless(request()->user()->hasPermission('manage_sub_categories'), 403);

        $subCategory->update(['is_active' => ! $subCategory->is_active]);

        return redirect()->route('regulation-categories.show', $subCategory->category)
            ->with('success', 'Status sub category berhasil diperbarui.');
    }

    public function destroySubCategory(SubCategory $subCategory): RedirectResponse
    {
        abort_unless(request()->user()->isAdmin(), 403);

        $category = $subCategory->category;
        $subCategory->delete();

        return redirect()->route('regulation-categories.show', $category)
            ->with('success', 'Sub category berhasil dihapus.');
    }
}
