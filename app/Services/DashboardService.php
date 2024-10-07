<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Doctor;
use App\Models\ChMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Facades\ChatifyMessenger as Chatify;


class DashboardService
{
    public function getOwnerDashboardData(): array
    {
        return [
            'categories' => $this->getCategoriesCount(),
            'products' => $this->getProductsCount(),
            'doctors' => $this->getDoctorsCount(),
            'totalMessages' => $this->getTotalMessagesCount(),
            'totalConsultations' => $this->getTotalConsultations(),
            'aiResponses' => $this->getAIResponsesCount(),
            'unreadMessages' => $this->getUnreadMessagesCount(),
            'recentMessages' => $this->getRecentMessages(),
        ];
    }

    private function getCategoriesCount(): int
    {
        return Category::count();
    }

    private function getProductsCount(): int
    {
        return Product::count();
    }

    private function getDoctorsCount(): int
    {
        return Doctor::count();
    }

    private function getTotalMessagesCount(): int
    {
        return ChMessage::count();
    }

    public function getTotalConsultations(): int
    {
        return DB::table('ch_messages')
            ->select(DB::raw('LEAST(from_id, to_id) as user1, GREATEST(from_id, to_id) as user2'))
            ->groupBy('user1', 'user2')
            ->get()
            ->count();
    }

    private function getAIResponsesCount(): int
    {
        return ChMessage::where('is_ai_response', true)->count();
    }

    private function getUnreadMessagesCount(): int
    {
        return ChMessage::where('seen', false)->count();
    }

    public function getRecentMessages()
    {
        $messages = ChMessage::select('ch_messages.*')
            ->join(DB::raw('(SELECT 
                LEAST(from_id, to_id) as user1,
                GREATEST(from_id, to_id) as user2,
                MAX(created_at) as max_created_at
                FROM ch_messages
                GROUP BY user1, user2) as latest_messages'), function ($join) {
                $join->on(function ($join) {
                    $join->on(DB::raw('LEAST(ch_messages.from_id, ch_messages.to_id)'), '=', 'latest_messages.user1')
                        ->on(DB::raw('GREATEST(ch_messages.from_id, ch_messages.to_id)'), '=', 'latest_messages.user2');
                })
                    ->on('ch_messages.created_at', '=', 'latest_messages.max_created_at');
            })
            ->with(['from', 'to'])
            ->orderBy('ch_messages.created_at', 'desc')
            ->paginate(10);

        // Process avatars
        $messages->getCollection()->transform(function ($message) {
            $message->from = $message->from ? Chatify::getUserWithAvatar($message->from) : null;
            $message->to = $message->to ? Chatify::getUserWithAvatar($message->to) : null;
            return $message;
        });

        return $messages;
    }
}
