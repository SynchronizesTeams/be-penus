<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\Galeri;
use App\Models\Sarana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MainController extends Controller
{
    public function index()
    {
        $galeri = Galeri::get();
        $berita = Berita::get();
        $sarana = Sarana::get();

        return response()->json([
            $data = [
                'galeri' => $galeri,
                'berita' => $berita,
                'sarana' => $sarana
            ],
            200
        ]);
    }

    public function createGaleri(Request $request)
    {
        $validateData = $request->validate([
            'image' => 'required|array',
            'image.*' => 'required|image|mimes:png,jpeg,jpg,webp',
            'title' => 'required|array',
            'title.*' => 'required|string'
        ]);

        $galeri = null;

        DB::transaction(function () use ($validateData, &$galeri) {
            foreach($validateData['image'] as $key => $image){
                $imagePath = $image->store('galeri', 'public');
                $galeri = Galeri::create([
                    'image' => $imagePath,
                    'title' => $validateData['title'][$key],
                ]);
            }
        });

        return response()->json([
            'message' => 'Gambar berhasil ditambahkan'
        ], 201);
    }

    public function updateGaleri(Request $request, $id)
{
    $request->validate([
        'image' => 'nullable|image|mimes:png,jpeg,jpg,webp|max:2048', // Gambar bersifat opsional
        'title' => 'required|string|max:255'
    ]);

    DB::transaction(function () use ($request, $id) {
        $galeri = Galeri::where('id', '=', $id)->firstOrFail();

        // Periksa apakah ada file gambar baru di request
        if ($request->hasFile('image')) {
            if ($galeri->image) {
                Storage::disk('public')->delete($galeri->image);
            }

            $imagePath = $request->file('image')->store('galeri', 'public');

            $galeri->image = $imagePath;
        }

        $galeri->title = $request->title;
        $galeri->save();
    });

    return response()->json([
        'message' => 'Data berhasil diupdate'
    ], 200);
}


}
