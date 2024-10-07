<?php

namespace App\Http\Controllers\Chatify;

use Carbon\Carbon;
use App\Models\User;
use App\Services\AIService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\GenerateAIResponse;
use Illuminate\Http\JsonResponse;
use App\Events\NewMessageReceived;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ChMessage as Message;
use Illuminate\Support\Facades\Auth;
use App\Models\ChFavorite as Favorite;
use Illuminate\Support\Facades\Response;
use App\Facades\ChatifyMessenger as Chatify;
use App\Services\DoctorRecommendationService;



class MessagesController extends Controller
{
    protected $perPage = 30;
    protected $aiService;
    protected $doctorRecommendationService;

    public function __construct(AIService $aiService, DoctorRecommendationService $doctorRecommendationService)
    {
        $this->aiService = $aiService;
        $this->doctorRecommendationService = $doctorRecommendationService;
    }

    public function pusherAuth(Request $request)
    {
        return Chatify::pusherAuth(
            $request->user(),
            Auth::user(),
            $request['channel_name'],
            $request['socket_id']
        );
    }

    /**
     * Returning the view of the app with the required data.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index($id = null)
    {
        $messenger_color = Auth::user()->messenger_color;
        return view('Chatify::pages.app', [
            'id' => $id ?? 0,
            'messengerColor' => $messenger_color ? $messenger_color : Chatify::getFallbackColor(),
            'dark_mode' => Auth::user()->dark_mode < 1 ? 'light' : 'dark',
        ]);
    }


    /**
     * Fetch data (user, favorite.. etc).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function idFetchData(Request $request)
    {
        $favorite = Chatify::inFavorite($request['id']);
        $fetch = User::where('id', $request['id'])->select('id', 'name', 'email')->first();
        if ($fetch) {
            $userAvatar = Chatify::getUserWithAvatar($fetch)->avatar;
        }
        unset($fetch['email']);
        return Response::json([
            'favorite' => $favorite,
            'fetch' => $fetch ?? null,
            'user_avatar' => $userAvatar ?? null,
        ]);
    }

    /**
     * This method to make a links for the attachments
     * to be downloadable.
     *
     * @param string $fileName
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|void
     */
    public function download($fileName)
    {
        $filePath = config('chatify.attachments.folder') . '/' . $fileName;
        if (Chatify::storage()->exists($filePath)) {
            return Chatify::storage()->download($filePath);
        }
        return abort(404, "Sorry, File does not exist in our server or may have been deleted!");
    }

