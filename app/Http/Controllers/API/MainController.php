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
    public function user(Request $request) 
    {   
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'name' => $user->name
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }


    public function index()
    {
        try {
            // data galeri
            $galeri = DB::table('galeri')
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->select('galeri_id', 'title', 'image')
            ->get();

            // data berita
            $berita = DB::table('berita')
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->select('berita_id', 'title', 'images', 'author', 'subtitle', 'description', 'tags')
            ->get();

            // data sarana
            $sarana = DB::table('sarana')
            ->where('status', 1)
            ->select('sarana_id', 'title', 'image')
            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil ditampilkan',
                'data' => [
                    'galeri' => $galeri,
                    'berita' => $berita,
                    'sarana' => $sarana
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi'
            ], 500);
        }
    }



    public function createGaleri(Request $request)
    {
        try {
            // Validasi input
            $validateData = $request->validate([
                'image' => 'required|array',
                'image.*' => 'required|image|mimes:png,jpeg,jpg,webp|max:2048',
                'title' => 'required|array',
                'title.*' => 'required|string|max:255'
            ]);

            $galeriList = [];

            DB::transaction(function () use ($validateData, &$galeriList) {
                foreach ($validateData['image'] as $key => $image) {
                    $galeri_id = 'galeri-' . uniqid();
                    $imagePath = $image->store('galeri', 'public');

                    $galeri = Galeri::create([
                        'image' => $imagePath,
                        'title' => $validateData['title'][$key],
                        'galeri_id' => $galeri_id
                    ]);

                    $galeriList[] = $galeri;
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Galeri berhasil dibuat',
                'data' => $galeriList,
            ], 201); 

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }




    public function updateGaleri(Request $request, $galeri_id)
    {
        try {
            // Validasi input
            $request->validate([
                'image' => 'nullable|image|mimes:png,jpeg,jpg,webp|max:2048',
                'title' => 'required|string|max:255'
            ]);

            $galeri = Galeri::where('galeri_id', $galeri_id)->where('status', 1)->first();

            if (!$galeri) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            if ($galeri->status === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Galeri dengan status 0 tidak dapat diedit'
                ], 403);
            }

            DB::transaction(function () use ($request, $galeri) {
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
                'message' => "Data berhasil diperbarui",
                'data' => $galeri
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi'
            ], 500);
        }
    }


    public function deleteGaleri(Request $request, $galeri_id)
    {
        try {
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }

    public function showGaleri(Request $request)
    {
        try {
            $galeri = Galeri::where('status', 1)->get();

            $modifiedGaleri = $galeri->values()->map(function ($item, $index) {
                $item->grid = $index % 2;
                return $item;
            });
        
            return response()->json([
                'success' => true,
                'data' => $modifiedGaleri
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi'
            ], 500);
        }
    }

    public function showGaleriById(Request $request, $galeri_id)
    {
        try {
            $galeri  = Galeri::where('galeri_id', '=', $galeri_id)->where('status', '=', 1)->firstOrFail();

            if (!$galeri) {
                return response()->json([
                    'message' => 'sarana tidak ditemukan'
                ], 400);
            } else {
                return response()->json([
                    'message' => 'saarnaa ditemukan',
                    'data' => $galeri
                ], 200);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi'
            ], 500);
        }
    }
    //end function galeri

    //start function sarana
    public function createSarana(Request $request)
    {
        try {
            $validateData = $request->validate([
                'image' => 'required|array',
                'image.*' => 'required|image|mimes:png,jpeg,jpg,webp',
                'title' => 'required|array',
                'title.*' => 'required|string'
            ]);

            $sarana = null;

            DB::transaction(function () use ($validateData, &$sarana) {
                foreach ($validateData['image'] as $key => $image) {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }

    public function updateSarana(Request $request, $sarana_id)
    {
        try {
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }

    public function deleteSarana(Request $request, $sarana_id)
    {
        try {
            DB::transaction(function () use ($sarana_id){
                $sarana = Sarana::where('sarana_id', '=', $sarana_id)->firstOrFail();

                $sarana->status = 0;
                $sarana->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }

    public function showSarana() 
    {
        try {
            $sarana = Sarana::where('status', '=', 1)->get();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil ditampilkan',
                'data' => $sarana   
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }

    public function showSaranaById(Request $request, $sarana_id)
    {
        try {
            $sarana  = Sarana::where('sarana_id', '=', $sarana_id)->where('status', '=', 1)->firstOrFail();

            if (!$sarana) {
                return response()->json([
                    'message' => 'sarana tidak ditemukan'
                ], 400);
            } else {
                return response()->json([
                    'message' => 'saarnaa ditemukan',
                    'data' => $sarana
                ], 200);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }
    //end function sarana

    //start function berita
    public function createBerita(Request $request)
    {
        try {
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
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }

    public function updateBerita(Request $request, $berita_id)
    {
        $request->validate([
            'images' => 'nullable|image|mimes:jpg,png,jpeg,webp',
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
            'description' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'required|string|max:255',
        ]);

        $berita = null;

        try {
            DB::transaction(function () use ($request, $berita_id, &$berita) {
                $berita = Berita::where('berita_id', '=', $berita_id)->firstOrFail();

                if ($request->hasFile('images')) {
                    if ($berita->images) {
                        Storage::disk('public')->delete($berita->images);
                    }

                    $imagePath = $request->file('images')->store('berita', 'public');
                    $berita->images = $imagePath;
                }

                $berita->update($request->only('title', 'subtitle', 'description', 'tags'));
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'data' => $berita,
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }


    public function deleteBerita(Request $request, $berita_id) {
        try {
            DB::transaction(function () use ($berita_id){
                $berita = Berita::where('berita_id', '=', $berita_id)->firstOrFail();

                if (!$berita) {
                    return response()->json([
                        'message' => 'Berita tidak ditemukan'
                    ], 404);
                }

                $berita->status = 0;
                $berita->save();
            });

            return response()->json([
                'message' => 'Data berhasil dihapus'
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }

    public function showBerita(Request $request, $berita_id)
    {
        try {
            $berita  = Berita::where('berita_id', '=', $berita_id)->where('status', '=', 1)->firstOrFail();
            $rekomendasi_berita = Berita::inRandomOrder()->select('berita_id', 'title', 'images')->take(4)->get();

            return response()->json([
                'message' => 'Berita ditemukan',
                'data' => $berita,
                'recommended' => $rekomendasi_berita
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Berita tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }

    public function showBeritaAll()
    {
        try {
            $berita  = Berita::where('status', '=', 1)->get();

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
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }


    public function getTags(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }

    public function recommendedBerita()
    {
        try {
            $berita = Berita::inRandomOrder()->select('berita_id', 'title')->take(4)->get();

            return response()->json([
                'success' => true,
                'data' => $berita
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi',
            ], 500);
        }
    }
}
