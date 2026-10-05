@extends('layouts.app')

@section('content')

<div class="bg-white rounded-lg shadow p-6">

    <div class="flex justify-between items-center mb-6">
        <a href="{{ auth()->user()->dashboardUrl() }}"
       class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
        ← Kembali ke Dashboard
    </a>
        <h2 class="text-xl font-semibold text-gray-800">
            Semua Notifikasi
        </h2>

        {{-- Mark as Read --}}
        <form method="POST" action="/notifikasi/read-all">
            @csrf
            <button class="text-sm text-blue-600 hover:underline">
                Tandai semua dibaca
            </button>
        </form>
    </div>

    <div class="space-y-2">

        @forelse($notifications as $notif)
            <a href="{{ $notif->url ?? '#' }}"
               class="block border rounded-lg px-4 py-3 transition
               {{ !$notif->is_read
                    ? 'bg-orange-50 border-orange-300 hover:bg-orange-100'
                    : 'bg-white hover:bg-gray-50' }}">

                <div class="flex justify-between items-start gap-4">

                    <div>
                        <h3 class="font-semibold text-gray-800">
                            {{ $notif->title }}
                        </h3>

                        <p class="text-sm text-gray-600 mt-1">
                            {{ $notif->message }}
                        </p>
                    </div>

                    <div class="text-xs text-gray-400 whitespace-nowrap">
                        {{ optional($notif->created_at)->diffForHumans() }}
                    </div>

                </div>

                @if(!$notif->is_read)
                    <span class="inline-block mt-2 text-xs
                                 bg-orange-500 text-white
                                 px-2 py-0.5 rounded-full">
                        Baru
                    </span>
                @endif
            </a>
        @empty
            <div class="text-center py-12 text-gray-500">
                Tidak ada notifikasi
            </div>
        @endforelse

    </div>

    {{-- PAGINATION --}}
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>

</div>

@endsection