    /**
     * Send a message to database
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request)
    {
        $error = (object) [
            'status' => 0,
            'message' => null
        ];
        $attachment = null;
        $attachment_title = null;

        // if there is attachment [file]
        if ($request->hasFile('file')) {
            // allowed extensions
            $allowed_images = Chatify::getAllowedImages();
            $allowed_files = Chatify::getAllowedFiles();
            $allowed = array_merge($allowed_images, $allowed_files);

            $file = $request->file('file');
            // check file size
            if ($file->getSize() < Chatify::getMaxUploadSize()) {
                if (in_array(strtolower($file->extension()), $allowed)) {
                    // get attachment name
                    $attachment_title = $file->getClientOriginalName();
                    // upload attachment and store the new name
                    $attachment = Str::uuid() . "." . $file->extension();
                    $file->storeAs(config('chatify.attachments.folder'), $attachment, config('chatify.storage_disk_name'));
                } else {
                    $error->status = 1;
                    $error->message = "File extension not allowed!";
                }
            } else {
                $error->status = 1;
                $error->message = "File size you are trying to upload is too large!";
            }
        }

        if (!$error->status) {
            $message = Chatify::newMessage([
                'from_id' => Auth::user()->id,
                'to_id' => $request['id'],
                'body' => htmlentities(trim($request['message']), ENT_QUOTES, 'UTF-8'),
                'attachment' => ($attachment) ? json_encode((object) [
                    'new_name' => $attachment,
                    'old_name' => htmlentities(trim($attachment_title), ENT_QUOTES, 'UTF-8'),
                ]) : null,
            ]);

            Log::info('MessagesController: New message created', [
                'messageId' => $message->id,
                'fromId' => $message->from_id,
                'fromIdType' => gettype($message->from_id)
            ]);

            $messageData = Chatify::parseMessage($message);

            if (Auth::user()->id != $request['id']) {
                Chatify::push("private-chatify." . $request['id'], 'messaging', [
                    'from_id' => Auth::user()->id,
                    'to_id' => $request['id'],
                    'message' => Chatify::messageCard($messageData, true)
                ]);
            }

            // Trigger AI response if recipient is a doctor
            $recipient = User::find($request['id']);
            if ($recipient && $recipient->isDoctor()) {
                $this->scheduleAIResponse($message);
            }

            event(new NewMessageReceived($message));

            return Response::json([
                'status' => '200',
                'error' => $error ?? null,
                'message' => Chatify::messageCard(@$messageData),
                'tempID' => $request['temporaryMsgId'],
            ]);
        }

        return Response::json([
            'status' => '400',
            'error' => $error ?? null
        ]);
    }


    private function scheduleAIResponse($originalMessage)
    {
        Log::info('MessagesController: Scheduling AI response', ['messageId' => $originalMessage->id]);
        GenerateAIResponse::dispatch($originalMessage)->delay(now()->addSeconds(30));
    }

    public function generateAIResponse($originalMessage)
    {
        Log::info('MessagesController: generateAIResponse method called', ['messageId' => $originalMessage->id]);

        $latestMessage = Message::where('to_id', $originalMessage->to_id)
            ->where('from_id', $originalMessage->to_id)
            ->where('created_at', '>', $originalMessage->created_at)
            ->latest()
            ->first();

        Log::info('MessagesController: Checked for latest message', [
            'latestMessageExists' => !is_null($latestMessage)
        ]);

        if (!$latestMessage) {
            $context = $this->getConversationContext($originalMessage);
            Log::info('MessagesController: Got conversation context', [
                'context' => $context
            ]);

            $userId = strval($originalMessage->from_id);
            Log::info('MessagesController: User ID prepared', [
                'userId' => $userId,
                'userIdType' => gettype($userId),
                'originalFromId' => $originalMessage->from_id,
                'originalFromIdType' => gettype($originalMessage->from_id)
            ]);

            try {
                $aiResponse = $this->aiService->generateResponse($originalMessage->body, $userId, $context);

                if ($aiResponse) {
                    $this->processAIResponse($aiResponse, $originalMessage);
                } else {
                    Log::error('MessagesController: Failed to generate AI response');
                }
            } catch (\InvalidArgumentException $e) {
                Log::error('MessagesController: ' . $e->getMessage(), [
                    'userId' => $userId,
                    'originalFromId' => $originalMessage->from_id,
                    'trace' => $e->getTraceAsString()
                ]);
            } catch (\Exception $e) {
                Log::error('MessagesController: Unexpected error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::info('MessagesController: No AI response generated (latest message exists)');
        }
    }

    private function processAIResponse($aiResponse, $originalMessage)
    {
        try {
            $aiMessage = Chatify::newMessage([
                'from_id' => $originalMessage->to_id,
                'to_id' => $originalMessage->from_id,
                'body' => $aiResponse['content'],
                'is_ai_response' => true,
                'sentiment' => $aiResponse['sentiment'] ?? null,
                'keywords' => json_encode($aiResponse['keywords'] ?? []),
                'ai_confidence' => $aiResponse['ai_confidence'] ?? null
            ]);

            Log::info('MessagesController: AI message saved to database', [
                'messageId' => $aiMessage->id,
                'content' => $aiMessage->body
            ]);

            $messageData = Chatify::parseMessage($aiMessage);

            $keywords = $aiResponse['keywords'] ?? [];
            $recommendedDoctor = $this->doctorRecommendationService->getRecommendedDoctor($originalMessage->from_id, $keywords);

            if ($recommendedDoctor) {
                $aiMessage->body .= "\n\nBerdasarkan keluhan Anda, saya merekomendasikan untuk berkonsultasi dengan Dr. " . $recommendedDoctor->name . ", yang merupakan spesialis " . $recommendedDoctor->spesialis . ".";
                $aiMessage->save();
            }

            $this->broadcastMessage($aiMessage, $messageData);

            event(new NewMessageReceived($aiMessage));
            Log::info('MessagesController: NewMessageReceived event dispatched', ['messageId' => $aiMessage->id]);
        } catch (\Exception $e) {
            Log::error('MessagesController: Error in processAIResponse', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function broadcastMessage($aiMessage, $messageData)
    {
        try {
            Chatify::push("private-chatify." . $aiMessage->to_id, 'messaging', [
                'from_id' => $aiMessage->from_id,
                'to_id' => $aiMessage->to_id,
                'message' => Chatify::messageCard($messageData, true)
            ]);

            Log::info('MessagesController: Pusher event sent', [
                'channel' => "private-chatify." . $aiMessage->to_id,
                'event' => 'messaging',
                'fromId' => $aiMessage->from_id,
                'toId' => $aiMessage->to_id
            ]);
        } catch (\Exception $e) {
            Log::error('MessagesController: Error sending Pusher event', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function getConversationContext($message)
    {
        $context = Message::where(function ($query) use ($message) {
            $query->where('from_id', $message->from_id)->where('to_id', $message->to_id)
                ->orWhere('from_id', $message->to_id)->where('to_id', $message->from_id);
        })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->reverse()
            ->map(function ($msg) use ($message) {
                return [
                    'role' => $msg->from_id === $message->from_id ? 'user' : 'assistant',
                    'content' => $msg->body
                ];
            })
            ->values()
            ->toArray();

        Log::info('MessagesController: Conversation context generated', ['context' => $context]);

        return $context;
    }

    public function getUnreadCount()
    {
        $count = Message::where('to_id', Auth::id())
            ->where('seen', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead(Request $request)
    {
        $messageIds = $request->input('message_ids', []);
        Message::whereIn('id', $messageIds)->update(['seen' => true]);

        return response()->json(['success' => true]);
    }

    public function getUserStatus($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $lastActivity = $user->last_activity_at ?? $user->updated_at;
        $isOnline = $lastActivity->diffInMinutes(now()) < 5;

        return response()->json([
            'is_online' => $isOnline,
            'last_seen' => $isOnline ? 'Online' : $lastActivity->diffForHumans()
        ]);
    }

    public function updateUserStatus()
    {
        Auth::user()->update(['last_activity_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function fetch(Request $request)
    {
        // Check if the user is an owner
        $isOwner = Auth::user()->hasRole('owner');

        if ($isOwner) {
            // For owner, fetch all messages
            $query = Message::latest();
        } else {
            // For non-owners, use the existing logic
            $query = Chatify::fetchMessagesQuery($request['id'])->latest();
        }

        $messages = $query->paginate($request->per_page ?? $this->perPage);
        $totalMessages = $messages->total();
        $lastPage = $messages->lastPage();
        $response = [
            'total' => $totalMessages,
            'last_page' => $lastPage,
            'last_message_id' => collect($messages->items())->last()->id ?? null,
            'messages' => '',
        ];

        // If there are messages
        if ($totalMessages > 0) {
            $allMessages = null;
            foreach ($messages->reverse() as $message) {
                $allMessages .= Chatify::messageCard(
                    Chatify::parseMessage($message)
                );
            }
            $response['messages'] = $allMessages;
        }

        return Response::json($response);
    }

    /**
     * Make messages as seen
     *
     * @param Request $request
     * @return JsonResponse|void
     */
    public function seen(Request $request)
    {
        // make as seen
        $seen = Chatify::makeSeen($request['id']);
        // send the response
        return Response::json([
            'status' => $seen,
        ], 200);
    }

