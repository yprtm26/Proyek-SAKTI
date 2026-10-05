<?php

namespace App\Ldap;

use App\Models\User as DatabaseUser;
use LdapRecord\Models\OpenLDAP\User as LdapUser;

class AttributeHandler
{
    public function handle(LdapUser $ldap, DatabaseUser $database)
    {
        $role = $ldap->getFirstAttribute('title');
        $npm = $ldap->getFirstAttribute('description');
        $nidn = $ldap->getFirstAttribute('description');

        $database->username = $ldap->getFirstAttribute('cn');
        $database->name = $database->name ?? $ldap->getFirstAttribute('displayName');
        $database->emailyarsi = $ldap->getFirstAttribute('mail');
        $database->telephonenumber = $database->telephonenumber ?? $ldap->getFirstAttribute('telephoneNumber');
        $database->email = $ldap->getFirstAttribute('mailAlternateAddress');

        $prefix = substr($npm ?? '', 0, 3);

        if ($role == 'M') {
            $database->role = 'mahasiswa';
            $database->npm = $npm;
            switch ($prefix) {
                case '140':
                    $database->prodi = 'TI';
                    break;
                case '150':
                    $database->prodi = 'PdSI';
                    break;
                default:
                    $database->prodi = null;
            }
        } else if ($role == 'D') {
            $database->role = 'dosen';
            $database->nidn = $nidn;
        } else if ($role == 'S') {
            $database->role = 'dosen';
            $database->nidn = $nidn;
        }
    }
}
