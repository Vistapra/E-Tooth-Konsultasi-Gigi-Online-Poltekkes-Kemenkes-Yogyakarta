<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search functionality
        if ($request->has('query')) {
            $searchTerm = $request->query('query');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('about', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category_id', $request->category);
        }

        // Sorting
        $sort = $request->query('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(15)->appends($request->query());
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }


    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'about' => 'required|string',
            'category_id' => 'required|integer|exists:categories,id',
            'photo' => 'nullable|image|mimetypes:image/*|max:2048',
            'video' => 'nullable|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime|max:20480',
            'video_link' => 'nullable|url'
        ]);

        DB::beginTransaction();

        try {
            $validated['slug'] = Str::slug($request->name);

            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('product_photos', 'public');
                $validated['photo'] = $photoPath;
            }

            if ($request->hasFile('video')) {
                $videoPath = $request->file('video')->store('product_videos', 'public');
                $validated['video'] = $videoPath;
            }

            if ($request->has('video_link')) {
                $validated['video_link'] = $request->video_link;
            }

            $newProduct = Product::create($validated);

            DB::commit();

            return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            $error = ValidationException::withMessages([
                'system_error' => ['System error: ' . $e->getMessage()],
            ]);
            throw $error;
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'about' => 'sometimes|string',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'photo' => 'sometimes|image|mimetypes:image/*|max:2048',
            'video' => 'sometimes|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime|max:20480',
            'video_link' => 'nullable|url'
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($product->photo) {
                    Storage::disk('public')->delete($product->photo);
                }

                // Store new photo
                $photoPath = $request->file('photo')->store('product_photos', 'public');
                $validated['photo'] = $photoPath;
            }

            if ($request->hasFile('video')) {
                // Delete old video
                if ($product->video) {
                    Storage::disk('public')->delete($product->video);
                }

                // Store new video
                $videoPath = $request->file('video')->store('product_videos', 'public');
                $validated['video'] = $videoPath;
            }

            if ($request->has('video_link')) {
                $validated['video_link'] = $request->video_link;
            }

            $validated['slug'] = Str::slug($request->name);

            $product->update($validated);

            DB::commit();

            return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            $error = ValidationException::withMessages([
                'system_error' => ['System error: ' . $e->getMessage()],
            ]);
            throw $error;
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();

        try {
            if ($product->photo) {
                Storage::disk('public')->delete($product->photo);
            }

            if ($product->video) {
                Storage::disk('public')->delete($product->video);
            }

            $product->delete();

            DB::commit();

            return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            $error = ValidationException::withMessages([
                'system_error' => ['System error: ' . $e->getMessage()],
            ]);
            throw $error;
        }
    }
}
