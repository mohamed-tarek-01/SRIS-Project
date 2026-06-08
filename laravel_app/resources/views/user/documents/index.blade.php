@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-12 pb-20" x-data="{ showUploadModal: false }">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20 shadow-lg shadow-blue-500/5">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight uppercase">Document Wallet</h1>
            </div>
            <p class="text-slate-400 font-medium max-w-xl">Securely store and manage your driving license, vehicle registration, and insurance policies.</p>
        </div>
        
        <button @click="showUploadModal = true" class="group relative px-8 py-4 bg-white text-dark-900 rounded-2xl font-black uppercase tracking-widest text-xs transition-all hover:bg-blue-500 hover:text-white active:scale-95 flex items-center gap-2 overflow-hidden shadow-xl shadow-white/5">
            <i data-lucide="upload-cloud" class="w-4 h-4 relative z-10"></i>
            <span class="relative z-10">Upload Document</span>
        </button>
    </div>

    <!-- Documents Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($documents as $doc)
            <div class="group glass-panel rounded-[2.5rem] border border-white/5 p-8 transition-all hover:border-blue-500/30 hover:shadow-2xl hover:shadow-blue-500/10 flex flex-col h-full">
                <div class="flex items-start justify-between mb-8">
                    <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-blue-400">
                        @php
                            $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION);
                            $icon = match($ext) {
                                'pdf' => 'file-type-2',
                                'jpg', 'jpeg', 'png' => 'image',
                                default => 'file'
                            };
                        @endphp
                        <i data-lucide="{{ $icon }}" class="w-8 h-8"></i>
                    </div>
                    
                    @if($doc->expiry_date)
                        @php
                            $days = \Carbon\Carbon::now()->diffInDays($doc->expiry_date, false);
                            $color = $days < 0 ? 'text-rose-400 bg-rose-500/10 border-rose-500/20' : ($days < 30 ? 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20' : 'text-green-400 bg-green-500/10 border-green-500/20');
                        @endphp
                        <div class="px-4 py-1.5 rounded-full border text-[9px] font-black uppercase tracking-widest {{ $color }}">
                            {{ $days < 0 ? 'Expired' : ($days < 30 ? 'Expiring Soon' : 'Valid') }}
                        </div>
                    @endif
                </div>

                <div class="flex-1 space-y-2">
                    <h3 class="text-xl font-black text-white leading-tight uppercase tracking-tight">{{ $doc->title }}</h3>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $doc->type }}</p>
                    
                    @if($doc->expiry_date)
                        <div class="pt-4 flex items-center gap-2 text-xs font-bold text-slate-400">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                            Expires: {{ \Carbon\Carbon::parse($doc->expiry_date)->format('M d, Y') }}
                        </div>
                    @endif
                </div>

                <div class="mt-8 pt-6 border-t border-white/5 flex items-center justify-between">
                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="flex items-center gap-2 text-[10px] font-black text-blue-400 uppercase tracking-widest hover:text-white transition-colors">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                        View File
                    </a>
                    
                    <form action="{{ route('user.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-3 rounded-xl bg-white/5 border border-white/10 text-slate-600 hover:text-rose-400 hover:bg-rose-500/10 hover:border-rose-500/20 transition-all">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 flex flex-col items-center text-center space-y-6">
                <div class="w-24 h-24 rounded-[2rem] bg-slate-900 flex items-center justify-center border border-white/5 text-slate-700">
                    <i data-lucide="file-lock-2" class="w-10 h-10"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-white mb-2">Wallet is Empty</h3>
                    <p class="text-slate-500 text-sm max-w-xs mx-auto">Upload your vehicle documents to have them accessible anywhere, anytime.</p>
                </div>
                <button @click="showUploadModal = true" class="px-8 py-4 bg-blue-500 text-white rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-blue-400 transition-all shadow-lg shadow-blue-500/20">
                    Upload My First Doc
                </button>
            </div>
        @endforelse
    </div>

    <!-- Upload Modal -->
    <template x-teleport="body">
        <div x-show="showUploadModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
            <div @click="showUploadModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>
            
            <div class="relative w-full max-w-lg glass-panel rounded-[2.5rem] border border-white/10 p-8 md:p-12 shadow-2xl bg-dark-800">
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-10">
                        <div>
                            <h2 class="text-3xl font-black text-white leading-none mb-2">Upload Doc</h2>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em]">Securely store your files</p>
                        </div>
                        <button @click="showUploadModal = false" class="text-slate-500 hover:text-white transition-colors">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <form action="{{ route('user.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Document Title</label>
                            <input type="text" name="title" required placeholder="e.g. Driver's License" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Category</label>
                            <input type="text" name="type" required placeholder="e.g. License, Insurance" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Expiry Date</label>
                            <input type="date" name="expiry_date" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-500/50 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">File (PDF or Image)</label>
                            <input type="file" name="file" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-slate-400 focus:outline-none focus:border-blue-500/50 transition-all file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-blue-500 file:text-white cursor-pointer">
                        </div>

                        <button type="submit" class="w-full py-5 bg-white text-dark-900 rounded-[1.5rem] font-black uppercase tracking-widest text-[11px] hover:bg-blue-500 hover:text-white transition-all shadow-xl active:scale-[0.98] mt-6">
                            Upload Securely
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
