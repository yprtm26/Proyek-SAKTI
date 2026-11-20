<?php

namespace App\Services;

use LdapRecord\Container;

/**
 * Pemeriksaan konektivitas LDAP sebelum autentikasi.
 *
 * LdapRecord Laravel memiliki fitur fallback ke tabel users ketika user
 * tidak ditemukan di LDAP. Fitur itu berguna untuk akun lokal, tetapi
 * berbahaya jika server LDAP sedang mati: exception koneksi ditelan dan
 * login bisa lolos lewat password database. Preflight ini memastikan
 * gangguan LDAP berhenti sebagai kegagalan tertutup (fail-closed).
 */
class LdapAvailability
{
    /**
     * @throws \Throwable jika LDAP tidak dapat dihubungi.
     */
    public function ensureAvailable(): void
    {
        Container::getConnection(config('ldap.default'))
            ->connect();
    }
}
