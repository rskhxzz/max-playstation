<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\RentalPackage;
use App\Models\Faq;

class HomeController extends Controller
{
    public function index()
    {
        $setting  = BusinessSetting::active();
        $packages = RentalPackage::where('is_active', true)->get();
        $faqs     = Faq::where('is_published', true)
            ->orderBy('seq')
            ->get();

        return view('home', compact('setting', 'packages', 'faqs'));
    }
}
