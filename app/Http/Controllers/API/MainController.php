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
        $galeri = Galeri::where('status', '=', 1)->get();
        $berita = Berita::where('status', '=', 1)->get();
        $sarana = Sarana::where('status', '=', 1)->get();

        return response()->json([
            $data = [
                'galeri' => $galeri,
                'berita' => $berita,
                'sarana' => $sarana
            ],
            200
        ]);
    }

    // start function galeri
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
            'image' => 'nullable|image|mimes:png,jpeg,jpg,webp|max:2048',
            'title' => 'required|string|max:255'
        ]);

        DB::transaction(function () use ($request, $id) {
            $galeri = Galeri::where('id', '=', $id)->firstOrFail();

            if ($galeri->status == 0) {
                return response()->json([
                    'message' => 'Data tidak ditemukan'
                ]);
            }
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

    public function deleteGaleri(Request $request, $id)
    {
        DB::transaction(function () use ($id){
            $galeri = Galeri::where('id', '=', $id)->firstOrFail();
            
            $galeri->status = 0;
            $galeri->save();
        });

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);
    }
    //end function galeri

    //start function sarana
    public function createSarana(Request $request)
    {
        
    }
}
