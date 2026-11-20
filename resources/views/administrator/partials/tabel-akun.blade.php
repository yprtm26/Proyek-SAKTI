{{-- ================= TABLE AKUN ================= --}}
<div class="bg-white rounded shadow overflow-x-auto mb-6">
    <table class="w-full border border-gray-300 rounded-lg overflow-hidden">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-3 text-center w-12">No</th>
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3 text-center">Role</th>
                <th class="px-4 py-3 text-center w-24">Aksi</th>
            </tr>
        </thead>

        <tbody class="divide-y">
        @forelse($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-center">
                    {{ $loop->iteration }}
                </td>

                <td class="px-4 py-3">
                    {{ $user->name }}
                </td>

                <td class="px-4 py-3">
                    {{ $user->email }}
                </td>

                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 rounded text-xs font-semibold text-white
                        @if($user->role === 'admin') bg-blue-600
                        @elseif($user->role === 'kaprodi') bg-green-600
                        @elseif($user->role === 'dekan') bg-purple-600
                        @endif">
                        {{ strtoupper($user->role) }}
                    </span>
                </td>

                <td class="px-4 py-3 text-center">
                    <form method="POST" action="/administrator/hapus-akun/{{ $user->id }}">
                        @csrf
                        @method('DELETE')
                        <button
                            onclick="return confirm('Yakin hapus akun ini?')"
                            class="bg-red-100 text-red-700 hover:bg-red-200
                                   px-3 py-1 rounded text-xs font-semibold">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                    Belum ada akun
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