    /**
     * Get contacts list
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getContacts(Request $request)
    {
        // Check if the user is an owner
        $isOwner = Auth::user()->hasRole('owner');

        if ($isOwner) {
            // For owner, get all users
            $users = User::where('id', '!=', Auth::user()->id)
                ->orderBy('name', 'asc')
                ->paginate($request->per_page ?? $this->perPage);
        } else {
            // For non-owners, use the existing logic
            $users = Message::join('users',  function ($join) {
                $join->on('ch_messages.from_id', '=', 'users.id')
                    ->orOn('ch_messages.to_id', '=', 'users.id');
            })
                ->where(function ($q) {
                    $q->where('ch_messages.from_id', Auth::user()->id)
                        ->orWhere('ch_messages.to_id', Auth::user()->id);
                })
                ->where('users.id', '!=', Auth::user()->id)
                ->select('users.*', DB::raw('MAX(ch_messages.created_at) max_created_at'))
                ->orderBy('max_created_at', 'desc')
                ->groupBy('users.id')
                ->paginate($request->per_page ?? $this->perPage);
        }

        $usersList = $users->items();

        if (count($usersList) > 0) {
            $contacts = '';
            foreach ($usersList as $user) {
                $contacts .= Chatify::getContactItem($user);
            }
        } else {
            $contacts = '<p class="message-hint center-el"><span>Your contact list is empty</span></p>';
        }

        return Response::json([
            'contacts' => $contacts,
            'total' => $users->total() ?? 0,
            'last_page' => $users->lastPage() ?? 1,
        ], 200);
    }

    /**
     * Update user's list item data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateContactItem(Request $request)
    {
        // Get user data
        $user = User::where('id', $request['user_id'])->first();
        if (!$user) {
            return Response::json([
                'message' => 'User not found!',
            ], 401);
        }
        $contactItem = Chatify::getContactItem($user);

        // send the response
        return Response::json([
            'contactItem' => $contactItem,
        ], 200);
    }

    /**
     * Put a user in the favorites list
     *
     * @param Request $request
     * @return JsonResponse|void
     */
    public function favorite(Request $request)
    {
        $userId = $request['user_id'];
        // check action [star/unstar]
        $favoriteStatus = Chatify::inFavorite($userId) ? 0 : 1;
        Chatify::makeInFavorite($userId, $favoriteStatus);

        // send the response
        return Response::json([
            'status' => @$favoriteStatus,
        ], 200);
    }

