<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContactMessageRequest;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $this->normalizedSort($request->string('sort')->toString(), $this->messageSortOptions());

        $query = ContactMessage::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')));

        $this->applyMessageSort($query, $sort);

        $messages = $query
            ->paginate(12)
            ->withQueryString();

        return view('admin.contact-messages.index', [
            'messages' => $messages,
            'statuses' => ContactMessage::statuses(),
            'sortOptions' => $this->messageSortOptions(),
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        if ($contactMessage->status === ContactMessage::STATUS_NEW) {
            $contactMessage->update([
                'status' => ContactMessage::STATUS_READ,
                'reviewed_at' => now(),
            ]);
        }

        return view('admin.contact-messages.show', [
            'message' => $contactMessage->refresh(),
            'statuses' => ContactMessage::statuses(),
        ]);
    }

    public function update(UpdateContactMessageRequest $request, ContactMessage $contactMessage): RedirectResponse
    {
        $status = $request->string('status')->toString();

        $previousStatus = $contactMessage->status;

        $contactMessage->update([
            'status' => $status,
            'admin_note' => $request->string('admin_note')->toString() ?: null,
            'reviewed_at' => $contactMessage->reviewed_at ?? now(),
            'resolved_at' => $status === ContactMessage::STATUS_RESOLVED
                ? ($contactMessage->resolved_at ?? now())
                : null,
        ]);

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_CONTACT_MESSAGES,
            'contact_message_updated',
            'Contact message status updated.',
            $contactMessage,
            ['previous_status' => $previousStatus, 'new_status' => $contactMessage->status]
        );

        return redirect()
            ->route('admin.contact-messages.show', $contactMessage)
            ->with('status', 'Contact message updated.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        app(AuditLogger::class)->record(
            request(),
            AuditLog::MODULE_CONTACT_MESSAGES,
            'contact_message_deleted',
            'Contact message deleted.',
            $contactMessage,
            ['previous_status' => $contactMessage->status]
        );

        $contactMessage->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('status', 'Contact message deleted.');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<ContactMessage>  $query
     */
    private function applyMessageSort($query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'status' => $query->orderBy('status')->orderByDesc('created_at'),
            'subject' => $query->orderBy('subject')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    /**
     * @return array<string, string>
     */
    private function messageSortOptions(): array
    {
        return [
            'newest' => 'Newest received',
            'oldest' => 'Oldest received',
            'status' => 'Status',
            'subject' => 'Subject',
        ];
    }

    /**
     * @param  array<string, string>  $options
     */
    private function normalizedSort(string $sort, array $options): string
    {
        return array_key_exists($sort, $options) ? $sort : 'newest';
    }
}
