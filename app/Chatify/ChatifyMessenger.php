<?php

namespace App\Chatify;

use App\Models\ChMessage as Message;
use App\Models\ChFavorite as Favorite;
use Illuminate\Support\Facades\Storage;
use Pusher\Pusher;
use Illuminate\Support\Facades\Auth;
use Exception;

class ChatifyMessenger
{
    public $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            config('chatify.pusher.key'),
            config('chatify.pusher.secret'),
            config('chatify.pusher.app_id'),
            config('chatify.pusher.options'),
        );
    }

    public function getMaxUploadSize()
    {
        return config('chatify.attachments.max_upload_size') * 1048576;
    }

    public function getAllowedImages()
    {
        return config('chatify.attachments.allowed_images');
    }

    public function getAllowedFiles()
    {
        return config('chatify.attachments.allowed_files');
    }

    public function getMessengerColors()
    {
        return config('chatify.colors');
    }

    public function getFallbackColor()
    {
        $colors = $this->getMessengerColors();
        return count($colors) > 0 ? $colors[0] : '#000000';
    }

    public function push($channel, $event, $data)
    {
        return $this->pusher->trigger($channel, $event, $data);
    }

    public function pusherAuth($requestUser, $authUser, $channelName, $socket_id)
    {
        $authData = json_encode([
            'user_id' => $authUser->id,
            'user_info' => [
                'name' => $authUser->name
            ]
        ]);
        if (Auth::check() && $requestUser->id == $authUser->id) {
            return $this->pusher->socket_auth($channelName, $socket_id, $authData);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function parseMessage($prefetchedMessage = null, $id = null)
    {
        $msg = $prefetchedMessage ?? Message::find($id);
        if (!$msg) {
            return [];
        }
        $attachment = null;
        $attachment_type = null;
        $attachment_title = null;
        if (isset($msg->attachment)) {
            $attachmentOBJ = json_decode($msg->attachment);
            $attachment = $attachmentOBJ->new_name;
            $attachment_title = htmlentities(trim($attachmentOBJ->old_name), ENT_QUOTES, 'UTF-8');
            $ext = pathinfo($attachment, PATHINFO_EXTENSION);
            $attachment_type = in_array($ext, $this->getAllowedImages()) ? 'image' : 'file';
        }
        return [
            'id' => $msg->id,
            'from_id' => $msg->from_id,
            'to_id' => $msg->to_id,
            'message' => $msg->body,
            'attachment' => (object) [
                'file' => $attachment,
                'title' => $attachment_title,
                'type' => $attachment_type
            ],
            'timeAgo' => $msg->created_at->diffForHumans(),
            'created_at' => $msg->created_at->toIso8601String(),
            'isSender' => ($msg->from_id == Auth::id()),
            'seen' => $msg->seen,
        ];
    }

    public function messageCard($data, $renderDefaultCard = false)
    {
        if (!$data) {
            return '';
        }
        if ($renderDefaultCard) {
            $data['isSender'] = false;
        }
        return view('chatify.layouts.messageCard', $data)->render();
    }

    public function fetchMessagesQuery($user_id)
    {
        return Message::where(function ($query) use ($user_id) {
            $query->where('from_id', Auth::id())->where('to_id', $user_id)
                ->orWhere('from_id', $user_id)->where('to_id', Auth::id());
        });
    }

    public function newMessage($data)
    {
        return Message::create($data);
    }

    public function makeSeen($user_id)
    {
        return Message::where('from_id', $user_id)
            ->where('to_id', Auth::id())
            ->where('seen', 0)
            ->update(['seen' => 1]);
    }

    public function getLastMessageQuery($user_id)
    {
        return $this->fetchMessagesQuery($user_id)->latest()->first();
    }

    public function countUnseenMessages($user_id)
    {
        return Message::where('from_id', $user_id)->where('to_id', Auth::id())->where('seen', 0)->count();
    }

    public function getContactItem($user)
    {
        try {
            $lastMessage = $this->getLastMessageQuery($user->id);
            $unseenCounter = $this->countUnseenMessages($user->id);
            if ($lastMessage) {
                $lastMessage->created_at = $lastMessage->created_at->toIso8601String();
                $lastMessage->timeAgo = $lastMessage->created_at->diffForHumans();
            }
            return view('chatify.layouts.listItem', [
                'get' => 'users',
                'user' => $this->getUserWithAvatar($user),
                'lastMessage' => $lastMessage,
                'unseenCounter' => $unseenCounter,
            ])->render();
        } catch (\Throwable $th) {
            report($th);
            return '';
        }
    }

    public function getUserWithAvatar($user)
    {
        if (!$user) {
            return null;
        }

        $defaultAvatarPath = 'avatar.png';
        $userAvatarFolder = config('chatify.user_avatar.folder');

        if (empty($user->avatar) || $user->avatar == $defaultAvatarPath) {
            // Gunakan avatar default
            $user->avatar = asset("storage/{$defaultAvatarPath}");
        } else {
            // Periksa apakah avatar pengguna ada di storage
            $userAvatarPath = "{$userAvatarFolder}/{$user->avatar}";
            if (Storage::disk(config('chatify.storage_disk_name'))->exists($userAvatarPath)) {
                $user->avatar = Storage::disk(config('chatify.storage_disk_name'))->url($userAvatarPath);
            } else {
                // Jika file tidak ditemukan, gunakan avatar default
                $user->avatar = asset("storage/{$defaultAvatarPath}");
            }
        }
        return $user;
    }

    public function inFavorite($user_id)
    {
        return Favorite::where('user_id', Auth::id())
            ->where('favorite_id', $user_id)
            ->exists();
    }

    public function makeInFavorite($user_id, $action)
    {
        if ($action > 0) {
            return Favorite::create([
                'user_id' => Auth::id(),
                'favorite_id' => $user_id,
            ]);
        } else {
            return Favorite::where('user_id', Auth::id())
                ->where('favorite_id', $user_id)
                ->delete();
        }
    }

    public function getSharedPhotos($user_id)
    {
        return $this->fetchMessagesQuery($user_id)
            ->whereNotNull('attachment')
            ->get()
            ->reduce(function ($carry, $message) {
                $attachment = json_decode($message->attachment);
                if (in_array(pathinfo($attachment->new_name, PATHINFO_EXTENSION), $this->getAllowedImages())) {
                    $carry[] = $attachment->new_name;
                }
                return $carry;
            }, []);
    }

    public function deleteConversation($user_id)
    {
        try {
            $messages = $this->fetchMessagesQuery($user_id)->get();
            foreach ($messages as $message) {
                $this->deleteMessage($message->id);
            }
            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function deleteMessage($id)
    {
        try {
            $message = Message::findOrFail($id);
            if ($message->from_id != Auth::id()) {
                return false;
            }
            if (isset($message->attachment)) {
                $path = config('chatify.attachments.folder') . '/' . json_decode($message->attachment)->new_name;
                if ($this->storage()->exists($path)) {
                    $this->storage()->delete($path);
                }
            }
            $message->delete();
            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function storage()
    {
        return Storage::disk(config('chatify.storage_disk_name'));
    }

    public function getUserAvatarUrl($user_avatar_name)
    {
        return $this->storage()->url(config('chatify.user_avatar.folder') . '/' . $user_avatar_name);
    }

    public function getAttachmentUrl($attachment_name)
    {
        return $this->storage()->url(config('chatify.attachments.folder') . '/' . $attachment_name);
    }
}
