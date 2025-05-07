@extends('components.layout')

{{-- 
@section('styles')
    <link rel="stylesheet" href="{{ asset('/css/homepage.css') }}">
@endsection --}}

@section('content')
    <div class="bg-[#f5efe4] min-h-screen p-10 w-full">
        <h1 class="text-3xl font-bold text-[#6b4b4b] mb-8">My Wardrobe</h1>

        <div class="grid grid-cols-4 gap-6">
            <div id="openModal">
                <button id="hoverTarget" type="submit"
                    class="group relative w-full aspect-[3/4] bg-white rounded-lg shadow-md overflow-hidden flex justify-center items-center hover:bg-gray-100 transition-colors duration-300">
                    <svg class="w-24 h-24 text-gray-800" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path fill-rule="evenodd"
                            d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4.243a1 1 0 1 0-2 0V11H7.757a1 1 0 1 0 0 2H11v3.243a1 1 0 1 0 2 0V13h3.243a1 1 0 1 0 0-2H13V7.757Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            @foreach ($clothes as $cloth)
                <div class="w-full aspect-[3/4] bg-white rounded-lg shadow-md overflow-hidden">
                    <img src="{{ asset( $cloth->image_url ) }}" alt="Clothing Image" class="w-full h-full object-cover">
                </div>
            @endforeach
        </div>
    </div>
    <div id="tooltip" class="hidden absolute z-50 bg-white rounded-lg shadow-md px-3 py-2">
        <span class="text-black text-center font-bold">Add new clothes of yours!</span>
    </div>

    <div id="addClothModal"
        class="fixed inset-0 pt-12 bg-black/50 backdrop-filter backdrop-blur-[5px]  z-50 hidden items-center justify-center overflow-y-auto">
        <div class="bg-white w-full max-w-xl rounded-lg p-6 relative shadow-lg my-10 max-h-10/12 overflow-y-auto">
            <button id="closeModalBtn"
                class="absolute top-2 right-2 text-gray-600 hover:text-red-500 text-xl">&times;</button>

            <h2 class="text-2xl font-bold mb-4 text-[#6b4b4b]">Add New Clothing</h2>

            <form action="{{ route('cloth.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <span class="mb-2">Name<span class="text-red-500"> *</span></span>
                <input value="{{ old('name') }}" type="text" name="name" placeholder="Name" required class="w-full p-2 border rounded">
                <span class="mb-2">Category<span class="text-red-500"> *</span></span>
                <select name="category" required class="w-full p-2 border rounded">
                    <option value="">Choose Type</option>
                    <option value="casual">Casual</option>
                    <option value="formal">Formal</option>
                    <option value="sportswear">Sportswear</option>
                    <option value="business">Business</option>
                </select>
                <span class="mb-2">Color<span class="text-red-500"> *</span></span>
                <input value="{{ old('color') }}" type="text" name="color" placeholder="Color" required class="w-full p-2 border rounded">
                <span class="mb-2">Season<span class="text-red-500"> *</span></span>
                <input value="{{ old('season') }}" type="text" name="season" placeholder="Season" required class="w-full p-2 border rounded">
                <span class="mb-2">Type<span class="text-red-500"> *</span></span>
                <select name="type" required class="w-full p-2 border rounded px-4">
                    <option value="">Choose Type</option>
                    <option value="top">Top</option>
                    <option value="bottom">Bottom</option>
                </select>
                <label for="image" class="block mb-2 font-medium text-gray-700">Upload Image<span class="text-red-500">
                        *</span></label>
                <label
                    class="flex items-center justify-center w-full px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm text-gray-600 cursor-pointer hover:bg-gray-100 transition gap-3">
                    <svg class="w-8 h-8 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                        height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 5v9m-5 0H5a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1h-2M8 9l4-5 4 5m1 8h.01" />
                    </svg>
                    Choose a file
                    <input value="{{ old('image') }}" id="image" name="image" type="file" accept="image/*" required class="hidden">                   
                </label>
                <img id="imagePreview" src="#" alt="Image Preview" class="mt-4 max-h-48 rounded hidden">
                <p id="imageFileName" class="mt-2 text-sm text-gray-600 hidden"></p>
                <div class="text-right">
                    <button type="submit" class="bg-[#6b4b4b] text-white px-4 py-2 rounded hover:bg-[#5a3e3e] transition">
                        Add Clothing
                    </button>
                </div>
            </form>
        </div>
    </div>
    <input id="guestId" class="hidden" value="{{ $guestId }}"></input>
@endsection

@section('script')
    <script>
        const target = document.getElementById('hoverTarget');
        const tooltip = document.getElementById('tooltip');
        const guestId = document.getElementById('guestId').value;
        const body = document.body;

        target.addEventListener('mouseenter', () => {
            tooltip.classList.remove('hidden');
        });

        target.addEventListener('mousemove', (e) => {
            tooltip.style.left = `${e.pageX}px`;
            tooltip.style.top = `${e.pageY + 10}px`;
        });

        target.addEventListener('mouseleave', () => {
            tooltip.classList.add('hidden');
        });
        const openModalBtn = document.getElementById('openModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const modal = document.getElementById('addClothModal');

        openModalBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            body.classList.add('overflow-hidden');
        });

        closeModalBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            body.classList.remove('overflow-hidden');
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                body.classList.remove('overflow-hidden');
            }
        });

        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const fileNameDisplay = document.getElementById('imageFileName');
            const file = e.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    preview.src = event.target.result;
                    preview.classList.remove('hidden');
                    fileNameDisplay.textContent = file.name; // show file name
                    fileNameDisplay.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
        console.log(guestId);
    </script>
@endsection
