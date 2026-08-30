<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;

class BusinessSettingController extends Controller
{
    public function edit()
    {
        $setting = BusinessSetting::active() ?? new BusinessSetting();
        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'business_name'          => 'required|string|max:150',
            'phone_number'           => 'nullable|string|max:20',
            'address'                => 'nullable|string',
            'google_place_id'        => 'nullable|string|max:255',
            'latitude'               => 'nullable|numeric|between:-90,90',
            'longitude'              => 'nullable|numeric|between:-180,180',
            'down_payment_amount'    => 'nullable|numeric|min:0',
            'payment_expiry_minutes' => 'nullable|integer|min:1',
            'maximum_delivery_km'    => 'nullable|numeric|min:0',
            'is_active'              => 'boolean',
        ]);

        $data['is_active']  = true;
        $data['updated_by'] = auth()->id();

        $setting = BusinessSetting::withoutGlobalScope('not_deleted')
            ->where('is_deleted', false)
            ->first();

        if ($setting) {
            $setting->update($data);
        } else {
            $data['created_by'] = auth()->id();
            BusinessSetting::create($data);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
