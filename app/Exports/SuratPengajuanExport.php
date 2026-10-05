<?php

namespace App\Exports;

use App\Models\SuratPengajuan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class SuratPengajuanExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithColumnFormatting,
    WithCustomValueBinder

{
    protected $status;
    protected $prodi;
    protected $jenis;
    protected $sort;
    protected $role;

    public function __construct($status=null,$prodi=null,$jenis=null,$sort='desc',$role=null)
    {
        $this->status = $status;
        $this->prodi  = $prodi;
        $this->jenis  = $jenis;
        $this->sort   = $sort;
        $this->role   = $role;
    }

    public function collection()
    {
        $query = SuratPengajuan::with(['user','jenisSurat'])
            ->whereIn('status',['disetujui_final','ditolak']);

        if ($this->status) {
            $query->where('status',$this->status);
        }

        if ($this->prodi) {
            $query->whereHas('user', fn($q)=>$q->where('prodi',$this->prodi));
        }

        if ($this->jenis) {
            $query->where('jenis_surat_id',$this->jenis);
        }

        if ($this->role) {
            $query->whereHas('user', function ($q) {
                $q->where('role', $this->role);
            });
        }

        return $query
            ->orderBy('tanggal_disetujui_final', $this->sort)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NPM / NIK',
            //'User',
            'Jenis Surat',
            'Nomor Surat',
            'Status',
            'Tanggal Pengajuan',
            'Tanggal Disetujui Final',
            'Penandatangan',
        ];
    }

    public function map($surat): array
    {
        $user = $surat->user;

        // IDENTITAS DINAMIS
        $identitas = match ($user->role) {
            'mahasiswa' => $user->npm,
            'dosen'     => $user->nidn,
            default     => '-',
        };

        // STATUS DINAMIS
        $statusLabel = $user->role === 'dosen'
            ? statusDosen($surat->status)
            : statusMahasiswa($surat->status);

        return [
            $user->name ?? '-',
            $identitas ?? '-',          // NPM / NIK
            //ucfirst($user->role),       // ROLE
            $surat->jenisSurat->nama ?? '-',
            $surat->nomor_surat ?? '-',
            $statusLabel,
            $surat->created_at?->format('d-m-Y H:i'),
            $surat->tanggal_disetujui_final
                ? $surat->tanggal_disetujui_final->format('d-m-Y H:i')
                : '-',
            $surat->penandatangan ?? '-',
        ];
    }

    // =========================
    // TAMBAHAN: FORMAT KOLOM
    // =========================
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT, // kolom NPM / NIDN
        ];
    }

    // ===================================
    // PAKSA NPM / NIDN JADI TEXT
    // ===================================
    public function bindValue(Cell $cell, $value)
    {
        // Kolom B = NPM / NIDN
        if ($cell->getColumn() === 'B') {
            $cell->setValueExplicit(
                (string) $value,
                DataType::TYPE_STRING
            );
            return true;
        }

        return parent::bindValue($cell, $value);
    }

}
