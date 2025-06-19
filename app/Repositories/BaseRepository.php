<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * The model instance
     */
    protected Model $model;

    /**
     * Constructor to initialize the model
     */
    public function __construct()
    {
        $this->model = app($this->model());
    }

    /**
     * Returns the current Model instance
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public function model(): string;

    /**
     * Get all records
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    /**
     * Find record by ID
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Find record by ID or throw exception
     */
    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * Find record by field
     */
    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->where($field, $value)->first($columns);
    }

    /**
     * Find records by field
     */
    public function findAllBy(string $field, mixed $value, array $columns = ['*']): Collection
    {
        return $this->model->where($field, $value)->get($columns);
    }

    /**
     * Create new record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update record by ID
     */
    public function update(int $id, array $data): bool
    {
        $record = $this->find($id);

        if (!$record) {
            return false;
        }

        return $record->update($data);
    }

    /**
     * Delete record by ID
     */
    public function delete(int $id): bool
    {
        $record = $this->find($id);

        if (!$record) {
            return false;
        }

        return $record->delete();
    }

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Get records with conditions
     */
    public function where(array $conditions, array $columns = ['*']): Collection
    {
        return $this->model->where($conditions)->get($columns);
    }

    /**
     * Get first record with conditions
     */
    public function whereFirst(array $conditions, array $columns = ['*']): ?Model
    {
        return $this->model->where($conditions)->first($columns);
    }

    /**
     * Get records with order by
     */
    public function orderBy(string $column, string $direction = 'asc', array $columns = ['*']): Collection
    {
        return $this->model->orderBy($column, $direction)->get($columns);
    }

    /**
     * Get records with limit
     */
    public function limit(int $limit, array $columns = ['*']): Collection
    {
        return $this->model->limit($limit)->get($columns);
    }

    /**
     * Get records with relationships
     */
    public function with(array $relationships, array $columns = ['*']): Collection
    {
        return $this->model->with($relationships)->get($columns);
    }

    /**
     * Get record with relationships by ID
     */
    public function findWith(int $id, array $relationships, array $columns = ['*']): ?Model
    {
        return $this->model->with($relationships)->find($id, $columns);
    }

    /**
     * Get count of records
     */
    public function count(array $conditions = []): int
    {
        $query = $this->model;

        if (!empty($conditions)) {
            $query = $query->where($conditions);
        }

        return $query->count();
    }

    /**
     * Check if record exists
     */
    public function exists(array $conditions): bool
    {
        return $this->model->where($conditions)->exists();
    }

    /**
     * Get query builder instance
     */
    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Update or create record
     */
    public function updateOrCreate(array $search, array $data): Model
    {
        return $this->model->updateOrCreate($search, $data);
    }

    /**
     * Get records with search functionality
     */
    public function search(string $term, array $searchableFields, array $columns = ['*']): Collection
    {
        $query = $this->model;

        foreach ($searchableFields as $field) {
            $query = $query->orWhere($field, 'LIKE', "%{$term}%");
        }

        return $query->get($columns);
    }

    /**
     * Get records with date range
     */
    public function whereDateRange(string $field, string $startDate, string $endDate, array $columns = ['*']): Collection
    {
        return $this->model
            ->whereBetween($field, [$startDate, $endDate])
            ->get($columns);
    }

    /**
     * Soft delete record (if model uses SoftDeletes)
     */
    public function softDelete(int $id): bool
    {
        $record = $this->find($id);

        if (!$record || !method_exists($record, 'delete')) {
            return false;
        }

        return $record->delete();
    }

    /**
     * Restore soft deleted record (if model uses SoftDeletes)
     */
    public function restore(int $id): bool
    {
        $record = $this->model->withTrashed()->find($id);

        if (!$record || !method_exists($record, 'restore')) {
            return false;
        }

        return $record->restore();
    }
}