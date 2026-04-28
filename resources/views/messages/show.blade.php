@extends('layouts.app')

@section('page-title', 'Messages with ' . $other->username_pub)
@section('page-heading', 'Messages with ' . $other->username_pub)

@section('breadcrumbs')
<a href="{{ route('messages.index') }}" class="text-amber-600 hover:text-amber-800">Messages</a>
<span class="text-gray-400">/</span>
<span>{{ $other->username_pub }}</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto flex flex-col gap-4">

    {{-- Chat window --}}
    <div class="border border-gray-200 rounded-xl overflow-hidden">

        {{-- Header --}}
        <div class="px-4 py-3 bg-white border-b border-gray-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr($other->username_pub, 0, 1)) }}
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $other->username_pub }}</p>
                <p class="text-xs text-gray-400">{{ $messages->count() }} {{ Str::plural('message', $messages->count()) }}</p>
            </div>
        </div>

        {{-- Messages --}}
        <div class="bg-gray-50 px-4 py-6 flex flex-col gap-5">

            @forelse($messages as $message)
            @php $isMine = $message->sender_id === auth()->id(); @endphp

            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                <div class="flex flex-col {{ $isMine ? 'items-end' : 'items-start' }}" style="max-width: 72%">

                    @unless($isMine)
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">
                        {{ $other->username_pub }}
                    </span>
                    @endunless

                    {{--
                        Explicit per-corner rounding — avoids shorthand cascade conflicts.
                        Mine:   TL TR BL all 2xl, BR = none  → sharp bottom-right tail
                        Theirs: TR BR BL all 2xl, TL = none  → sharp top-left tail
                    --}}
                    <div class="px-4 py-3 text-sm leading-relaxed break-words
                                {{ $isMine
                                    ? 'bg-amber-500 text-white rounded-tl-2xl rounded-tr-2xl rounded-bl-2xl rounded-br-none'
                                    : 'bg-white text-gray-800 border border-gray-200 rounded-tl-none rounded-tr-2xl rounded-br-2xl rounded-bl-2xl' }}">
                        <p class="whitespace-pre-wrap">{{ $message->message }}</p>
                    </div>

                    <span class="text-[10px] text-gray-400 mt-1.5 {{ $isMine ? 'mr-0.5' : 'ml-0.5' }}">
                        {{ $message->created_at->format('d M Y, H:i') }}
                    </span>

                </div>
            </div>

            @empty
            <div class="py-10 text-center text-sm text-gray-400">
                No messages yet. Send the first one below.
            </div>
            @endforelse

        </div>
    </div>

    {{-- Reply form --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <form action="{{ route('messages.store', $thread) }}" method="POST">
            @csrf
            <textarea name="message" rows="3"
                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent resize-none"
                      placeholder="Write a message..." required maxlength="2000">{{ old('message') }}</textarea>
            @error('message')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            <div class="flex justify-end mt-2">
                <button type="submit"
                        class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    Send
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
