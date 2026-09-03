<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlaystationUnit;
use Illuminate\Http\Request;

class PlaystationUnitController extends Controller
{
    public function index()
    {
        $units = PlaystationUnit::latest()->paginate(10);
        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        return view('admin.units.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_code'     => 'required|string|max:30|unique:c_playstation_unit,unit_code',
            'name'          => 'required|string|max:100',
            'console_type'  => 'nullable|string|max:30',
            'serial_number' => 'nullable|string|max:100',
            'status'        => 'required|in:available,maintenance,inactive',
            'notes'         => 'nullable|string',
            'is_active'     => 'boolean',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['created_by'] = auth('admin')->id();

        PlaystationUnit::create($data);

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $unit = PlaystationUnit::findOrFail($id);
        return view('admin.units.form', compact('unit'));
    }

    public function update(Request $request, string $id)
    {
        $unit = PlaystationUnit::findOrFail($id);

        $data = $request->validate([
            'unit_code'     => 'required|string|max:30|unique:c_playstation_unit,unit_code,' . $id,
            'name'          => 'required|string|max:100',
            'console_type'  => 'nullable|string|max:30',
            'serial_number' => 'nullable|string|max:100',
            'status'        => 'required|in:available,maintenance,inactive',
            'notes'         => 'nullable|string',
            'is_active'     => 'boolean',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['updated_by'] = auth('admin')->id();

        $unit->update($data);

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $unit = PlaystationUnit::findOrFail($id);
        $unit->is_deleted = true;
        $unit->updated_by = auth('admin')->id();
        $unit->save();

        return back()->with('success', 'Unit berhasil dihapus.');
    }
}
