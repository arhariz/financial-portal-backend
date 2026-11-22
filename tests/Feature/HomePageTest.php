<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_returns_successful_response(): void
    {
        $this->get('/')->assertOk();
    }
}
