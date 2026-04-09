<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_path_redirects_to_the_device_monitor(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/devices');
    }
}
