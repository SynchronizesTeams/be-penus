<?php

namespace App\Http\Controllers\API;

use app\Helpers\JsonReturn;
use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\Galeri;
use App\Models\Sarana;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    public function index()
    {
        $galeri = Galeri::where('status', '=', 1)->get();
        $berita = Berita::where('status', '=', 1)->get();
        $sarana = Sarana::where('status', '=', 1)->get();

        return response()->json([
            'success' => true,
            'message' => 'data berhasil ditampilkan',
            'data' => $data = [
                'galeri' => $galeri,
                'berita' => $berita,
                'sarana' => $sarana
            ],
        ], 200);
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
                $galeri_id = 'galeri' . '-' . uniqid();
                $imagePath = $image->store('galeri', 'public');
                $galeri = Galeri::create([
                    'image' => $imagePath,
                    'title' => $validateData['title'][$key],
                    'galeri_id' => $galeri_id
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'data' => $galeri,
        ], 200);
    }



    public function updateGaleri(Request $request, $galeri_id)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:png,jpeg,jpg,webp|max:2048',
            'title' => 'required|string|max:255'
        ]);
        $galeri = null;
        DB::transaction(function () use ($request, $galeri_id, &$galeri) {
            $galeri = Galeri::where('galeri_id', '=', $galeri_id)->where('status', '=', 1)->first();

            if (!$galeri) {
                return JsonReturn::error('Data tidak ditemukan');
            }

            if ($galeri->status === 0) {
                return JsonReturn::error('Galeri dengan status 0 tidak dapat diedit');
            }
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
            'success' => true,
            'message' => "data berhasil di update",
            'data' => $galeri
        ], 200);
    }

    public function deleteGaleri(Request $request, $galeri_id)
    {
        DB::transaction(function () use ($galeri_id){
            $galeri = Galeri::where('galeri_id', '=', $galeri_id)->firstOrFail();
            if (!$galeri) {
                $response = JsonReturn::error("data tidak ditemukan");
            }
            $galeri->status = 0;
            $galeri->save();
        });

        return response()->json([
            'success' => true,
            'message' => "data berhasil dihapus",
        ]);
    }

    public function showGaleri(Request $request)
    {
        $galeri = Galeri::get()->where('status', '=', 1);

        return response()->json([
            'success' => true,
            'data' => $galeri
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
                $sarana_id = 'sarana' . '-' . uniqid();
                $imagePath = $image->store('sarana', 'public');
                $sarana = Sarana::create([
                    'image' => $imagePath,
                    'title' => $validateData['title'][$key],
                    'sarana_id' => $sarana_id
                ]);
            }
        });

        return response()->json([
            'success' => true, 
            'message' => 'Gambar berhasil ditambahkan',
            'data' => $sarana
        ], 201);
    }

    public function updateSarana(Request $request, $sarana_id)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:png,jpeg,jpg,webp|max:2048',
            'title' => 'required|string|max:255'
        ]);
        $sarana = null;
        DB::transaction(function () use ($request, $sarana_id, &$sarana) {
            $sarana = Sarana::where('sarana_id', '=', $sarana_id)->firstOrFail();

            if ($sarana->status == 0) {
                return response()->json([
                    'message' => 'Data tidak ditemukan'
                ]);
            }
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
            'success' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $sarana
        ], 200);
    }

    public function deleteSarana(Request $request, $sarana_id)
    {
        DB::transaction(function () use ($sarana_id){
            $sarana = Sarana::where('sarana_id', '=', $sarana_id)->firstOrFail();

            $sarana->status = 0;
            $sarana->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ], 201);
    }

    public function showSarana() 
    {
        $sarana = Sarana::where('status', '=', 1)->get();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditampilkan',
            'data' => $sarana   
        ]);
    }

    //end function sarana

    //start function berita
    public function createBerita(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'images' => 'required|image|mimes:jpg,png,jpeg,webp',
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
            'description' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'required|string|max:255',
        ]);

        $berita = null;

        DB::transaction(function () use ($request, $user, &$berita) {
            $imagePath = $request->file('images')->store('berita', 'public');

            $berita_id = 'berita' . '-' . uniqid();
            $berita = Berita::create([
                'berita_id' => $berita_id,
                'author' => $user->name,
                'images' => $imagePath,
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'description' => $request->description,
                'tags' => $request->tags,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil ditambahkan',
            'data' => $berita
        ], 200);
    }

    public function updateBerita(Request $request, $berita_id)
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
         DB::transaction(function () use ($request, $berita_id, &$berita) {
            $berita = Berita::where('berita_id', '=', $berita_id)->firstOrFail();
            $imagePath = $request->file('images')->store('berita', 'public');
            $berita->images = $imagePath;
            $berita->update($request->only('title', 'subtitle', 'description', 'tags'));
            $berita->save();

         });

         return response()->json([
            'success' => true,
            'message' => 'Data berhasil di update',
            'data' => $berita
         ], 201);
    }

    public function deleteBerita(Request $request, $berita_id) {
        DB::transaction(function () use ($berita_id){
            $berita = Berita::where('berita_id', '=', $berita_id)->firstOrFail();

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
        ], 201);
    }

    public function showBerita(Request $request, $berita_id)
    {
        $berita  = Berita::where('berita_id', '=', $berita_id)->firstOrFail();

        if (!$berita) {
            return response()->json([
                'message' => 'Berita tidak ditemukan'
            ], 400);
        } else {
            return response()->json([
                'message' => 'Berita ditemukan',
                'data' => $berita
            ], 200);
        }
    }

    public function showBeritaAll()
    {
        $berita  = Berita::get();

        if (!$berita) {
            return response()->json([
                'message' => 'Berita tidak ditemukan'
            ], 400);
        } else {
            return response()->json([
                'message' => 'Berita ditemukan',
                'data' => $berita
            ], 200);
        }
    }


    public function getTags(Request $request)
    {
        $tag = $request->query('tag');

        if (!$tag) {
            return response()->json(['message' => 'Tag harus diisi']);
        }

        $berita = Berita::whereRaw('JSON_CONTAINS(tags, ?)', [json_encode($tag)])
        ->get();

        if (!$berita) {
            return response()->json([
                'message' => 'Berita tidak ditemukan'
            ]);
        }

        return response()->json([
            'message' => 'Berita ditemukan',
            'data' => $berita,
        ], 200);
    }
}
