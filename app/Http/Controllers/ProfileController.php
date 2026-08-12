<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Package;
use App\Models\UserPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $packages = Package::where('is_active', true)->orderBy('sort')->orderBy('id')->get();

        return view('profile.edit', compact('packages'));
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        $package = $request->filled('package_id')
            ? Package::where('is_active', true)->findOrFail($request->integer('package_id'))
            : null;

        if ($package) {
            $userPackage = UserPackage::create([
                'user_id' => $request->user()->id,
                'package_id' => $package->id,
                'type' => $package->isTrial() ? 'trial' : 'paid',
                'status' => $package->isTrial() ? 'active' : 'pending',
                'trial_ends_at' => $package->isTrial() ? Carbon::now()->addMonth() : null,
            ]);

            if ($userPackage->status === 'pending') {
                return redirect()->route('packages.payment', $userPackage)
                    ->with('success', 'Data pribadi tersimpan. Silakan selesaikan pembayaran paket Anda.');
            }

            return redirect()->route('dashboard')
                ->with('success', "Paket {$package->name} aktif. Selamat menikmati layanan!");
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Data pribadi berhasil diperbarui.');
    }
}
