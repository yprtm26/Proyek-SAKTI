<?php

namespace App\Exports;

use App\Models\Disposisi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DisposisiExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Disposisi::with(['surat','fromUser','toUser'])
            //->orderBy('created_at','desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tgl Terima Surat',
            'Email / Cetak',
            'Tgl di Surat',
            'Pengirim',
            'Tujuan Surat',
            'No Surat',
            'Perihal',
            'Jumlah',
            'Link',
            'Disposisi',
        ];
    }

    public function map($d): array
    {
        return [
            optional($d->surat->tgl_terima_surat)->format('d-m-Y') ?? '-',
            strtoupper($d->surat->pilihan_kirim ?? '-'), // email / cetak
            optional($d->surat->tgl_surat)->format('d-m-Y') ?? '-',
            $d->surat->nama_pengirim ?? '-',
            $d->toUser->name ?? '-',
            $d->surat->no_surat ?? '-',
            $d->surat->perihal ?? '-',
            $d->surat->jumlah_pembayaran ?? '-', // boleh kosong
            url('/storage/'.$d->surat->file_path), // 🔗 link file
            $d->status ?? '-',
        ];
    }
}