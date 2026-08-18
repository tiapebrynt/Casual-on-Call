<?php
namespace App\Repositories\Contracts;
use App\Models\Job; use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface JobRepositoryInterface { public function paginatePublished(array $filters=[], int $perPage=12):LengthAwarePaginator; public function create(array $data):Job; public function update(Job $job,array $data):Job; public function delete(Job $job):void; }
