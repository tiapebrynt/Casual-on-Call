<?php

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/v1/jobs', function (Request $request) {
    $jobs=Job::with(['company:id,name,slug','category:id,name'])->where('status','published')->latest()->paginate(min($request->integer('per_page',12),50));
    return response()->json(['success'=>true,'message'=>'Daftar lowongan','data'=>$jobs->items(),'meta'=>['current_page'=>$jobs->currentPage(),'last_page'=>$jobs->lastPage(),'total'=>$jobs->total()]]);
})->middleware('throttle:api');
