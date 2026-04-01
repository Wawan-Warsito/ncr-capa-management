<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsApi(User $user): self
    {
        Sanctum::actingAs($user);
        return $this;
    }
}
