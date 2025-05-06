<?php

namespace App\Http\Controllers;

use App\Models\Cloth;
use Auth;
use Illuminate\Http\Request;
use Storage;
use Str;

class ClothController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::check() ? Auth::id() : null;
        $guestId = session()->get('guest_id');
        
        if (!$userId && !$guestId) {
            $guestId = Str::uuid()->toString();
            session()->put('guest_id', $guestId);
        }

        $clothes = Cloth::when($userId, function($query) use ($userId) {
                        return $query->where('user_id', $userId);
                    })
                    ->when(!$userId, function($query) use ($guestId) {
                        return $query->where('guest_id', $guestId);
                    })
                    ->get();

        return view('e-wardrobe.myclothes', compact('clothes', 'guestId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $userId = Auth::check() ? Auth::id() : null;
        // $guestId = session()->get('guest_id');
        
        // if (!$userId && !$guestId) {
        //     $guestId = Str::uuid()->toString();
        //     session()->put('guest_id', $guestId);
        // }

        // $clothes = Cloth::when($userId, function($query) use ($userId) {
        //                 return $query->where('user_id', $userId);
        //             })
        //             ->when(!$userId, function($query) use ($guestId) {
        //                 return $query->where('guest_id', $guestId);
        //             })
        //             ->get();

        // return view('e-wardrobe.myclothes', compact('clothes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:casual,formal,sportswear,business,nightwear',
            'color' => 'required|string|max:50',
            'season' => 'required|string|max:50',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|string|in:top,bottom',
        ]);
    
        $userId = Auth::check() ? Auth::id() : null;
        $guestId = session()->get('guest_id');
        $type = $request->input('type');
        
        if (!$userId && !$guestId) {
            $guestId = Str::uuid()->toString();
            session()->put('guest_id', $guestId);
        }
    
        if ($request->hasFile('image')) {
            $folder = in_array($type, ['top', 'bottom']) ? $type : 'others';
            $path = $request->file('image')->store("clothes/{$folder}", 'public');
            $imageUrl = Storage::url($path);
        } else {
            $imageUrl = null;
        }
    
        Cloth::create([
            'user_id' => $userId,
            'guest_id' => $userId ? null : $guestId,
            'name' => $request->name,
            'category' => $request->category,
            'color' => $request->color,
            'season' => $request->season,
            'image_url' => $imageUrl,
            'type' => $request->type,
        ]);
    
        return redirect()->route('cloth.index')->with('success', 'Clothing item added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
