@extends('layouts.portal')
@section('title', 'Messages')
@section('content')
<x-page-heading eyebrow="Private communication" title="Messages" :description="match (auth()->user()->role) { 'admin' => 'Message any portal user.', 'coach' => 'Message administrators and your assigned clients.', default => 'Message administrators and your assigned coach.' }" />

<div class="rush-card mt-6 grid min-h-[38rem] overflow-hidden lg:grid-cols-[19rem_1fr]">
    <aside class="border-b border-zinc-200 bg-zinc-50 p-3 lg:border-b-0 lg:border-r">
        <form method="GET" action="{{ $contact ? route('chat.show', $contact) : route('chat.index') }}" role="search" class="mb-3">
            <label class="sr-only" for="contact-search">Search chats</label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input id="contact-search" class="rush-input min-h-11 pl-9 text-sm" type="search" name="contact_search" value="{{ $contactSearch }}" placeholder="Search chats…" maxlength="100">
            </div>
            @if($messageSearch !== '')<input type="hidden" name="message_search" value="{{ $messageSearch }}">@endif
        </form>
        <p class="px-3 py-2 text-xs font-bold uppercase tracking-wider text-zinc-500">Contacts</p>
        <nav class="grid max-h-48 gap-1 overflow-y-auto lg:max-h-[30rem]" aria-label="Chat contacts">
            @forelse($contacts as $person)
                <a href="{{ route('chat.show', ['contact' => $person, 'contact_search' => $contactSearch]) }}" class="flex items-center gap-3 rounded-xl p-3 {{ $contact?->is($person) ? 'bg-lime-100' : 'hover:bg-white' }}">
                    <span class="rush-avatar !size-9">{{ mb_strtoupper(mb_substr($person->name, 0, 1)) }}</span>
                    <span class="min-w-0"><strong class="block truncate text-sm">{{ $person->name }}</strong><span class="text-xs capitalize text-zinc-500">{{ $person->role }}</span></span>
                </a>
            @empty
                <p class="p-3 text-sm text-zinc-500">{{ $contactSearch ? 'No matching contacts.' : 'No contacts available.' }}</p>
            @endforelse
        </nav>
    </aside>

    <section class="flex min-h-96 min-w-0 flex-col">
        @if($contact)
            <header class="flex items-center justify-between gap-3 border-b border-zinc-200 p-4">
                <div class="min-w-0"><p class="truncate font-bold">{{ $contact->name }}</p><p class="text-xs capitalize text-zinc-500">{{ $contact->role }}</p></div>
                <details class="rush-menu relative shrink-0">
                    <summary class="flex size-11 cursor-pointer items-center justify-center rounded-xl border border-zinc-200 bg-white text-xl font-bold" aria-label="Conversation options">⋯</summary>
                    <div class="absolute right-0 top-full z-30 mt-2 grid w-60 gap-1 rounded-xl border border-zinc-200 bg-white p-2 shadow-xl">
                        <div class="flex items-center justify-between gap-2 px-2 py-1"><span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Conversation</span><button type="button" class="rounded-lg px-2 py-1 font-bold text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950" data-menu-close aria-label="Close conversation options">✕</button></div>
                        <button type="button" class="rounded-lg px-3 py-2 text-left text-sm font-semibold hover:bg-zinc-50" data-chat-panel-target="conversation-search">Search conversation</button>
                        <button type="button" class="rounded-lg px-3 py-2 text-left text-sm font-semibold hover:bg-zinc-50" data-chat-panel-target="conversation-media">View sent media/photos</button>
                        <a href="{{ route('chat.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Exit conversation</a>
                    </div>
                </details>
            </header>

            <section class="border-b border-zinc-200 bg-zinc-50 p-4" data-chat-panel="conversation-search" @if($messageSearch === '') hidden @endif>
                <div class="mb-3 flex items-center justify-between gap-3"><h2 class="font-bold">Search conversation</h2><button type="button" class="rounded-lg px-2 py-1 font-bold text-zinc-500 hover:bg-zinc-200 hover:text-zinc-950" data-chat-panel-close aria-label="Close conversation search">✕</button></div>
                <form method="GET" action="{{ route('chat.show', $contact) }}" role="search" class="flex gap-2">
                    <label class="sr-only" for="message-search">Search this conversation</label>
                    <input id="message-search" class="rush-input min-h-11" type="search" name="message_search" value="{{ $messageSearch }}" placeholder="Search messages or attachment names…" maxlength="100">
                    @if($contactSearch !== '')<input type="hidden" name="contact_search" value="{{ $contactSearch }}">@endif
                    <button type="submit" class="rush-secondary">Search</button>
                    @if($messageSearch !== '')<a href="{{ route('chat.show', ['contact' => $contact, 'contact_search' => $contactSearch]) }}" class="rush-secondary">Clear</a>@endif
                </form>
                @if($messageSearch !== '')<p class="mt-2 text-xs text-zinc-500">{{ $messages->count() }} {{ Str::plural('result', $messages->count()) }} for “{{ $messageSearch }}”</p>@endif
            </section>

            <section class="border-b border-zinc-200 bg-zinc-50 p-4" data-chat-panel="conversation-media" hidden>
                <div class="mb-3 flex items-center justify-between gap-3"><h2 class="font-bold">Shared media</h2><div class="flex items-center gap-2"><span class="text-xs text-zinc-500">{{ $mediaMessages->count() }} items</span><button type="button" class="rounded-lg px-2 py-1 font-bold text-zinc-500 hover:bg-zinc-200 hover:text-zinc-950" data-chat-panel-close aria-label="Close shared media">✕</button></div></div>
                <div class="grid max-h-72 grid-cols-2 gap-3 overflow-y-auto sm:grid-cols-3">
                    @forelse($mediaMessages as $mediaMessage)
                        @if($mediaMessage->attachment_type === 'image')
                            <a href="{{ route('chat.attachment', $mediaMessage) }}" target="_blank" rel="noopener" class="overflow-hidden rounded-xl border border-zinc-200 bg-white"><img class="aspect-square w-full object-cover" src="{{ route('chat.attachment', $mediaMessage) }}" alt="{{ $mediaMessage->attachment_name }}"></a>
                        @else
                            <div class="rounded-xl border border-zinc-200 bg-white p-3"><p class="mb-2 truncate text-xs font-semibold">{{ $mediaMessage->attachment_name }}</p><audio class="w-full" controls preload="metadata" src="{{ route('chat.attachment', $mediaMessage) }}"></audio></div>
                        @endif
                    @empty
                        <p class="col-span-full py-8 text-center text-sm text-zinc-500">No photos or voice messages in this conversation.</p>
                    @endforelse
                </div>
            </section>

            <div class="flex-1 space-y-3 overflow-y-auto p-4 sm:p-6" data-chat-messages>
                @forelse($messages as $message)
                    <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm leading-6 sm:max-w-[75%] {{ $message->sender_id === auth()->id() ? 'bg-zinc-950 text-white' : 'bg-zinc-100 text-zinc-800' }}">
                            @if($message->body)<p class="whitespace-pre-wrap">{{ $message->body }}</p>@endif
                            @if($message->attachment_type === 'image')
                                <a href="{{ route('chat.attachment', $message) }}" target="_blank" rel="noopener" class="mt-2 block overflow-hidden rounded-xl bg-white/10"><img class="max-h-80 w-full object-contain" src="{{ route('chat.attachment', $message) }}" alt="{{ $message->attachment_name }}"></a>
                            @elseif($message->attachment_type === 'audio')
                                <div class="mt-2 min-w-56 rounded-xl bg-white p-2 text-zinc-900"><p class="mb-1 truncate text-xs font-semibold">Voice message</p><audio class="w-full" controls preload="metadata" src="{{ route('chat.attachment', $message) }}"></audio></div>
                            @endif
                            <time class="mt-1 block text-[0.65rem] opacity-60">{{ $message->created_at->format('M j · g:i A') }}</time>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-sm text-zinc-500">{{ $messageSearch ? 'No messages match your search.' : 'Start the conversation.' }}</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('chat.store', $contact) }}" enctype="multipart/form-data" class="border-t border-zinc-200 p-3 sm:p-4" data-chat-compose>
                @csrf
                <div class="flex items-end gap-2">
                    <input type="file" name="attachment" accept="image/jpeg,image/png,image/gif,image/webp" hidden data-chat-attachment>
                    <button type="button" class="rush-secondary size-12 shrink-0 !px-0" data-chat-photo aria-label="Attach a photo" title="Attach a photo"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-5-5L5 20"/></svg></button>
                    <button type="button" class="rush-secondary size-12 shrink-0 !px-0" data-chat-camera aria-label="Take a photo" title="Take a photo"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h3l1.5-2h7L17 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z"/><circle cx="12" cy="13" r="4"/></svg></button>
                    <label class="sr-only" for="message-body">Message</label>
                    <textarea id="message-body" class="rush-input min-h-12 resize-none py-3" name="body" placeholder="Write a private message…" maxlength="3000">{{ old('body') }}</textarea>
                    <button class="rush-primary min-h-12 shrink-0 self-end" type="submit" data-chat-send>Send</button>
                </div>
                <div class="mt-2 flex min-h-5 items-center justify-between gap-3 text-xs"><span class="truncate text-zinc-500" data-chat-attachment-status aria-live="polite">Attach or take a photo (up to 2 MB).</span><button type="button" class="font-semibold text-red-600" data-chat-attachment-clear hidden>Remove</button></div>
                <img class="mt-2 max-h-48 rounded-xl border border-zinc-200 object-contain" data-chat-photo-preview alt="Selected photo preview" hidden>
                <x-input-error :messages="$errors->all()" />

                <dialog class="m-auto w-[min(42rem,calc(100vw-2rem))] rounded-2xl bg-zinc-950 p-0 text-white shadow-2xl backdrop:bg-zinc-950/75" data-chat-camera-dialog>
                    <div class="p-4 sm:p-5">
                        <div class="mb-4 flex items-center justify-between"><div><h2 class="font-bold">Take a photo</h2><p class="text-xs text-zinc-400">Allow camera access, then capture your image.</p></div><button type="button" class="rounded-lg px-3 py-2 font-bold text-zinc-300 hover:bg-zinc-800" data-chat-camera-close aria-label="Close camera">✕</button></div>
                        <div class="overflow-hidden rounded-xl bg-black"><video class="aspect-video w-full object-cover" autoplay playsinline muted data-chat-camera-video></video><canvas data-chat-camera-canvas hidden></canvas></div>
                        <p class="mt-3 text-sm text-amber-300" data-chat-camera-status aria-live="polite">Starting camera…</p>
                        <div class="mt-4 flex justify-end gap-3"><button type="button" class="rush-dark-button" data-chat-camera-cancel>Cancel</button><button type="button" class="rush-primary" data-chat-camera-capture>Capture photo</button></div>
                    </div>
                </dialog>
            </form>
        @else
            <div class="grid flex-1 place-items-center p-8 text-center"><div><p class="font-bold">Choose a contact</p><p class="mt-2 text-sm text-zinc-500">Your conversation will appear here.</p></div></div>
        @endif
    </section>
</div>
@endsection
