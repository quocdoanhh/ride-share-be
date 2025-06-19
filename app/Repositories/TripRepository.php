<?php

namespace App\Repositories;

use App\Models\Trip;

class TripRepository extends BaseRepository
{
    public function model(): string
    {
        return Trip::class;
    }
}
