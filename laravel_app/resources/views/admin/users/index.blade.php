@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400 font-sans tracking-tight">User Management</h1>
            <p class="text-slate-400 mt-1 text-sm font-light">Efficiently manage system access, roles, and identity profiles across the ecosystem.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-semibold rounded-xl shadow-[0_0_25px_rgba(16,185,129,0.2)] transition-all transform hover:-translate-y-0.5 active:scale-95 group">
            <i data-lucide="user-plus" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            <span>Add New User</span>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-primary-500/10 border border-primary-500/20 text-primary-400 p-4 rounded-xl flex items-center gap-3 animate-fade-in-up">
            <div class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl flex items-center gap-3 animate-fade-in-up">
            <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="glass-panel rounded-2xl overflow-hidden border border-white/5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/[0.03] border-b border-white/10">
                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">User Profile</th>
                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">National ID</th>
                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Access Role</th>

                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.05]">
                    @foreach($users as $user)
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center text-slate-500 border border-white/5 group-hover:border-primary-500/30 group-hover:text-primary-400 transition-all duration-300">
                                        <i data-lucide="user" class="w-6 h-6"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <div class="text-sm font-bold text-white tracking-wide">{{ $user->name }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono tracking-tight">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-sm font-mono text-slate-400">
                                    {{ $user->national_id ?? '--- --- --- ---' }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="inline-flex">
                                    @if($user->role === 'admin')
                                        <span class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter bg-amber-500/10 text-amber-500 border border-amber-500/20 shadow-[0_0_15px_rgba(245,158,11,0.05)]">
                                            <i data-lucide="shield-check" class="w-3 h-3"></i> Admin
                                        </span>
                                    @else
                                        <span class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter bg-blue-500/10 text-blue-400 border border-blue-500/20 shadow-[0_0_15px_rgba(59,130,246,0.05)]">
                                            <i data-lucide="shield" class="w-3 h-3"></i> User
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-3 opacity-60 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="p-2.5 rounded-xl bg-white/5 border border-white/5 text-slate-400 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all group/btn" 
                                       title="Edit Configuration">
                                        <i data-lucide="settings-2" class="w-5 h-5 group-hover/btn:rotate-45 transition-transform"></i>
                                    </a>
                                    
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('CRITICAL: Are you sure you want to permanently revoke access for this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-2.5 rounded-xl bg-white/5 border border-white/5 text-slate-400 hover:text-red-400 hover:bg-red-500/10 hover:border-red-500/20 transition-all group/del" 
                                                    title="Permanently Delete">
                                                <i data-lucide="trash-2" class="w-5 h-5 group-hover/del:scale-110 transition-transform"></i>
                                            </button>
                                        </form>
                                    @else
                                        <div class="p-2.5 rounded-xl bg-primary-500/20 border border-primary-500/30 text-primary-400 cursor-help" title="Active Session Container">
                                            <i data-lucide="lock" class="w-5 h-5"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Section -->
        <div class="px-8 py-5 bg-white/[0.01] border-t border-white/10">
            <div class="text-slate-500 text-xs font-medium italic">
                Showing {{ $users->count() }} of {{ $users->total() }} records in the system cluster
            </div>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
