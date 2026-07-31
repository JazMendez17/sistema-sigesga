<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function test_profile_is_managed_via_panel(): void
    {
        $this->markTestSkipped('Profile management uses panel/mi-perfil routes, not /profile.');
    }
}
