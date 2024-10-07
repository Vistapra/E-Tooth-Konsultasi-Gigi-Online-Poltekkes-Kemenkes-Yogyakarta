<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * Menampilkan daftar kategori.
     */
    public function index()
    {
        $categories = Category::all();
        return view('admin.categories.index', [
            'categories' => $categories
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * Menampilkan form untuk membuat kategori baru.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan kategori baru ke dalam basis data.
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|image|mimes:png,svg,jpg|max:2048'
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'icon.required' => 'Icon wajib diunggah.',
            'icon.image' => 'Icon harus berupa gambar.',
            'icon.mimes' => 'Icon harus berformat png, svg, atau jpg.',
            'icon.max' => 'Ukuran icon tidak boleh lebih dari 2MB.',
        ]);

        // Memulai transaksi database
        DB::beginTransaction();

        try {
            // Cek apakah file icon ada, jika ada simpan di storage
            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('category_icons', 'public');
                $validated['icon'] = $iconPath;
            }

            // Membuat slug dari nama kategori
            $validated['slug'] = Str::slug($request->name);

            // Membuat kategori baru dengan data yang telah divalidasi
            $newCategory = Category::create($validated);

            // Komit transaksi jika berhasil
            DB::commit();

            // Redirect ke halaman index kategori admin dengan pesan sukses
            return redirect()->route('admin.categories.index')->with('success', 'Kategori telah berhasil dibuat.');
        } catch (\Exception $e) {
            // Rollback transaksi jika ada kesalahan
            DB::rollBack();
            // Lempar kesalahan validasi dengan pesan kesalahan sistem
            $error = ValidationException::withMessages([
                'system_error' => ['System error: ' . $e->getMessage()],
            ]);
            throw $error;
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail kategori tertentu.
     */
    public function show(Category $category)
    {
        return view('admin.categories.show', [
            'category' => $category
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * Menampilkan form untuk mengedit kategori tertentu.
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', [
            'category' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Mengupdate data kategori tertentu.
     */
    public function update(Request $request, Category $category)
    {
        // Validasi input dari form
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'icon' => 'sometimes|image|mimes:png,svg,jpg|max:2048'
        ]);

        // Memulai transaksi database
        DB::beginTransaction();

        try {
            // Cek apakah file icon ada, jika ada simpan di storage
            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('category_icons', 'public');
                $validated['icon'] = $iconPath;
            }

            // Membuat slug dari nama kategori
            $validated['slug'] = Str::slug($request->name);

            $category->update($validated);

            // Komit transaksi jika berhasil
            DB::commit();

            // Redirect ke halaman index kategori admin dengan pesan sukses
            return redirect()->route('admin.categories.index')->with('success', 'Kategori telah berhasil diperbarui.');
        } catch (\Exception $e) {
            // Rollback transaksi jika ada kesalahan
            DB::rollBack();
            // Lempar kesalahan validasi dengan pesan kesalahan sistem
            $error = ValidationException::withMessages([
                'system_error' => ['System error: ' . $e->getMessage()],
            ]);
            throw $error;
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus kategori tertentu dari basis data.
     */
    public function destroy(Category $category)
    {
        try {
            DB::beginTransaction();

            $category->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            // Rollback transaksi jika ada kesalahan
            DB::rollBack();
            // Lempar kesalahan validasi dengan pesan kesalahan sistem
            $error = ValidationException::withMessages([
                'system_error' => ['System error: ' . $e->getMessage()],
            ]);
            throw $error;
        }
    }
}
