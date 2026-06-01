@extends('admin.layouts.admin')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'Users - CreativeMarket')
@section('header', 'Users')

@section('content')
<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
            <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                <option value="">All Roles</option>
                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                <option value="author" {{ request('role') == 'author' ? 'selected' : '' }}>Author</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-black text-white rounded-lg text-sm font-semibold hover:bg-gray-800">Filter</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Joined</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-[#FFC300] to-[#FFD633] rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-black">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            @if($user->role == 'admin') bg-red-100 text-red-700
                            @elseif($user->role == 'author') bg-blue-100 text-blue-700
                            @else bg-gray-100 text-gray-600
                            @endif capitalize">{{ $user->role }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->products_count ?? $user->products->count() ?? 0 }}</td>
                    <td class="px-6 py-4 text-sm font-semibold whitespace-nowrap">{{ CurrencyHelper::format($user->balance) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-sm text-[#FFC300] hover:text-black font-semibold mr-3">Edit</a>
                        @if(!$user->isAdmin() || \App\Models\User::where('role', 'admin')->count() > 1)
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete this user?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-semibold">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200">
        {{ $users->links() }}
    </div>
</div>
@endsection
