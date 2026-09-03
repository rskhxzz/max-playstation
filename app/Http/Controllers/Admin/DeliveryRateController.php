<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRate;
use Illuminate\Http\Request;

class DeliveryRateController extends Controller
{
    public function index()
    {
        $rates = DeliveryRate::orderBy('minimum_distance_km')->paginate(10);
        return view('admin.delivery-rates.index', compact('rates'));
    }

    public function create()
    {
        return view('admin.delivery-rates.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:100',
            'minimum_distance_km' => 'required|numeric|min:0',
            'maximum_distance_km' => 'required|numeric|gt:minimum_distance_km',
            'delivery_fee'        => 'required|numeric|min:0',
            'is_active'           => 'boolean',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['created_by'] = auth('admin')->id();

        DeliveryRate::create($data);

        return redirect()->route('admin.delivery-rates.index')
            ->with('success', 'Tarif berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $rate = DeliveryRate::findOrFail($id);
        return view('admin.delivery-rates.form', compact('rate'));
    }

    public function update(Request $request, string $id)
    {
        $rate = DeliveryRate::findOrFail($id);

        $data = $request->validate([
            'name'                => 'required|string|max:100',
            'minimum_distance_km' => 'required|numeric|min:0',
            'maximum_distance_km' => 'required|numeric|gt:minimum_distance_km',
            'delivery_fee'        => 'required|numeric|min:0',
            'is_active'           => 'boolean',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['updated_by'] = auth('admin')->id();

        $rate->update($data);

        return redirect()->route('admin.delivery-rates.index')
            ->with('success', 'Tarif berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $rate = DeliveryRate::findOrFail($id);
        $rate->is_deleted = true;
        $rate->updated_by = auth('admin')->id();
        $rate->save();

        return back()->with('success', 'Tarif berhasil dihapus.');
    }
}
