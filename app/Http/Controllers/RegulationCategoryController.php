<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegulationCategory\StoreRegulationCategoryRequest;
use App\Http\Requests\RegulationCategory\UpdateRegulationCategoryRequest;
use App\Models\CategoryFile;
use App\Models\RegulationCategory;
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
        $categories = $this->categoryRepository->all();

        return view('regulation-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('regulation-categories.create');
    }

    public function store(StoreRegulationCategoryRequest $request): RedirectResponse
    {
        $this->categoryRepository->create($request->validated());

        return redirect()->route('regulation-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(RegulationCategory $regulationCategory): View
    {
        $category = $this->categoryRepository->findById($regulationCategory->id);

        return view('regulation-categories.show', compact('category'));
    }

    public function edit(RegulationCategory $regulationCategory): View
    {
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
        foreach ($regulationCategory->files as $file) {
            Storage::disk('public')->delete($file->file_path);
        }

        $this->categoryRepository->delete($regulationCategory);

        return redirect()->route('regulation-categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function uploadFile(Request $request, RegulationCategory $regulationCategory): RedirectResponse
    {
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
        $category = $file->category;

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return redirect()->route('regulation-categories.show', $category)
            ->with('success', 'File deleted successfully.');
    }

    public function viewFile(CategoryFile $file): StreamedResponse
    {
        return Storage::disk('public')->response($file->file_path, null, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline',
        ]);
    }
}
