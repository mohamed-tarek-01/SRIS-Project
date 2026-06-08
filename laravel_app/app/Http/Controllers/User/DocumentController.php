<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = auth()->user()->documents()->latest()->get();
        return view('user.documents.index', compact('documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'expiry_date' => 'nullable|date',
        ]);

        $path = $request->file('file')->store('documents', 'public');

        Document::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'type' => $request->type,
            'file_path' => $path,
            'expiry_date' => $request->expiry_date,
        ]);

        return redirect()->back()->with('success', 'Document uploaded successfully!');
    }

    public function destroy(Document $document)
    {
        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->back()->with('success', 'Document removed successfully!');
    }
}