    /**
     * Get favorites list
     *
     * @param Request $request
     * @return JsonResponse|void
     */
    public function getFavorites(Request $request)
    {
        $favoritesList = null;
        $favorites = Favorite::where('user_id', Auth::user()->id);
        foreach ($favorites->get() as $favorite) {
            // get user data
            $user = User::where('id', $favorite->favorite_id)->first();
            $favoritesList .= view('Chatify::layouts.favorite', [
                'user' => $user,
            ]);
        }
        // send the response
        return Response::json([
            'count' => $favorites->count(),
            'favorites' => $favorites->count() > 0
                ? $favoritesList
                : 0,
        ], 200);
    }

    /**
     * Search in messenger
     *
     * @param Request $request
     * @return JsonResponse|void
     */
    public function search(Request $request)
    {
        $getRecords = null;
        $input = trim(filter_var($request['input']));
        $records = User::where('id', '!=', Auth::user()->id)
            ->where('name', 'LIKE', "%{$input}%")
            ->paginate($request->per_page ?? $this->perPage);
        foreach ($records->items() as $record) {
            $getRecords .= view('Chatify::layouts.listItem', [
                'get' => 'search_item',
                'user' => Chatify::getUserWithAvatar($record),
            ])->render();
        }
        if ($records->total() < 1) {
            $getRecords = '<p class="message-hint center-el"><span>Nothing to show.</span></p>';
        }
        // send the response
        return Response::json([
            'records' => $getRecords,
            'total' => $records->total(),
            'last_page' => $records->lastPage()
        ], 200);
    }

