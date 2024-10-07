<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     * Menampilkan daftar dokter.
     */
    public function index()
    {
        // Mengambil daftar dokter dan mengurutkannya berdasarkan nama
        $doctor = Doctor::orderBy('name', 'asc')->get();

        return view('admin.doctor.index', [
            'doctor' => $doctor
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * Menampilkan form untuk membuat dokter baru.
     */
    public function create()
    {
        return view('admin.doctor.create');
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan dokter baru ke dalam basis data.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'spesialis' => 'required|string|max:255',
            'photo' => 'required|image|mimetypes:image/*|max:204800',
        ]);

        // Memulai transaksi database
        DB::beginTransaction();

        try {
            // Create the user first
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'doctor',
            ]);
            $user->assignRole('doctor');

            // Then create the doctor with the new user's ID
            $doctorData = $validated;
            $doctorData['user_id'] = $user->id;
            $doctorData['slug'] = Str::slug($request->name);

            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('doctor_photos', 'public');
                $doctorData['photo'] = $photoPath;
            }

            $newDoctor = Doctor::create($doctorData);

            DB::commit();

            return redirect()->route('admin.doctor.index')->with('success', 'Dokter telah berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'system_error' => ['System error: ' . $e->getMessage()],
            ]);
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dokter tertentu.
     */
    public function show(Doctor $doctor)
    {
        return view('admin.doctor.show', [
            'doctor' => $doctor
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * Menampilkan form untuk mengedit dokter tertentu.
     */
    public function edit(Doctor $doctor)
    {
        return view('admin.doctor.edit', [
            'doctor' => $doctor
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Mengupdate data dokter tertentu.
     */
    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $doctor->user_id,
            'password' => 'nullable|string|min:8|confirmed',
            'spesialis' => 'sometimes|string|max:255',
            'photo' => 'nullable|image|mimetypes:image/*|max:204800',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('doctor_photos', 'public');
                $validated['photo'] = $photoPath;
            }

            $validated['slug'] = Str::slug($request->name);

            $doctor->update($validated);

            $user = $doctor->user;
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.doctor.index')->with('success', 'Dokter telah berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            $error = ValidationException::withMessages([
                'system_error' => ['System error: ' . $e->getMessage()],
            ]);
            throw $error;
        }
    }


    /**
     * Remove the specified resource from storage.
     * Menghapus dokter tertentu dari basis data.
     */
    public function destroy(Doctor $doctor)
    {
        try {
            DB::beginTransaction();

            $doctor->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Dokter berhasil dihapus.');
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