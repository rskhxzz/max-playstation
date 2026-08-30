<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RentalPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RentalPackageController extends Controller
{
    public function index()
    {
        $packages = RentalPackage::latest()->paginate(10);
        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.packages.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'               => 'required|string|max:30|unique:c_rental_package,code',
            'name'               => 'required|string|max:100',
            'duration_hours'     => 'required|integer|min:1',
            'price'              => 'required|numeric|min:0',
            'blocked_start_time' => 'nullable|date_format:H:i',
            'blocked_end_time'   => 'nullable|date_format:H:i',
            'description'        => 'nullable|string',
            'is_active'          => 'boolean',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['created_by'] = auth()->id();

        RentalPackage::create($data);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Paket berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $package = RentalPackage::findOrFail($id);
        return view('admin.packages.form', compact('package'));
    }

    public function update(Request $request, string $id)
    {
        $package = RentalPackage::findOrFail($id);

        $data = $request->validate([
            'code'               => 'required|string|max:30|unique:c_rental_package,code,' . $id,
            'name'               => 'required|string|max:100',
            'duration_hours'     => 'required|integer|min:1',
            'price'              => 'required|numeric|min:0',
            'blocked_start_time' => 'nullable|date_format:H:i',
            'blocked_end_time'   => 'nullable|date_format:H:i',
            'description'        => 'nullable|string',
            'is_active'          => 'boolean',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['updated_by'] = auth()->id();

        $package->update($data);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $package = RentalPackage::findOrFail($id);
        $package->is_deleted = true;
        $package->updated_by = auth()->id();
        $package->save();

        return back()->with('success', 'Paket berhasil dihapus.');
    }
}
