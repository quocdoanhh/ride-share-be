<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface BaseRepositoryInterface
{
    /**
     * Get all records
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find record by ID
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find record by ID or throw exception
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Find record by field
     */
    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model;

    /**
     * Find records by field
     */
    public function findAllBy(string $field, mixed $value, array $columns = ['*']): Collection;

    /**
     * Create new record
     */
    public function create(array $data): Model;

    /**
     * Update record by ID
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete record by ID
     */
    public function delete(int $id): bool;

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Get records with conditions
     */
    public function where(array $conditions, array $columns = ['*']): Collection;

    /**
     * Get first record with conditions
     */
    public function whereFirst(array $conditions, array $columns = ['*']): ?Model;

    /**
     * Get records with order by
     */
    public function orderBy(string $column, string $direction = 'asc', array $columns = ['*']): Collection;

    /**
     * Get records with limit
     */
    public function limit(int $limit, array $columns = ['*']): Collection;

    /**
     * Get records with relationships
     */
    public function with(array $relationships, array $columns = ['*']): Collection;

    /**
     * Get record with relationships by ID
     */
    public function findWith(int $id, array $relationships, array $columns = ['*']): ?Model;

    /**
     * Get count of records
     */
    public function count(array $conditions = []): int;

    /**
     * Check if record exists
     */
    public function exists(array $conditions): bool;

    /**
     * Get query builder instance
     */
    public function query(): Builder;

    /**
     * Update or create record
     */
    public function updateOrCreate(array $search, array $data): Model;

    /**
     * Get records with search functionality
     */
    public function search(string $term, array $searchableFields, array $columns = ['*']): Collection;

    /**
     * Get records with date range
     */
    public function whereDateRange(string $field, string $startDate, string $endDate, array $columns = ['*']): Collection;
}