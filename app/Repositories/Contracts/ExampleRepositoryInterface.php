<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface ExampleRepositoryInterface
{
    public function find(int $id): mixed;
}
