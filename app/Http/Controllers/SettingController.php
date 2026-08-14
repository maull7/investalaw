<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('settings.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trial_requires_confirmation' => ['sometimes', 'in:0,1'],
            'trial_max_hours' => ['required', 'integer', 'min:1', 'max:8760'],
        ]);

        Setting::updateOrCreate(['key' => 'trial_requires_confirmation'], [
            'value' => $request->boolean('trial_requires_confirmation') ? '1' : '0',
        ]);
        Setting::updateOrCreate(['key' => 'trial_max_hours'], [
            'value' => (string) $validated['trial_max_hours'],
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Setting paket trial berhasil diperbarui.');
    }
}
