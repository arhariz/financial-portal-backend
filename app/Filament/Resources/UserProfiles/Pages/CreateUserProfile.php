<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProfiles\Pages;

use App\Filament\Resources\UserProfiles\UserProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserProfile extends CreateRecord
{
    protected static string $resource = UserProfileResource::class;
}
