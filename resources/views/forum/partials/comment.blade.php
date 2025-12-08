<div class="comment {{ $level > 0 ? 'ml-8 border-l-2 border-gray-200 pl-4' : '' }}">
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        {{-- Comment Header --}}
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                    <span class="text-yellow-700 font-bold text-sm">{{ substr($comment->user->username_pub, 0, 1) }}</span>
                </div>
                <div>
                    <div class="font-medium text-sm">
                        <a href="{{ route('profile.show', $comment->user->username_pub) }}"
                           class="text-yellow-700 hover:text-yellow-600">
                            {{ $comment->user->username_pub }}
                        </a>
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $comment->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>

            {{-- Comment Actions --}}
            <div class="flex items-center space-x-2 text-xs">
                @can('delete', $comment)
                    <form action="{{ route('forum.comments.destroy', $comment) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="text-red-600 hover:text-red-800">
                            Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        {{-- Comment Content --}}
        <div class="whitespace-pre-wrap text-gray-900 text-sm">{{ $comment->body }}</div>

        {{-- Reply Form --}}
        @if(!$post->is_locked && $level < 3)
            <details class="mt-4 pt-4 border-t border-gray-200">
                <summary class="cursor-pointer text-sm text-gray-600 hover:text-gray-800 font-medium">
                    Reply to this comment
                </summary>
                <div class="mt-3">
                    <form action="{{ route('forum.comments.reply', $comment) }}" method="POST">
                        @csrf
                        <div class="space-y-3">
                            <textarea name="body"
                                      rows="3"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500 text-sm"
                                      placeholder="Write a reply..."
                                      required
                                      maxlength="5000"></textarea>
                            <div class="flex justify-end">
                                <button type="submit"
                                        class="px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm">
                                    Post Reply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </details>
        @endif

        {{-- Report Comment --}}
        @if(auth()->id() !== $comment->user_id)
            <details class="mt-4 pt-4 border-t border-gray-200">
                <summary class="cursor-pointer text-sm text-red-600 hover:text-red-800 font-medium">
                    Report this comment
                </summary>
                <div class="mt-3">
                    <form action="{{ route('forum.comments.report', $comment) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="reason-{{ $comment->id }}" class="block text-sm font-medium text-gray-700 mb-2">
                                    Reason for reporting
                                </label>
                                <textarea name="reason"
                                          id="reason-{{ $comment->id }}"
                                          rows="4"
                                          class="block w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-yellow-500"
                                          placeholder="Please explain why you are reporting this comment..."
                                          required
                                          maxlength="1000"></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                    Submit Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </details>
        @endif
    </div>

    {{-- Nested Replies --}}
    @if($comment->replies->count() > 0 && $level < 3)
        <div class="mt-4 space-y-4">
            @foreach($comment->replies as $reply)
                @include('forum.partials.comment', ['comment' => $reply, 'post' => $post, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
