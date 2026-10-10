<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaseAttachmentRequest;
use App\Models\AuditLog;
use App\Models\CaseActivity;
use App\Models\CaseAttachment;
use App\Models\SupportCase;
use App\Services\AuditLogger;
use App\Services\CaseAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CaseAttachmentController extends Controller
{
    public function store(StoreCaseAttachmentRequest $request, SupportCase $supportCase): RedirectResponse
    {
        $attachments = app(CaseAttachmentService::class)->storeMany(
            $supportCase,
            $request->file('attachments', []),
            $request->user()
        );

        if ($attachments !== []) {
            $supportCase->recordActivity(
                CaseActivity::ACTION_ATTACHMENT_ADDED,
                count($attachments) === 1 ? 'Case attachment uploaded.' : count($attachments).' case attachments uploaded.',
                $request->user(),
                null,
                (string) count($attachments),
                $request
            );

            app(AuditLogger::class)->record(
                $request,
                AuditLog::MODULE_CASES,
                'case_attachment_uploaded',
                'Case attachment uploaded for '.$supportCase->case_reference.'.',
                $supportCase,
                ['attachment_count' => count($attachments)]
            );
        }

        return back()->with('status', 'Attachment uploaded.');
    }

    public function download(Request $request, CaseAttachment $caseAttachment): StreamedResponse
    {
        $caseAttachment->loadMissing('supportCase');
        $supportCase = $caseAttachment->supportCase;

        abort_unless($supportCase instanceof SupportCase, 404);
        abort_unless($this->canDownload($request, $supportCase), 403);
        abort_unless(Storage::disk($caseAttachment->disk)->exists($caseAttachment->path), 404);

        return Storage::disk($caseAttachment->disk)->download(
            $caseAttachment->path,
            $caseAttachment->safeDownloadName(),
            [
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Content-Type' => $caseAttachment->mime_type,
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    private function canDownload(Request $request, SupportCase $supportCase): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSupportOfficer()) {
            return $supportCase->canBeManagedByOfficer($user);
        }

        return $user->isRegisteredUser() && $supportCase->user_id === $user->id;
    }
}
