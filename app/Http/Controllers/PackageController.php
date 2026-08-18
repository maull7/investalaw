<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\UserActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    private function parseBenefits(array $lines): array
    {
        return array_values(array_filter(array_map(fn ($line) => trim((string) $line), $lines)));
    }

    public function index(): View
    {
        $packages = Package::orderBy('sort')->orderBy('id')->get();

        return view('packages.index', compact('packages'));
    }

    public function create(): View
    {
        return view('packages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        $package = Package::create([
            ...$validated,
            'benefits' => $this->parseBenefits(explode("\n", (string) ($request->input('benefits') ?? ''))),
            'is_popular' => $request->boolean('is_popular'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        UserActivityLog::log('created', Package::class, $package->id, "Menambahkan paket {$package->name}");

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil ditambahkan.');
    }

    public function edit(Package $package): View
    {
        return view('packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $validated = $this->validateData($request);

        $package->update([
            ...$validated,
            'benefits' => $this->parseBenefits(explode("\n", (string) ($request->input('benefits') ?? ''))),
            'is_popular' => $request->boolean('is_popular'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        UserActivityLog::log('updated', Package::class, $package->id, "Memperbarui paket {$package->name}");

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $name = $package->name;
        $package->delete();

        UserActivityLog::log('deleted', Package::class, null, "Menghapus paket {$name}");

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'string', 'max:255'],
            'price_period' => ['nullable', 'string', 'max:255'],
            'duration_hours' => ['nullable', 'integer', 'min:1'],
            'kak_vesta_tokens' => ['nullable', 'integer', 'min:1'],
            'benefits' => ['nullable', 'string'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
