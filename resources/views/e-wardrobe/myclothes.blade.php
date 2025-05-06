@extends('components.layout')

{{-- 
@section('styles')
    <link rel="stylesheet" href="{{ asset('/css/homepage.css') }}">
@endsection --}}
@vite('resources/css/app.css')
@section('content')
<div class="bg-[#f5efe4] min-h-screen p-10">
    <h1 class="text-3xl font-bold text-[#6b4b4b] mb-8">My Clothes</h1>

    <div class="grid grid-cols-4 gap-6">
        @php
            $images = [                
                'images/top1.jpeg', // shirt
                'images/top2.jpeg', // blouse
                'images/top3.jpeg', // t-shirt            
                'images/bottom1.jpeg', 
                'images/bottom2.jpeg', // pants
                'images/bottom3.jpeg', // skirt             
                'images/outfit1.jpeg', // one set
                'images/outfit2.jpeg',
                'images/outfit3.jpeg', // jacket outfit            
            ];
        @endphp
        {{-- @foreach($images as $cloth)
            <div class="w-full aspect-[3/4] rounded-lg overflow-hidden shadow">
                <img src="{{ asset('images/'.$cloth['image']) }}" 
                     alt="{{ $cloth['name'] }}" 
                     class="w-full h-full object-cover">
            </div>
        @endforeach --}}
        {{-- <div class="flex gap-6 w-full justify-center"> --}}
            @foreach ($images as $image)
                <div class="w-full aspect-[3/4] bg-white rounded-lg shadow-md overflow-hidden">
                    <img src="{{ $image }}" alt="Clothing Image" class="w-full h-full object-cover">
                </div>
            @endforeach
        {{-- </div> --}}
    </div>
</div>
@endsection