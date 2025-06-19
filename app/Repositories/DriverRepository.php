<?php

namespace App\Repositories;

use App\Models\Driver;

class DriverRepository extends BaseRepository
{
    public function model(): string
    {
        return Driver::class;
    }

    public function updateOrCreateDriver(array $data)
    {
        return $this->model->updateOrCreate([
            'user_id' => auth()->user()->id,
            ...$data,
        ], $data);
    }
}
