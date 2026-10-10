<?php

namespace App\Services;

use App\Models\AbuseType;
use App\Models\SupportCase;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CaseReportService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<SupportCase>
     */
    public function query(array $filters): Builder
    {
        return SupportCase::query()
            ->with(['abuseType:id,name', 'assignedOfficer:id,name'])
            ->when(! empty($filters['date_from']), fn (Builder $query) => $query->where('created_at', '>=', CarbonImmutable::parse($filters['date_from'])->startOfDay()))
            ->when(! empty($filters['date_to']), fn (Builder $query) => $query->where('created_at', '<=', CarbonImmutable::parse($filters['date_to'])->endOfDay()))
            ->when(! empty($filters['status']), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['urgency']), fn (Builder $query) => $query->where('urgency', $filters['urgency']))
            ->when(! empty($filters['abuse_type_id']), fn (Builder $query) => $query->where('abuse_type_id', $filters['abuse_type_id']))
            ->when(! empty($filters['assigned_officer_id']), function (Builder $query) use ($filters) {
                if ($filters['assigned_officer_id'] === 'unassigned') {
                    $query->whereNull('assigned_officer_id');

                    return;
                }

                $query->where('assigned_officer_id', $filters['assigned_officer_id']);
            })
            ->when(! empty($filters['source']), fn (Builder $query) => $query->where('is_anonymous', $filters['source'] === 'anonymous'))
            ->when(! empty($filters['workflow']), function (Builder $query) use ($filters) {
                $statuses = $filters['workflow'] === 'pending'
                    ? SupportCase::openStatuses()
                    : SupportCase::terminalStatuses();

                $query->whereIn('status', $statuses);
            });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array{label: string, value: int, tone: string}>
     */
    public function summaryCards(array $filters): array
    {
        $query = $this->query($filters);

        return [
            ['label' => 'Filtered Cases', 'value' => (clone $query)->count(), 'tone' => 'slate'],
            ['label' => 'Pending Cases', 'value' => (clone $query)->whereIn('status', SupportCase::openStatuses())->count(), 'tone' => 'amber'],
            ['label' => 'Resolved Cases', 'value' => (clone $query)->whereIn('status', SupportCase::terminalStatuses())->count(), 'tone' => 'emerald'],
            ['label' => 'Anonymous Cases', 'value' => (clone $query)->where('is_anonymous', true)->count(), 'tone' => 'violet'],
            ['label' => 'Registered Cases', 'value' => (clone $query)->where('is_anonymous', false)->count(), 'tone' => 'teal'],
            ['label' => 'High Priority', 'value' => (clone $query)->whereIn('urgency', [SupportCase::URGENCY_HIGH, SupportCase::URGENCY_CRITICAL])->count(), 'tone' => 'red'],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, array<string, int>>
     */
    public function groupedCounts(array $filters): array
    {
        $cases = $this->query($filters)
            ->get(['id', 'created_at', 'status', 'urgency', 'abuse_type_id', 'assigned_officer_id', 'is_anonymous']);

        return [
            'date' => $this->casesByDate($cases),
            'status' => $this->countsForValues($cases, 'status', SupportCase::statuses()),
            'urgency' => $this->countsForValues($cases, 'urgency', SupportCase::urgencies()),
            'abuseType' => $this->casesByAbuseType($cases),
            'officer' => $this->casesByOfficer($cases),
            'source' => [
                'Registered' => $cases->where('is_anonymous', false)->count(),
                'Anonymous' => $cases->where('is_anonymous', true)->count(),
            ],
            'workflow' => [
                'Pending' => $cases->whereIn('status', SupportCase::openStatuses())->count(),
                'Resolved' => $cases->whereIn('status', SupportCase::terminalStatuses())->count(),
            ],
        ];
    }

    /**
     * @param  Collection<int, SupportCase>  $cases
     * @return array<string, int>
     */
    private function casesByDate(Collection $cases): array
    {
        return $cases
            ->groupBy(fn (SupportCase $case): string => $case->created_at->format('Y-m-d'))
            ->map->count()
            ->sortKeys()
            ->all();
    }

    /**
     * @param  Collection<int, SupportCase>  $cases
     * @param  array<int, string>  $values
     * @return array<string, int>
     */
    private function countsForValues(Collection $cases, string $field, array $values): array
    {
        return collect($values)
            ->mapWithKeys(fn (string $value): array => [str($value)->headline()->toString() => $cases->where($field, $value)->count()])
            ->all();
    }

    /**
     * @param  Collection<int, SupportCase>  $cases
     * @return array<string, int>
     */
    private function casesByAbuseType(Collection $cases): array
    {
        $names = AbuseType::query()
            ->whereIn('id', $cases->pluck('abuse_type_id')->filter()->unique())
            ->pluck('name', 'id');

        return $cases
            ->groupBy('abuse_type_id')
            ->mapWithKeys(fn (Collection $items, mixed $abuseTypeId): array => [($names[$abuseTypeId] ?? 'Unspecified') => $items->count()])
            ->sortKeys()
            ->all();
    }

    /**
     * @param  Collection<int, SupportCase>  $cases
     * @return array<string, int>
     */
    private function casesByOfficer(Collection $cases): array
    {
        $names = User::query()
            ->whereIn('id', $cases->pluck('assigned_officer_id')->filter()->unique())
            ->pluck('name', 'id');

        return $cases
            ->groupBy(fn (SupportCase $case): string => $case->assigned_officer_id ? (string) $case->assigned_officer_id : 'unassigned')
            ->mapWithKeys(fn (Collection $items, string $officerId): array => [$officerId === 'unassigned' ? 'Unassigned' : ($names[$officerId] ?? 'Unknown officer') => $items->count()])
            ->sortKeys()
            ->all();
    }
}