    /**
     * Get shared photos
     *
     * @param Request $request
     * @return JsonResponse|void
     */
    public function sharedPhotos(Request $request)
    {
        $shared = Chatify::getSharedPhotos($request['user_id']);
        $sharedPhotos = null;

        // shared with its template
        for ($i = 0; $i < count($shared); $i++) {
            $sharedPhotos .= view('Chatify::layouts.listItem', [
                'get' => 'sharedPhoto',
                'image' => Chatify::getAttachmentUrl($shared[$i]),
            ])->render();
        }
        // send the response
        return Response::json([
            'shared' => count($shared) > 0 ? $sharedPhotos : '<p class="message-hint"><span>Nothing shared yet</span></p>',
        ], 200);
    }

    /**
     * Delete conversation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteConversation(Request $request)
    {
        $userId = Auth::user()->id;
        $messageId = $request['id'];

        // Hapus pesan reguler
        Message::where('from_id', $userId)->where('to_id', $messageId)->delete();
        Message::where('to_id', $userId)->where('from_id', $messageId)->delete();

        // Hapus pesan AI
        Message::where('from_id', $messageId)->where('to_id', $userId)->where('is_ai_response', true)->delete();

        return Response::json([
            'deleted' => true,
        ], 200);
    }

    /**
     * Delete message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMessage(Request $request)
    {
        $messageId = $request['id'];
        $userId = Auth::user()->id;

        // Hapus pesan reguler
        $message = Message::where('id', $messageId)->where('from_id', $userId)->first();

        // Jika pesan ditemukan dan dihapus
        if ($message && $message->delete()) {
            // Hapus pesan AI yang terkait (jika ada)
            Message::where('from_id', $message->to_id)
                ->where('to_id', $userId)
                ->where('is_ai_response', true)
                ->where('created_at', '>', $message->created_at)
                ->delete();

            return Response::json(['deleted' => true], 200);
        }

        return Response::json(['deleted' => false], 400);
    }

    public function updateSettings(Request $request)
    {
        $msg = null;
        $error = $success = 0;

        // dark mode
        if ($request['dark_mode']) {
            $request['dark_mode'] == "dark"
                ? User::where('id', Auth::user()->id)->update(['dark_mode' => 1])  // Make Dark
                : User::where('id', Auth::user()->id)->update(['dark_mode' => 0]); // Make Light
        }

        // If messenger color selected
        if ($request['messengerColor']) {
            $messenger_color = trim(filter_var($request['messengerColor']));
            User::where('id', Auth::user()->id)
                ->update(['messenger_color' => $messenger_color]);
        }
        // if there is a [file]
        if ($request->hasFile('avatar')) {
            // allowed extensions
            $allowed_images = Chatify::getAllowedImages();

            $file = $request->file('avatar');
            // check file size
            if ($file->getSize() < Chatify::getMaxUploadSize()) {
                if (in_array(strtolower($file->extension()), $allowed_images)) {
                    // delete the older one
                    if (Auth::user()->avatar != config('chatify.user_avatar.default')) {
                        $avatar = Auth::user()->avatar;
                        if (Chatify::storage()->exists($avatar)) {
                            Chatify::storage()->delete($avatar);
                        }
                    }
                    // upload
                    $avatar = Str::uuid() . "." . $file->extension();
                    $update = User::where('id', Auth::user()->id)->update(['avatar' => $avatar]);
                    $file->storeAs(config('chatify.user_avatar.folder'), $avatar, config('chatify.storage_disk_name'));
                    $success = $update ? 1 : 0;
                } else {
                    $msg = "File extension not allowed!";
                    $error = 1;
                }
            } else {
                $msg = "File size you are trying to upload is too large!";
                $error = 1;
            }
        }

        // send the response
        return Response::json([
            'status' => $success ? 1 : 0,
            'error' => $error ? 1 : 0,
            'message' => $error ? $msg : 0,
        ], 200);
    }

    /**
     * Set user's active status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setActiveStatus(Request $request)
    {
        $activeStatus = $request['status'] > 0 ? 1 : 0;
        $status = User::where('id', Auth::user()->id)->update(['active_status' => $activeStatus]);
        return Response::json([
            'status' => $status,
        ], 200);
    }
}