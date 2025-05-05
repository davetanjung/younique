@extends('components.layout')

{{-- 
@section('styles')
    <link rel="stylesheet" href="{{ asset('/css/homepage.css') }}">
@endsection --}}
@vite('resources/css/app.css')
@section('content')
<div class="p-10 space-y-12">
    @foreach (['Top Wear', 'Bottom Wear', 'Outfits'] as $category)
        <div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-3xl font-bold">Favorite {{ $category }}</h2>
                <button class="me-10 bg-[#5D3A00] text-white px-5 py-2 rounded-md font-medium hover:bg-[#4a2f00]">
                    See More
                </button>
            </div>
            <div class="flex items-center space-x-4">
                
                <button class="text-4xl text-[#5D3A00] font-bold">&lt;</button>
                <div class="flex gap-8 w-full justify-center">
                    
                        {{-- <div class="w-50 h-60 bg-gray-300 rounded-lg"></div> --}}
                        @php
                            $images = [
                                'Top Wear' => [
                                    'images/top1.jpeg', // shirt
                                    'images/top2.jpeg', // blouse
                                    'images/top3.jpeg', // t-shirt
                                ],
                                'Bottom Wear' => [
                                    'images/bottom1.jpeg', 
                                    'images/bottom2.jpeg', // pants
                                    'images/bottom3.jpeg', // skirt
                                ],
                                'Outfits' => [
                                    'images/outfit1.jpeg', // one set
                                    'images/outfit2.jpeg',
                                    'images/outfit3.jpeg', // jacket outfit
                                ],
                            ];
                        @endphp

                        <div class="flex gap-6 w-full justify-center">
                            @foreach ($images[$category] as $image)
                                <div class="w-40 h-52 bg-white rounded-lg shadow-md overflow-hidden">
                                    <img src="{{ $image }}" alt="Clothing Image" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>

                    
                </div>
                <button class="text-4xl text-[#5D3A00] font-bold">&gt;</button>
            </div>
        </div>
    @endforeach

    <!-- Bottom Buttons -->
    <div class="flex justify-center space-x-6 mt-10">
        <button class="bg-[#5D3A00] text-white px-6 py-3 rounded-md font-semibold hover:bg-[#4a2f00]">
            See My Clothes
        </button>
        <button class="bg-[#5D3A00] text-white px-6 py-3 rounded-md font-semibold hover:bg-[#4a2f00]">
            Add New Clothes
        </button>
    </div>
</div>
@endsection