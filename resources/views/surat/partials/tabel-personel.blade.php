{{-- Partial pembungkus komponen daftar anggota kegiatan (B-10 B) --}}
<x-personel-list
    :name="$name ?? 'personel'"
    :rows="$rows ?? null"
    :min-rows="$minRows ?? 1"
    :prefill-user="$prefillUser ?? true"
    :default-peran="$defaultPeran ?? 'Ketua / Penanggung Jawab'"
/>
