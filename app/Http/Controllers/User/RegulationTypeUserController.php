<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RegulationType;
use Illuminate\View\View;

class RegulationTypeUserController extends Controller
{
    public function index(): View
    {

        $types = RegulationType::withCount('regulations')->orderBy('level')->get();

        return view('regulation-types.user.index', compact('types'));
    }

    public function show(RegulationType $regulationType): View
    {
        $regulationType->load([
            'regulations.category',
            'regulations.documents',
            'regulations.subCategories',
        ]);
        $regulations = $regulationType->regulations()
            ->with([
                'type',
                'documents',
            ])
            ->latest('effective_date')
            ->paginate(10);

        return view('regulation-types.user.show', compact('regulationType', 'regulations'));
    }
}
