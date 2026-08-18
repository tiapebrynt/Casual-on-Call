<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkerProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.worker', ['worker' => $request->user()->worker]);
    }

    public function update(WorkerProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $worker = $user->worker;
        $oldCv = $worker->cv_path;
        $newCv = $request->file('cv')?->store("workers/{$worker->id}/cv", 'local');

        DB::transaction(function () use ($request, $user, $worker, $newCv): void {
            $user->update($request->safe()->only(['name', 'phone']));
            $profile = $request->safe()->only(['headline', 'bio', 'city', 'experience_years', 'portfolio_url']);
            if ($newCv) {
                $profile['cv_path'] = $newCv;
            }
            $worker->update($profile);
        });

        if ($newCv && $oldCv) {
            Storage::disk('local')->delete($oldCv);
        }

        return back()->with('success', 'Profil worker berhasil diperbarui.');
    }

    public function download(Request $request): StreamedResponse
    {
        $path = $request->user()->worker->cv_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, 'CV-'.$request->user()->name.'.'.pathinfo($path, PATHINFO_EXTENSION));
    }

    public function destroyCv(Request $request): RedirectResponse
    {
        $worker = $request->user()->worker;
        if ($worker->cv_path) {
            Storage::disk('local')->delete($worker->cv_path);
            $worker->update(['cv_path' => null]);
        }

        return back()->with('success', 'CV berhasil dihapus.');
    }
}
