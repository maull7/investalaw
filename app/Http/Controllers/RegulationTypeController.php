<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegulationType\StoreRegulationTypeRequest;
use App\Http\Requests\RegulationType\UpdateRegulationTypeRequest;
use App\Models\RegulationType;
use App\Models\UserActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegulationTypeController extends Controller
{
    public function index(): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_types'), 403);

        $types = RegulationType::withCount('regulations')->orderBy('level')->get();

        return view('regulation-types.index', compact('types'));
    }

    public function create(): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_types'), 403);

        return view('regulation-types.create');
    }

    public function store(StoreRegulationTypeRequest $request): RedirectResponse
    {
        $type = RegulationType::create($request->validated());

        UserActivityLog::log('created', RegulationType::class, $type->id, "Menambahkan jenis regulasi {$type->name}");

        return redirect()->route('regulation-types.index')
            ->with('success', 'Jenis regulasi berhasil ditambahkan.');
    }

    public function edit(RegulationType $regulationType): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_types'), 403);

        return view('regulation-types.edit', compact('regulationType'));
    }

    public function update(UpdateRegulationTypeRequest $request, RegulationType $regulationType): RedirectResponse
    {
        $regulationType->update($request->validated());

        UserActivityLog::log('updated', RegulationType::class, $regulationType->id, "Memperbarui jenis regulasi {$regulationType->name}");

        return redirect()->route('regulation-types.index')
            ->with('success', 'Jenis regulasi berhasil diperbarui.');
    }

    public function destroy(RegulationType $regulationType): RedirectResponse
    {
        abort_unless(request()->user()->hasPermission('manage_types'), 403);

        if ($regulationType->regulations()->exists()) {
            return redirect()->route('regulation-types.index')
                ->with('error', 'Tidak dapat menghapus jenis regulasi yang masih digunakan oleh regulasi.');
        }

        $name = $regulationType->name;
        $regulationType->delete();

        UserActivityLog::log('deleted', RegulationType::class, null, "Menghapus jenis regulasi {$name}");

        return redirect()->route('regulation-types.index')
            ->with('success', 'Jenis regulasi berhasil dihapus.');
    }
}
