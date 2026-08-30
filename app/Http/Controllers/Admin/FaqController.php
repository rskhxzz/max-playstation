<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('seq')->paginate(10);
        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.faqs.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question'     => 'required|string|max:255',
            'answer'       => 'required|string',
            'seq'          => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);

        $data['seq']          = $request->seq ?? 0;
        $data['is_published'] = $request->boolean('is_published', true);
        $data['created_by']   = auth()->id();

        Faq::create($data);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $faq = Faq::findOrFail($id);
        return view('admin.faqs.form', compact('faq'));
    }

    public function update(Request $request, string $id)
    {
        $faq = Faq::findOrFail($id);

        $data = $request->validate([
            'question'     => 'required|string|max:255',
            'answer'       => 'required|string',
            'seq'          => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);

        $data['seq']          = $request->seq ?? 0;
        $data['is_published'] = $request->boolean('is_published', false);
        $data['updated_by']   = auth()->id();

        $faq->update($data);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $faq = Faq::findOrFail($id);
        $faq->is_deleted = true;
        $faq->updated_by = auth()->id();
        $faq->save();

        return back()->with('success', 'FAQ berhasil dihapus.');
    }
}
