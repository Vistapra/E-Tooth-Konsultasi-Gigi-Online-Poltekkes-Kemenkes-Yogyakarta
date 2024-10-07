<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Product;
use App\Models\Category;
use App\Models\ChMessage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FrontController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->select('id', 'name', 'slug', 'about', 'photo', 'video', 'video_link', 'category_id', 'created_at')
            ->orderBy('created_at', 'DESC')
            ->take(100)
            ->get();

        $categories = Category::all();
        $doctor = Doctor::all();

        return view('front.index', [
            'products' => $products,
            'categories' => $categories,
            'doctor' => $doctor,
        ]);
    }


    public function details(Product $product)
    {
        $product->load('category');

        return view('front.details', [
            'product' => $product,
        ]);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $products = Product::where('name', 'LIKE', '%' . $keyword . '%')
            ->orWhere('slug', 'LIKE', '%' . $keyword . '%')
            ->orWhere('about', 'LIKE', '%' . $keyword . '%')
            ->get();
        $doctors = Doctor::where('name', 'LIKE', '%' . $keyword . '%')->get();

        return view('front.search', [
            'products' => $products,
            'doctors' => $doctors,
            'keyword' => $keyword,
        ]);
    }

    public function category(Category $category)
    {
        $products = Product::where('category_id', $category->id)->with('category')->get();
        return view('front.category', [
            'products' => $products,
            'category' => $category,
        ]);
    }

    public function konsultasi()
    {
        $doctor = DB::table('doctor')
            ->leftJoin('users', 'users.id', '=', 'doctor.user_id')
            ->orderBy('users.name', 'asc')
            ->get();

        return view('front.konsultasi', [
            'doctor' => $doctor,
        ]);
    }


    public function riwayat(Request $request)
    {
        $userId = Auth::id();
        $keyword = $request->input('keyword');

        $query = ChMessage::where(function ($q) use ($userId) {
            $q->where('from_id', $userId)->orWhere('to_id', $userId);
        })
            ->with(['from', 'to'])
            ->select('from_id', 'to_id')
            ->groupBy(DB::raw('LEAST(from_id, to_id)'), DB::raw('GREATEST(from_id, to_id)'))
            ->orderBy('created_at', 'desc');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('from', function ($subQ) use ($keyword) {
                    $subQ->where('name', 'LIKE', "%{$keyword}%");
                })->orWhereHas('to', function ($subQ) use ($keyword) {
                    $subQ->where('name', 'LIKE', "%{$keyword}%");
                });
            });
        }

        $conversations = $query->get();

        $users = $conversations->map(function ($conversation) use ($userId) {
            $otherUserId = $conversation->from_id == $userId ? $conversation->to_id : $conversation->from_id;
            $user = User::find($otherUserId);
            $latestMessage = ChMessage::where(function ($q) use ($userId, $otherUserId) {
                $q->where(function ($subQ) use ($userId, $otherUserId) {
                    $subQ->where('from_id', $userId)->where('to_id', $otherUserId);
                })->orWhere(function ($subQ) use ($userId, $otherUserId) {
                    $subQ->where('from_id', $otherUserId)->where('to_id', $userId);
                });
            })
                ->latest()
                ->first();

            $user->latestMessage = $latestMessage;
            return $user;
        })->unique('id');

        return view('front.riwayat', [
            'users' => $users,
        ]);
    }
}
