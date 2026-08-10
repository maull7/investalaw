<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RegulationType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegulationTypeUserController extends Controller
{
    public function index(): View
    {

        $types = RegulationType::withCount('regulations')->orderBy('level')->get();

        return view('regulation-types.user.index', compact('types'));
    }
}
