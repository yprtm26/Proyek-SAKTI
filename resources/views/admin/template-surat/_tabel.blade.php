{{-- TABEL TEMPLATE PER KATEGORI --}}
<div class="bg-white rounded-b shadow overflow-x-auto">
    <table class="w-full border border-gray-300 rounded-b-lg overflow-hidden">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-3 text-center w-12">No</th>
                <th class="px-4 py-3 text-left">Nama Template</th>
                <th class="px-4 py-3 text-left">Kode</th>
                <th class="px-4 py-3 text-center">Alur</th>
                <th class="px-4 py-3 text-center">Field</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-center w-40">Aksi</th>
            </tr>
        </thead>

        <tbody class="divide-y">
        @forelse($daftar as $jenisSurat)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-center">
                    {{ $loop->iteration }}
                </td>

                <td class="px-4 py-3">
                    <div class="font-medium text-gray-800">{{ $jenisSurat->nama }}</div>
                    <div class="text-xs text-gray-400">{{ $jenisSurat->slug }}</div>
                </td>

                <td class="px-4 py-3 text-gray-600">
                    {{ $jenisSurat->kode ?: '-' }}
                </td>

                <td class="px-4 py-3 text-center">
                    <div class="flex flex-wrap justify-center gap-1">
                        @foreach(($jenisSurat->alur ?? []) as $tahap)
                            <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-medium capitalize">
                                {{ $tahap }}
                            </span>
                        @endforeach
                    </div>
                </td>

                <td class="px-4 py-3 text-center text-gray-600">
                    {{ is_array($jenisSurat->fields) ? count($jenisSurat->fields) : 0 }}
                </td>

                <td class="px-4 py-3 text-center">
                    @if($jenisSurat->aktif)
                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                            Aktif
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                            Nonaktif
                        </span>
                    @endif
                </td>

                <td class="px-4 py-3">
                    <div class="flex justify-center gap-2">
                        <a href="{{ route('admin.template-surat.edit', $jenisSurat) }}"
                           class="px-3 py-1.5 rounded-lg bg-yellow-50 text-yellow-700 hover:bg-yellow-100 text-sm font-medium">
                            Edit
                        </a>

                        <form action="{{ route('admin.template-surat.destroy', $jenisSurat) }}"
                              method="POST"
                              onsubmit="return confirm('Yakin ingin menghapus template ini? Tindakan ini tidak dapat dibatalkan.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-sm font-medium">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                    Belum ada template surat untuk kategori <strong>{{ $kategori }}</strong>.
                    <a href="{{ route('admin.template-surat.create') }}" class="text-blue-600 hover:underline">
                        Buat template baru
                    </a>.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
