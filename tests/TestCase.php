<?php

namespace Tests;

use App\Services\LdapAvailability;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LdapRecord\Laravel\LdapUserRepository;
use Mockery;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test tidak boleh menghubungi server LDAP kampus. Secara default
        // LDAP dianggap tersedia, tetapi pencarian user selalu kosong agar
        // jalur fallback database dapat diuji secara deterministik.
        $this->app->instance(
            LdapAvailability::class,
            Mockery::mock(LdapAvailability::class)
                ->shouldReceive('ensureAvailable')
                ->andReturnNull()
                ->getMock()
        );

        $this->app->bind(LdapUserRepository::class, function () {
            $repository = Mockery::mock(LdapUserRepository::class);
            $repository->shouldReceive('findByCredentials')->andReturnNull();

            return $repository;
        });

        $this->app['auth']->forgetGuards();
    }
}
