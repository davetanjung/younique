@extends('components.layout')

@section('content')
    <div class="bg-gradient-to-b from-[#f5efe4] to-[#e8e0d3] min-h-screen">
        <!-- Hero Section with Background Image -->
        <div class="relative h-64 sm:h-80 lg:h-96 overflow-hidden">
            <div class="absolute inset-0 bg-[#6b4b4b] opacity-60"></div>
            <img src="{{ asset('images/fashion-banner.png') }}" alt="Fashion Banner" class="w-full h-full object-cover">
            <div class="absolute inset-0 flex flex-col justify-center px-10">
                <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 drop-shadow-lg">Meet Your Dream Stylist ✨</h1>
                <p class="text-lg md:text-xl text-white max-w-2xl drop-shadow-md">Every great look starts with a little inspiration. Find your perfect stylist match today.</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-12">
            <!-- Search and Filter Bar -->
            <div class="mb-10 bg-white rounded-lg shadow-md p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="relative flex-grow">
                    <input type="text" placeholder="Search stylists..." 
                           class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6b4b4b] focus:border-transparent">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-3.5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex flex-wrap gap-2">
                    <select class="bg-white border border-gray-200 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#6b4b4b]">
                        <option>All Styles</option>
                        <option>Minimalist</option>
                        <option>Bohemian</option>
                        <option>Streetwear</option>
                        <option>Luxury</option>
                    </select>
                    <select class="bg-white border border-gray-200 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#6b4b4b]">
                        <option>Sort By</option>
                        <option>Experience</option>
                        <option>Rating</option>
                        <option>Pricing</option>
                    </select>
                </div>
            </div>

            <!-- Stylist Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @foreach ($stylists as $stylist)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-xl group">
                        <div class="relative">
                            <img src="{{ asset('images/' . ($stylist->image ?? 'outfit1.jpeg')) }}" alt="{{ $stylist->name }}"
                                class="w-full h-56 object-cover transform group-hover:scale-105 transition duration-500">
                            
                            <!-- Badge -->
                            @if(isset($stylist->featured) && $stylist->featured)
                                <div class="absolute top-3 right-3 bg-[#f8d568] text-[#6b4b4b] text-xs font-bold px-3 py-1 rounded-full">
                                    FEATURED
                                </div>
                            @endif
                            
                            <!-- Rating -->
                            @if(isset($stylist->rating))
                                <div class="absolute bottom-3 left-3 bg-white bg-opacity-90 px-2 py-1 rounded-lg flex items-center">
                                    <div class="flex">
                                        @for($i = 0; $i < 5; $i++)
                                            @if($i < floor($stylist->rating))
                                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="ml-1 text-xs font-medium text-[#6b4b4b]">{{ $stylist->rating ?? '0' }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-5">
                            <div class="flex justify-between items-center mb-2">
                                <h2 class="text-xl font-bold text-[#6b4b4b]">{{ $stylist->name }}</h2>
                                <div class="text-sm font-medium text-[#9b8282]">
                                    {{ $stylist->experience ?? '5+ years' }}
                                </div>
                            </div>
                            
                            <!-- Specialties Tags -->
                            @if(isset($stylist->specialties))
                                <div class="flex flex-wrap gap-1 mb-3">
                                    @foreach($stylist->specialties as $specialty)
                                        <span class="bg-[#f5efe4] text-[#6b4b4b] text-xs px-2 py-1 rounded-full">{{ $specialty }}</span>
                                    @endforeach
                                </div>
                            @endif
                            
                            <p class="text-sm text-[#6b4b4b] mb-4 line-clamp-3">{{ $stylist->bio }}</p>
                            
                            <div class="flex justify-between items-center">
                                <a href="{{ route('stylist.index', $stylist->id) }}"
                                   class="text-[#6b4b4b] font-medium hover:text-[#4f3939] transition underline text-sm">
                                    View Profile
                                </a>
                                <a href="{{ $stylist->link }}"
                                   target="_blank"
                                   class="inline-block bg-[#6b4b4b] text-white rounded-md px-4 py-2 text-sm font-medium hover:bg-[#4f3939] transition shadow-sm">
                                    Book Session
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            

            <div class="mt-16 bg-white rounded-xl shadow-lg p-8 md:p-10">
                <div class="flex flex-col md:flex-row gap-8">
                    <div class="md:w-1/2">
                        <h3 class="text-2xl font-bold text-[#6b4b4b] mb-2">Get Styled Monthly</h3>
                        <p class="text-[#6b4b4b] mb-4">Sign up for our newsletter to receive monthly styling tips, trend reports, and exclusive offers from our top stylists.</p>
                        <div class="flex">
                            <input type="email" placeholder="Your email address" 
                                 class="flex-grow rounded-l-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6b4b4b] focus:border-transparent px-4 py-3">
                            <button class="bg-[#6b4b4b] text-white rounded-r-lg px-6 py-3 font-medium hover:bg-[#4f3939] transition">
                                Subscribe
                            </button>
                        </div>
                    </div>
                    <div class="md:w-1/2 flex justify-center md:justify-end items-center">
                        <img src="{{ asset('images/newsletter-image.png') }}" alt="Fashion Newsletter" class="h-32 md:h-40">
                    </div>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="mt-12 flex justify-center">
                <nav class="inline-flex rounded-md shadow">
                    <a href="#" class="relative inline-flex items-center px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-[#6b4b4b] hover:bg-gray-50">
                        Previous
                    </a>
                    <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-[#6b4b4b] text-sm font-medium text-white">
                        1
                    </a>
                    <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-[#6b4b4b] hover:bg-gray-50">
                        2
                    </a>
                    <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-[#6b4b4b] hover:bg-gray-50">
                        3
                    </a>
                    <a href="#" class="relative inline-flex items-center px-3 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-[#6b4b4b] hover:bg-gray-50">
                        Next
                    </a>
                </nav>
            </div>
        </div>
    </div>
@endsection