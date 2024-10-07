<div class="favorite-list-item">
    @if ($user)
        <div data-id="{{ $user->id }}" data-action="0" class="avatar av-m"
            style="background-image: url('{{ asset('/storage/' . config('chatify.user_avatar.folder') . '/' . $user->avatar) }}');">
        </div>
        <p>{{ Str::limit($user->name, 6) }}</p>
    @endif
</div>
