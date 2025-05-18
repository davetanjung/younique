@extends('components.layout')

@section('content')
    <div class="bg-[#f5efe4] min-h-screen p-10 w-full">
        <h1 class="text-3xl font-bold text-[#6b4b4b] mb-2">My Wardrobe</h1>
        <p class="text-[#6b4b4b] mb-8">Manage your clothing collection with ease</p>

        <div class="flex mb-6 gap-4">
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Search clothes..."
                    class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#6b4b4b] focus:border-transparent">
                <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <select id="filterType"
                class="px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#6b4b4b]">
                <option value="">All Types</option>
                <option value="top">Top</option>
                <option value="bottom">Bottom</option>
            </select>
            <select id="filterSeason"
                class="px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#6b4b4b]">
                <option value="">All Seasons</option>
                <option value="spring">Spring</option>
                <option value="summer">Summer</option>
                <option value="fall">Fall</option>
                <option value="winter">Winter</option>
                <option value="all-season">All Season</option>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <div id="openModal">
                <button id="hoverTarget" type="button"
                    class="group relative w-full aspect-[3/4] bg-white rounded-lg shadow-md overflow-hidden flex flex-col justify-center items-center hover:bg-gray-100 transition-colors duration-300">
                    <svg class="w-16 h-16 text-gray-800 mb-2" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path fill-rule="evenodd"
                            d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4.243a1 1 0 1 0-2 0V11H7.757a1 1 0 1 0 0 2H11v3.243a1 1 0 1 0 2 0V13h3.243a1 1 0 1 0 0-2H13V7.757Z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-gray-600 font-medium">Add New Item</span>
                </button>
            </div>
            @foreach ($clothes as $cloth)
                <div class="cloth-item w-full aspect-[3/4] bg-white rounded-lg shadow-md overflow-hidden flex flex-col relative"
                    data-name="{{ $cloth->name }}" data-type="{{ $cloth->type }}" data-season="{{ $cloth->season }}">
                    <!-- Edit and Delete buttons -->
                    <div class="absolute top-2 left-2 flex gap-1 z-10">
                        <button class="edit-btn bg-white/90 text-[#6b4b4b] p-1 rounded-full hover:bg-gray-100 transition"
                            data-id="{{ $cloth->id }}" data-name="{{ $cloth->name }}" data-color="{{ $cloth->color }}"
                            data-type="{{ $cloth->type }}" data-category="{{ $cloth->category }}"
                            data-season="{{ $cloth->season }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </button>
                        <form action="{{ route('cloth.destroy', $cloth->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="delete-btn bg-white/90 text-red-500 p-1 rounded-full hover:bg-gray-100 transition"
                                onclick="return confirm('Are you sure you want to delete this item?')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    <div class="relative h-3/4">
                        <img src="{{ asset($cloth->image_url) }}" alt="{{ $cloth->name }}"
                            class="w-full h-full object-cover mt-12">
                        <div class="absolute top-2 right-2 flex gap-1">
                            <span
                                class="bg-[#6b4b4b]/90 text-white text-xs px-2 py-1 rounded-full">{{ ucfirst($cloth->type) }}</span>
                            <span
                                class="bg-[#6b4b4b]/90 text-white text-xs px-2 py-1 rounded-full">{{ ucfirst($cloth->season) }}</span>
                        </div>
                    </div>
                    <div class="px-3 mt-8 bg-white flex-grow flex flex-col justify-center min-h-[50px]">
                        <h3 class="font-medium text-black truncate">{{ $cloth->name }}</h3>
                        <p class="text-sm text-gray-500">{{ ucfirst($cloth->category) }} • {{ ucfirst($cloth->color) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="noResults" class="hidden w-full py-8 text-center">
            <p class="text-lg text-gray-600">No matching clothes found. Try adjusting your filters.</p>
        </div>
    </div>

    <div id="tooltip" class="hidden absolute z-50 bg-white rounded-lg shadow-md px-3 py-2">
        <span class="text-black text-center font-bold">Add new clothes to your wardrobe!</span>
    </div>

    <!-- Add Cloth Modal -->
    <div id="addClothModal"
        class="fixed inset-0 pt-12 bg-black/50 backdrop-filter backdrop-blur-[5px] z-50 hidden items-center justify-center overflow-y-auto">
        <div class="bg-white w-full max-w-md rounded-lg p-6 relative shadow-lg my-10 max-h-10/12 overflow-y-auto">
            <button id="closeModalBtn"
                class="absolute top-2 right-2 text-gray-600 hover:text-red-500 text-xl">&times;</button>

            <h2 class="text-2xl font-bold mb-4 text-[#6b4b4b]">Add New Clothing</h2>
            <p class="text-gray-600 mb-6">Just upload a photo of your clothing item and we'll automatically detect its
                attributes!</p>

            <form action="{{ route('cloth.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label for="image" class="block font-medium text-gray-700">Upload Image<span class="text-red-500">
                            *</span></label>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-8 flex flex-col items-center cursor-pointer hover:bg-gray-50 transition"
                        id="imageDropArea">
                        <svg class="w-16 h-16 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm text-gray-600 text-center font-medium">Drag & drop your image here, or <span
                                class="text-[#6b4b4b] font-bold">browse</span></p>
                        <p class="text-xs text-gray-500 mt-1">Supported formats: JPG, PNG, WEBP</p>
                        <input id="image" name="image" type="file" accept="image/*" required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                    <div id="imagePreviewContainer" class="hidden mt-4 relative">
                        <img id="imagePreview" src="#" alt="Image Preview"
                            class="w-full h-64 object-contain rounded-lg border">
                        <button type="button" id="removeImage"
                            class="absolute top-2 right-2 bg-white rounded-full p-1 shadow-md hover:bg-gray-100">
                            <svg class="w-5 h-5 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <p id="imageFileName" class="mt-2 text-sm text-gray-600"></p>
                    </div>
                </div>

                <div id="processingState" class="hidden">
                    <div class="flex items-center justify-center py-4">
                        <svg class="animate-spin h-8 w-8 text-[#6b4b4b]" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="ml-3 text-[#6b4b4b] font-medium">Processing your image...</span>
                    </div>
                </div>

                <div class="text-right pt-4">
                    <button type="button" id="cancelBtn"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 mr-2 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                        class="bg-[#6b4b4b] text-white px-4 py-2 rounded-lg hover:bg-[#5a3e3e] transition">
                        Add to Wardrobe
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Cloth Modal -->
    <div id="editClothModal"
        class="fixed inset-0 pt-12 bg-black/50 backdrop-filter backdrop-blur-[5px] z-50 hidden items-center justify-center overflow-y-auto">
        <div class="bg-white w-full max-w-md rounded-lg p-6 relative shadow-lg my-10 max-h-10/12 overflow-y-auto">
            <button id="closeEditModalBtn"
                class="absolute top-2 right-2 text-gray-600 hover:text-red-500 text-xl">&times;</button>

            <h2 class="text-2xl font-bold mb-4 text-[#6b4b4b]">Edit Clothing Item</h2>

            <form id="editClothForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="editId">

                <div class="space-y-2">
                    <label for="editName" class="block font-medium text-gray-700">Name<span class="text-red-500">
                            *</span></label>
                    <input type="text" id="editName" name="name" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#6b4b4b]">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="editColor" class="block font-medium text-gray-700">Color<span class="text-red-500">
                                *</span></label>
                        <input type="text" id="editColor" name="color" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#6b4b4b]">
                    </div>

                    <div class="space-y-2">
                        <label for="editType" class="block font-medium text-gray-700">Type<span class="text-red-500">
                                *</span></label>
                        <select id="editType" name="type" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#6b4b4b]">
                            <option value="top">Top</option>
                            <option value="bottom">Bottom</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="editCategory" class="block font-medium text-gray-700">Category<span
                                class="text-red-500"> *</span></label>
                        <select id="editCategory" name="category" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#6b4b4b]">
                            <option value="casual">Casual</option>
                            <option value="formal">Formal</option>
                            <option value="sportswear">Sportswear</option>
                            <option value="business">Business</option>
                            <option value="loungewear">Loungewear</option>
                            <option value="party">Party</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="editSeason" class="block font-medium text-gray-700">Season<span class="text-red-500">
                                *</span></label>
                        <select id="editSeason" name="season" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#6b4b4b]">
                            <option value="spring">Spring</option>
                            <option value="summer">Summer</option>
                            <option value="fall">Fall</option>
                            <option value="winter">Winter</option>
                            <option value="all-season">All Season</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="editImage" class="block font-medium text-gray-700">Change Image</label>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-8 flex flex-col items-center cursor-pointer hover:bg-gray-50 transition"
                        id="editImageDropArea">
                        <svg class="w-16 h-16 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm text-gray-600 text-center font-medium">Drag & drop new image here, or <span
                                class="text-[#6b4b4b] font-bold">browse</span></p>
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                        <input id="editImage" name="image" type="file" accept="image/*"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                    <div id="editImagePreviewContainer" class="hidden mt-4 relative">
                        <img id="editImagePreview" src="#" alt="Image Preview"
                            class="w-full h-64 object-contain rounded-lg border">
                        <button type="button" id="removeEditImage"
                            class="absolute top-2 right-2 bg-white rounded-full p-1 shadow-md hover:bg-gray-100">
                            <svg class="w-5 h-5 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <p id="editImageFileName" class="mt-2 text-sm text-gray-600"></p>
                    </div>
                </div>

                <div class="text-right pt-4">
                    <button type="button" id="cancelEditBtn"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 mr-2 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" id="submitEditBtn"
                        class="bg-[#6b4b4b] text-white px-4 py-2 rounded-lg hover:bg-[#5a3e3e] transition">
                        Save Changes
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
        const openModalBtn = document.getElementById('openModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const modal = document.getElementById('addClothModal');
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const imageFileName = document.getElementById('imageFileName');
        const removeImageBtn = document.getElementById('removeImage');
        const submitBtn = document.getElementById('submitBtn');
        const processingState = document.getElementById('processingState');
        const searchInput = document.getElementById('searchInput');
        const filterType = document.getElementById('filterType');
        const filterSeason = document.getElementById('filterSeason');
        const clothItems = document.querySelectorAll('.cloth-item');
        const noResults = document.getElementById('noResults');

        // Edit modal elements
        const editModal = document.getElementById('editClothModal');
        const closeEditModalBtn = document.getElementById('closeEditModalBtn');
        const cancelEditBtn = document.getElementById('cancelEditBtn');
        const editForm = document.getElementById('editClothForm');
        const editImageInput = document.getElementById('editImage');
        const editImagePreview = document.getElementById('editImagePreview');
        const editImagePreviewContainer = document.getElementById('editImagePreviewContainer');
        const editImageFileName = document.getElementById('editImageFileName');
        const removeEditImageBtn = document.getElementById('removeEditImage');

        // Tooltip functionality
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

        // Modal functionality
        openModalBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            body.classList.add('overflow-hidden');
        });

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            body.classList.remove('overflow-hidden');
        };

        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Image preview functionality
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.src = event.target.result;
                    imagePreviewContainer.classList.remove('hidden');
                    imageFileName.textContent = file.name;
                }
                reader.readAsDataURL(file);
            }
        });

        // Remove image
        removeImageBtn.addEventListener('click', () => {
            imageInput.value = '';
            imagePreviewContainer.classList.add('hidden');
            imagePreview.src = '#';
        });

        // Show processing state on form submit
        // New code - specific to the add form
        document.querySelector('#addClothModal form').addEventListener('submit', function(e) {
            // Show processing state immediately
            processingState.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';

            // If you want to confirm the image is present
            if (imageInput.files.length === 0) {
                e.preventDefault(); // Prevent form submission if no image
                return;
            }
        });

        // Drag and drop functionality
        const dropArea = document.getElementById('imageDropArea');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropArea.classList.add('border-[#6b4b4b]', 'bg-[#f5efe4]/50');
        }

        function unhighlight() {
            dropArea.classList.remove('border-[#6b4b4b]', 'bg-[#f5efe4]/50');
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length) {
                imageInput.files = files;
                const event = new Event('change', {
                    bubbles: true
                });
                imageInput.dispatchEvent(event);
            }
        }

        // Filtering functionality
        function filterClothes() {
            const searchTerm = searchInput.value.toLowerCase();
            const typeFilter = filterType.value.toLowerCase();
            const seasonFilter = filterSeason.value.toLowerCase();
            let visibleCount = 0;

            clothItems.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                const type = item.getAttribute('data-type').toLowerCase();
                const season = item.getAttribute('data-season').toLowerCase();

                const matchesSearch = name.includes(searchTerm);
                const matchesType = typeFilter === '' || type === typeFilter;
                const matchesSeason = seasonFilter === '' || season === seasonFilter;

                if (matchesSearch && matchesType && matchesSeason) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            if (visibleCount === 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        searchInput.addEventListener('input', filterClothes);
        filterType.addEventListener('change', filterClothes);
        filterSeason.addEventListener('change', filterClothes);

        // Edit functionality
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const color = this.getAttribute('data-color');
                const type = this.getAttribute('data-type');
                const category = this.getAttribute('data-category');
                const season = this.getAttribute('data-season');

                // Set form values
                document.getElementById('editId').value = id;
                document.getElementById('editName').value = name;
                document.getElementById('editColor').value = color;
                document.getElementById('editType').value = type;
                document.getElementById('editCategory').value = category;
                document.getElementById('editSeason').value = season;

                // Set form action
                editForm.action = `/myclothes/${id}`;

                // Show modal
                editModal.classList.remove('hidden');
                editModal.classList.add('flex');
                body.classList.add('overflow-hidden');
            });
        });

        // Edit modal close functionality
        const closeEditModal = () => {
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
            body.classList.remove('overflow-hidden');
        };

        closeEditModalBtn.addEventListener('click', closeEditModal);
        cancelEditBtn.addEventListener('click', closeEditModal);

        window.addEventListener('click', (e) => {
            if (e.target === editModal) {
                closeEditModal();
            }
        });

        // Edit image preview functionality
        editImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    editImagePreview.src = event.target.result;
                    editImagePreviewContainer.classList.remove('hidden');
                    editImageFileName.textContent = file.name;
                }
                reader.readAsDataURL(file);
            }
        });

        // Remove edit image
        removeEditImageBtn.addEventListener('click', () => {
            editImageInput.value = '';
            editImagePreviewContainer.classList.add('hidden');
            editImagePreview.src = '#';
        });

        // Edit form drag and drop
        const editDropArea = document.getElementById('editImageDropArea');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            editDropArea.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            editDropArea.addEventListener(eventName, highlightEdit, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            editDropArea.addEventListener(eventName, unhighlightEdit, false);
        });

        function highlightEdit() {
            editDropArea.classList.add('border-[#6b4b4b]', 'bg-[#f5efe4]/50');
        }

        function unhighlightEdit() {
            editDropArea.classList.remove('border-[#6b4b4b]', 'bg-[#f5efe4]/50');
        }

        editDropArea.addEventListener('drop', handleEditDrop, false);

        function handleEditDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length) {
                editImageInput.files = files;
                const event = new Event('change', {
                    bubbles: true
                });
                editImageInput.dispatchEvent(event);
            }
        }

        console.log(guestId);
    </script>
@endsection
