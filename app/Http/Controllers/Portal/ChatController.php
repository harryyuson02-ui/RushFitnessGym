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
use Illuminate\View\View;

class ChatController extends Controller
{
    private const CHAT_ROLES = ['admin', 'coach', 'user'];

    private const MEMBER_CONTACT_ROLES = ['admin', 'coach'];

    public function index(Request $request): View
    {
        return view('portal.chat', [
            'contacts' => $this->contacts($request->user()),
            'contact' => null,
            'messages' => collect(),
        ]);
    }

    public function show(Request $request, User $contact): View
    {
        abort_unless($this->canMessage($request->user(), $contact), 403);

        Message::where('sender_id', $contact->id)->where('recipient_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        $messages = Message::query()
            ->where(function (Builder $query) use ($request, $contact): void {
                $query->where('sender_id', $request->user()->id)->where('recipient_id', $contact->id);
            })
            ->orWhere(function (Builder $query) use ($request, $contact): void {
                $query->where('sender_id', $contact->id)->where('recipient_id', $request->user()->id);
            })
            ->oldest()
            ->get();

        return view('portal.chat', ['contacts' => $this->contacts($request->user()), 'contact' => $contact, 'messages' => $messages]);
    }

    public function store(StoreMessageRequest $request, User $contact): RedirectResponse
    {
        abort_unless($this->canMessage($request->user(), $contact), 403);
        $message = Message::create([
            'sender_id' => $request->user()->id,
            'recipient_id' => $contact->id,
            'body' => $request->validated('body'),
        ]);
        $message->load('sender');
        $contact->notify(new NewMessageReceived($message));

        return redirect()->route('chat.show', $contact);
    }

    private function contacts(User $user): Collection
    {
        return $this->contactQuery($user)->orderBy('name')->get();
    }

    private function contactQuery(User $user): Builder
    {
        $contactRoles = $user->isMember() ? self::MEMBER_CONTACT_ROLES : self::CHAT_ROLES;

        return User::whereKeyNot($user->id)
            ->whereIn('role', $contactRoles)
            ->when($user->isCoach(), function (Builder $query) use ($user): void {
                $query->where(function (Builder $contacts) use ($user): void {
                    $contacts->where('role', 'admin')
                        ->orWhereHas('member.coach', fn (Builder $coach): Builder => $coach->where('user_id', $user->id))
                        ->orWhereHas('sentMessages', fn (Builder $messages): Builder => $messages->where('recipient_id', $user->id));
                });
            });
    }

    private function canMessage(User $sender, User $recipient): bool
    {
        return in_array($sender->role, self::CHAT_ROLES, true)
            && $this->contactQuery($sender)->whereKey($recipient->id)->exists();
    }
}
