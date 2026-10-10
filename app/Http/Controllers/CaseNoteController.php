<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaseNoteRequest;
use App\Models\AuditLog;
use App\Models\CaseActivity;
use App\Models\SupportCase;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CaseNoteController extends Controller
{
    public function store(StoreCaseNoteRequest $request, SupportCase $supportCase): RedirectResponse
    {
        DB::transaction(function () use ($request, $supportCase) {
            $supportCase->notes()->create([
                'user_id' => $request->user()->id,
                'note' => $request->validated('note'),
            ]);

            $supportCase->recordActivity(
                CaseActivity::ACTION_INTERNAL_NOTE_ADDED,
                'Internal case note added.',
                $request->user(),
                null,
                null,
                $request
            );

            app(AuditLogger::class)->record(
                $request,
                AuditLog::MODULE_CASES,
                'internal_note_added',
                'Internal case note added for '.$supportCase->case_reference.'.',
                $supportCase
            );
        });

        return back()->with('status', 'Internal note saved.');
    }
}
