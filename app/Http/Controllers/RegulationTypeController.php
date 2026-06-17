<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegulationType\StoreRegulationTypeRequest;
use App\Http\Requests\RegulationType\UpdateRegulationTypeRequest;
use App\Models\RegulationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegulationTypeController extends Controller
{
    public function index(): View
    {
        $types = RegulationType::withCount('regulations')->orderBy('level')->get();

        return view('regulation-types.index', compact('types'));
    }

    public function create(): View
    {
        return view('regulation-types.create');
    }

    public function store(StoreRegulationTypeRequest $request): RedirectResponse
    {
        RegulationType::create($request->validated());

        return redirect()->route('regulation-types.index')
            ->with('success', 'Jenis regulasi berhasil ditambahkan.');
    }

    public function edit(RegulationType $regulationType): View
    {
        return view('regulation-types.edit', compact('regulationType'));
    }

    public function update(UpdateRegulationTypeRequest $request, RegulationType $regulationType): RedirectResponse
    {
        $regulationType->update($request->validated());

        return redirect()->route('regulation-types.index')
            ->with('success', 'Jenis regulasi berhasil diperbarui.');
    }

    public function destroy(RegulationType $regulationType): RedirectResponse
    {
        if ($regulationType->regulations()->exists()) {
            return redirect()->route('regulation-types.index')
                ->with('error', 'Tidak dapat menghapus jenis regulasi yang masih digunakan oleh regulasi.');
        }

        $regulationType->delete();

        return redirect()->route('regulation-types.index')
            ->with('success', 'Jenis regulasi berhasil dihapus.');
    }
}
