<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Documents\Cnpj;
use Tests\TestCase;

final class CnpjTest extends TestCase
{
    public function test_formats_and_validates_a_known_cnpj(): void
    {
        $this->assertTrue(Cnpj::isValid('11.444.777/0001-61'));
        $this->assertSame('11444777000161', Cnpj::digits('11.444.777/0001-61'));
        $this->assertSame('11.444.777/0001-61', Cnpj::format('11444777000161'));
    }

    public function test_rejects_repeated_digits(): void
    {
        $this->assertFalse(Cnpj::isValid('11.111.111/1111-11'));
    }
}
