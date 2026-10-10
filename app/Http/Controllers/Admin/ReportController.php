<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CaseReportFilterRequest;
use App\Models\AbuseType;
use App\Models\SupportCase;
use App\Models\User;
use App\Services\CaseReportService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(CaseReportFilterRequest $request, CaseReportService $reports): View
    {
        $filters = $request->validated();

        return view('admin.reports.index', [
            'filters' => $filters,
            'summaryCards' => $reports->summaryCards($filters),
            'groupedCounts' => $reports->groupedCounts($filters),
            'cases' => $reports->query($filters)
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            ...$this->filterOptions(),
        ]);
    }

    public function export(CaseReportFilterRequest $request, CaseReportService $reports): StreamedResponse
    {
        $filters = $request->validated();

        return response()->streamDownload(function () use ($reports, $filters) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Case Reference',
                'Submitted Date',
                'Source',
                'Abuse Type',
                'Urgency',
                'Status',
                'Assigned Officer',
                'Resolved Date',
                'Closed Date',
            ]);

            $reports->query($filters)
                ->latest()
                ->lazy()
                ->each(function (SupportCase $case) use ($handle): void {
                    fputcsv($handle, [
                        $case->case_reference,
                        $case->created_at->format('Y-m-d H:i:s'),
                        $case->is_anonymous ? 'Anonymous' : 'Registered',
                        $case->abuseType?->name ?? 'Unspecified',
                        str($case->urgency)->headline()->toString(),
                        str($case->status)->headline()->toString(),
                        $case->assignedOfficer?->name ?? 'Unassigned',
                        $case->resolved_at?->format('Y-m-d H:i:s') ?? '',
                        $case->closed_at?->format('Y-m-d H:i:s') ?? '',
                    ]);
                });

            fclose($handle);
        }, 'case-report-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function filterOptions(): array
    {
        return [
            'abuseTypes' => AbuseType::query()->orderBy('name')->get(['id', 'name']),
            'officers' => User::query()
                ->where('role', User::ROLE_SUPPORT_OFFICER)
                ->orderBy('name')
                ->get(['id', 'name', 'account_status']),
            'statuses' => SupportCase::statuses(),
            'urgencies' => SupportCase::urgencies(),
        ];
    }
}
