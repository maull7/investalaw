<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RegulationCategory;
use App\Repositories\RegulationCategoryRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegulationCategoryUserController extends Controller
{
    public function __construct(
        private readonly RegulationCategoryRepository $categoryRepository
    ) {}
    public function index()
    {
        $categories = $this->categoryRepository->all();

        return view('regulation-categories.user.index', compact('categories'));
    }
    public function show(RegulationCategory $regulationCategory): View
    {

        $regulationCategory->load(['files', 'subCategories', 'regulations.type', 'regulations.documents']);
        $category = $regulationCategory;
        return view('regulation-categories.user.show', compact('category'));
    }
}
