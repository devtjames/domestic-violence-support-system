<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnonymousSupportCaseRequest;
use App\Http\Requests\TrackAnonymousCaseRequest;
use App\Models\AbuseType;
use App\Models\AnonymousCaseAccess;
use App\Models\CaseActivity;
use App\Models\SupportCase;
use App\Models\SystemSetting;
use App\Services\CaseAttachmentService;
use App\Services\CaseNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AnonymousSupportCaseController extends Controller
{
    public function create(): View
    {
        return view('anonymous.create', [
            'case' => new SupportCase([
                'urgency' => SupportCase::URGENCY_LOW,
                'preferred_contact_method' => SupportCase::CONTACT_NO_DIRECT_CONTACT,
            ]),
            'abuseTypes' => AbuseType::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'urgencies' => SupportCase::urgencies(),
            'contactMethods' => SupportCase::contactMethods(),
            'criticalCaseGuidance' => SystemSetting::getValue(SystemSetting::KEY_CRITICAL_CASE_GUIDANCE),
        ]);
    }

    public function store(StoreAnonymousSupportCaseRequest $request): RedirectResponse
    {
        try {
            [$supportCase, $accessCode] = DB::transaction(function () use ($request) {
                $data = $request->safe()->except('attachments');
                $data['user_id'] = null;
                $data['is_anonymous'] = true;
                $data['status'] = SupportCase::STATUS_SUBMITTED;
                $data['assigned_officer_id'] = null;

                $supportCase = SupportCase::create($data);
                $accessCode = AnonymousCaseAccess::generateAccessCode();

                $supportCase->anonymousAccess()->create([
                    'access_code_hash' => AnonymousCaseAccess::hashAccessCode($accessCode),
                ]);

                $supportCase->recordActivity(
                    CaseActivity::ACTION_CASE_SUBMITTED,
                    'Anonymous visitor submitted a support request.',
                    null,
                    null,
                    SupportCase::STATUS_SUBMITTED,
                    $request
                );

                $attachments = app(CaseAttachmentService::class)->storeMany(
                    $supportCase,
                    $request->file('attachments', [])
                );

                if ($attachments !== []) {
                    $supportCase->recordActivity(
                        CaseActivity::ACTION_ATTACHMENT_ADDED,
                        count($attachments) === 1 ? 'Anonymous case attachment uploaded at submission.' : count($attachments).' anonymous case attachments uploaded at submission.',
                        null,
                        null,
                        (string) count($attachments),
                        $request
                    );
                }

                app(CaseNotificationService::class)->caseSubmitted($supportCase);

                return [$supportCase, $accessCode];
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['support_case' => 'We could not submit the anonymous request. Please try again.']);
        }

        return redirect()
            ->route('anonymous.confirmation')
            ->with([
                'anonymous_case_reference' => $supportCase->case_reference,
                'anonymous_access_code' => $accessCode,
            ]);
    }

    public function confirmation(): View|RedirectResponse
    {
        if (! session()->has('anonymous_case_reference') || ! session()->has('anonymous_access_code')) {
            return redirect()->route('anonymous.create');
        }

        return view('anonymous.confirmation', [
            'caseReference' => session('anonymous_case_reference'),
            'accessCode' => session('anonymous_access_code'),
        ]);
    }

    public function trackForm(): View
    {
        return view('anonymous.track');
    }

    public function lookup(TrackAnonymousCaseRequest $request): View
    {
        $credentials = $request->validated();

        $supportCase = SupportCase::query()
            ->where('case_reference', $credentials['case_reference'])
            ->where('is_anonymous', true)
            ->with(['abuseType', 'anonymousAccess'])
            ->first();

        if (! $supportCase || ! $supportCase->anonymousAccess?->matches($credentials['access_code'])) {
            throw ValidationException::withMessages([
                'case_reference' => 'The case reference or access code is invalid.',
            ]);
        }

        $supportCase->anonymousAccess->update([
            'last_accessed_at' => now(),
        ]);

        return view('anonymous.result', [
            'case' => $supportCase,
        ]);
    }
}
