<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageReceived;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    private const CHAT_ROLES = ['admin', 'coach', 'user'];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'contact_search' => ['nullable', 'string', 'max:100'],
        ]);
        $contactSearch = trim($validated['contact_search'] ?? '');

        return view('portal.chat', [
            'contacts' => $this->contacts($request->user(), $contactSearch),
            'contact' => null,
            'messages' => collect(),
            'mediaMessages' => collect(),
            'contactSearch' => $contactSearch,
            'messageSearch' => '',
        ]);
    }

    public function show(Request $request, User $contact): View
    {
        abort_unless($this->canMessage($request->user(), $contact), 403);
        $validated = $request->validate([
            'contact_search' => ['nullable', 'string', 'max:100'],
            'message_search' => ['nullable', 'string', 'max:100'],
        ]);
        $contactSearch = trim($validated['contact_search'] ?? '');
        $messageSearch = trim($validated['message_search'] ?? '');

        Message::where('sender_id', $contact->id)->where('recipient_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        $conversation = $this->conversationQuery($request->user(), $contact);
        $messages = (clone $conversation)
            ->when($messageSearch !== '', function (Builder $query) use ($messageSearch): void {
                $query->where(function (Builder $matches) use ($messageSearch): void {
                    $matches->where('body', 'like', '%'.$messageSearch.'%')
                        ->orWhere('attachment_name', 'like', '%'.$messageSearch.'%');
                });
            })
            ->oldest()
            ->get();
        $mediaMessages = (clone $conversation)
            ->whereNotNull('attachment_path')
            ->latest()
            ->limit(100)
            ->get();

        return view('portal.chat', [
            'contacts' => $this->contacts($request->user(), $contactSearch),
            'contact' => $contact,
            'messages' => $messages,
            'mediaMessages' => $mediaMessages,
            'contactSearch' => $contactSearch,
            'messageSearch' => $messageSearch,
        ]);
    }

    public function store(StoreMessageRequest $request, User $contact): RedirectResponse
    {
        abort_unless($this->canMessage($request->user(), $contact), 403);
        $validated = $request->validated();
        $attachment = $request->file('attachment');
        $attachmentPath = $attachment?->store('chat-attachments', config('filesystems.default'));

        if ($attachment !== null && ! is_string($attachmentPath)) {
            abort(500, 'The attachment could not be stored.');
        }

        $attachmentMime = $attachment?->getMimeType();
        $message = Message::create([
            'sender_id' => $request->user()->id,
            'recipient_id' => $contact->id,
            'body' => filled($validated['body'] ?? null) ? trim($validated['body']) : null,
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentMime === null ? null : 'image',
            'attachment_mime' => $attachmentMime,
            'attachment_name' => $attachment?->getClientOriginalName(),
            'attachment_size' => $attachment?->getSize(),
        ]);
        $message->load('sender');
        $contact->notify(new NewMessageReceived($message));

        return redirect()->route('chat.show', $contact);
    }

    public function attachment(Request $request, Message $message): StreamedResponse
    {
        abort_unless(
            in_array($request->user()->id, [$message->sender_id, $message->recipient_id], true),
            403,
        );
        $disk = Storage::disk(config('filesystems.default'));
        abort_if($message->attachment_path === null || ! $disk->exists($message->attachment_path), 404);

        return $disk->response(
            $message->attachment_path,
            $message->attachment_name,
            [
                'Content-Type' => $message->attachment_mime,
                'Cache-Control' => 'private, max-age=300',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }

    public function destroy(Request $request, Message $message): RedirectResponse
    {
        abort_unless($message->sender_id === $request->user()->id, 403);

        $message->loadMissing('recipient');
        $contact = $message->recipient;
        $disk = Storage::disk(config('filesystems.default'));

        if ($message->attachment_path !== null && $disk->exists($message->attachment_path)) {
            abort_unless($disk->delete($message->attachment_path), 500, 'The message attachment could not be removed.');
        }

        DB::transaction(function () use ($message): void {
            $message->recipient->notifications()
                ->where('type', NewMessageReceived::class)
                ->get()
                ->filter(fn ($notification): bool => ($notification->data['message_id'] ?? null) === $message->id)
                ->each->delete();

            $message->delete();
        });

        return $this->canMessage($request->user(), $contact)
            ? redirect()->route('chat.show', $contact)->with('success', 'Message unsent.')
            : redirect()->route('chat.index')->with('success', 'Message unsent.');
    }

    private function contacts(User $user, string $search = ''): Collection
    {
        return $this->contactQuery($user)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $matches) use ($search): void {
                    $matches->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->get();
    }

    private function conversationQuery(User $user, User $contact): Builder
    {
        return Message::query()->where(function (Builder $conversation) use ($user, $contact): void {
            $conversation->where(function (Builder $query) use ($user, $contact): void {
                $query->where('sender_id', $user->id)->where('recipient_id', $contact->id);
            })->orWhere(function (Builder $query) use ($user, $contact): void {
                $query->where('sender_id', $contact->id)->where('recipient_id', $user->id);
            });
        });
    }

    private function contactQuery(User $user): Builder
    {
        $query = User::whereKeyNot($user->id)->whereIn('role', self::CHAT_ROLES);

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isCoach()) {
            return $query->where(function (Builder $contacts) use ($user): void {
                $contacts->where('role', 'admin')
                    ->orWhereHas('member.coach', fn (Builder $coach): Builder => $coach->where('user_id', $user->id));
            });
        }

        $assignedCoachUserId = $user->member?->coach?->user_id;

        return $query->where(function (Builder $contacts) use ($assignedCoachUserId): void {
            $contacts->where('role', 'admin');

            if ($assignedCoachUserId !== null) {
                $contacts->orWhereKey($assignedCoachUserId);
            }
        });
    }

    private function canMessage(User $sender, User $recipient): bool
    {
        return in_array($sender->role, self::CHAT_ROLES, true)
            && $this->contactQuery($sender)->whereKey($recipient->id)->exists();
    }
}
