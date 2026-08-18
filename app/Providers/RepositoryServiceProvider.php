<?php
namespace App\Providers; use App\Repositories\Contracts\JobRepositoryInterface; use App\Repositories\EloquentJobRepository; use Illuminate\Support\ServiceProvider;
class RepositoryServiceProvider extends ServiceProvider { public function register():void{$this->app->bind(JobRepositoryInterface::class,EloquentJobRepository::class);} }
