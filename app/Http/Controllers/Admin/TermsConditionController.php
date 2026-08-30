<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TermsCondition;
use Illuminate\Http\Request;

class TermsConditionController extends Controller
{
    public function index()
    {
        $terms = TermsCondition::orderByDesc('version')->paginate(10);
        return view('admin.terms.index', compact('terms'));
    }

    public function create()
    {
        $latestVersion = TermsCondition::max('version') ?? 0;
        return view('admin.terms.form', ['nextVersion' => $latestVersion + 1]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:150',
            'content'   => 'required|string',
            'is_active' => 'boolean',
        ]);

        $data['version']    = (TermsCondition::withoutGlobalScope('not_deleted')->max('version') ?? 0) + 1;
        $data['is_active']  = $request->boolean('is_active', true);
        $data['created_by'] = auth()->id();

        // Deactivate others if this is active
        if ($data['is_active']) {
            TermsCondition::where('is_active', true)->update(['is_active' => false]);
        }

        TermsCondition::create($data);

        return redirect()->route('admin.terms.index')
            ->with('success', 'Syarat dan ketentuan berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $terms = TermsCondition::findOrFail($id);
        return view('admin.terms.form', compact('terms'));
    }

    public function update(Request $request, string $id)
    {
        $terms = TermsCondition::findOrFail($id);

        $data = $request->validate([
            'title'     => 'required|string|max:150',
            'content'   => 'required|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active']  = $request->boolean('is_active', false);
        $data['updated_by'] = auth()->id();

        if ($data['is_active']) {
            TermsCondition::where('id', '!=', $id)->where('is_active', true)->update(['is_active' => false]);
        }

        $terms->update($data);

        return redirect()->route('admin.terms.index')
            ->with('success', 'Syarat dan ketentuan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $terms = TermsCondition::findOrFail($id);
        $terms->is_deleted = true;
        $terms->updated_by = auth()->id();
        $terms->save();

        return back()->with('success', 'Syarat dan ketentuan berhasil dihapus.');
    }
}
