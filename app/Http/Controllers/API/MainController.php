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
        $validateData = $request->validate([
            'image' => 'required|array',
            'image.*' => 'required|image|mimes:png,jpeg,jpg,webp',
            'title' => 'required|array',
            'title.*' => 'required|string'
        ]);

        $sarana = null;

        DB::transaction(function () use ($validateData, &$sarana) {
            foreach($validateData['image'] as $key => $image){
                $imagePath = $image->store('sarana', 'public');
                $sarana = Sarana::create([
                    'image' => $imagePath,
                    'title' => $validateData['title'][$key],
                ]);
            }
        });

        return response()->json([
            'message' => 'Gambar berhasil ditambahkan'
        ], 201);
    }

    public function updateSarana(Request $request, $id)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:png,jpeg,jpg,webp|max:2048',
            'title' => 'required|string|max:255'
        ]);

        DB::transaction(function () use ($request, $id) {
            $sarana = Sarana::where('id', '=', $id)->firstOrFail();

            if ($sarana->status == 0) {
                return response()->json([
                    'message' => 'Data tidak ditemukan'
                ]);
            }
            // Periksa apakah ada file gambar baru di request
            if ($request->hasFile('image')) {
                if ($sarana->image) {
                    Storage::disk('public')->delete($sarana->image);
                }

                $imagePath = $request->file('image')->store('sarana', 'public');

             $sarana->image = $imagePath;
            }

            $sarana->title = $request->title;
            $sarana->save();
        });

        return response()->json([
            'message' => 'Data berhasil diupdate'
        ], 200);
    }

    public function deleteSarana(Request $request, $id)
    {
        DB::transaction(function () use ($id){
            $sarana = Sarana::where('id', '=', $id)->firstOrFail();

            $sarana->status = 0;
            $sarana->save();
        });

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);
    }

    //end function sarana

    //start function berita
    public function createBerita(Request $request)
    {
        $request->validate([
            'images' => 'required|image|mimes:jpg,png,jpeg,webp',
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
            'description' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'required|string|max:255',
        ]);

        $berita = null;

        DB::transaction(function () use ($request, &$berita) {
            $imagePath = $request->file('images')->store('berita', 'public');

            // Create berita with tags stored as JSON
            $berita = Berita::create([
                'images' => $imagePath,
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'description' => $request->description,
                'tags' => $request->tags,
            ]);
        });

        return response()->json([
            'message' => 'Berita berhasil ditambahkan',
        ], 201);
    }

    public function updateBerita(Request $request, $id)
    {
         $request->validate([
            'images' => 'required|image|mimes:jpg,png,jpeg,webp',
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
            'description' => 'required|string',
            'tags' => 'require d|array',
            'tags.*' => 'required|string|max:255',
        ]);

         DB::transaction(function () use ($request, $id) {
            $berita = Berita::where('id', '=', $id)->firstOrFail();
            $imagePath = $request->file('images')->store('berita', 'public');
            $berita->images = $imagePath;
            $berita->update($request->only('title', 'subtitle', 'description', 'tags'));
            $berita->save();

         });

         return response()->json([
            'message' => 'Data berhasil di update'
         ]);
    }

    public function deleteBerita(Request $request, $id) {
        DB::transaction(function () use ($id){
            $berita = Berita::where('id', '=', $id)->firstOrFail();

            if (!$berita) {
                return response()->json([
                    'message' => 'Berita tidak ditemukan'
                ]);
            }

            $berita->status = 0;
            $berita->save();
        });

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);
    }


    public function getTags(Request $request, $id)
    {
        
    }
}
