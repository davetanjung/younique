@extends('components.layout')

@section('content')
    <div class="flex flex-col min-h-screen w-full px-4 md:px-12 py-8 md:py-12 bg-[#F5F0E6]">
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-bold text-[#2E2E2E]">Planner Calendar</h1>
            <p class="text-xl text-[#6B6B6B] mt-2">Organize your outfits for every day of the month.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 mb-8">
            <button onclick="prevMonth()"
                class="text-gray-600 hover:text-black p-2 rounded-full hover:bg-amber-100 transition-colors">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>

            <div id="month-year-display"
                class="text-3xl font-medium transition-opacity duration-300 opacity-100 min-w-[200px] text-center md:min-w-[250px]">
                <!-- Month Year will be populated here -->
            </div>

            <button onclick="nextMonth()"
                class="text-gray-600 hover:text-black p-2 rounded-full hover:bg-amber-100 transition-colors">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    class="transform rotate-180">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
            <button id="generateOutfitsBtn" onclick="generateMonthlyOutfits()"
                class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors flex items-center gap-2 ml-auto shadow-sm">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M16 3H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H16C17.6569 21 19 19.6569 19 18V6C19 4.34315 17.6569 3 16 3Z"
                        stroke="white" stroke-width="2" />
                    <path d="M12 8V16" stroke="white" stroke-width="2" stroke-linecap="round" />
                    <path d="M8 12H16" stroke="white" stroke-width="2" stroke-linecap="round" />
                </svg>
                Generate This Month's Outfits
            </button>
        </div>
        <div class="w-full border-collapse rounded-lg overflow-hidden shadow-md">
            <div class="grid grid-cols-7">
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-center px-2 bg-[#F3D4B5] font-medium">Mon</div>
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-center px-2 bg-[#F3D4B5] font-medium">Tue</div>
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-center px-2 bg-[#F3D4B5] font-medium">Wed</div>
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-center px-2 bg-[#F3D4B5] font-medium">Thu</div>
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-center px-2 bg-[#F3D4B5] font-medium">Fri</div>
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-center px-2 bg-[#F3D4B5] font-medium">Sat</div>
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-center px-2 bg-[#F3D4B5] font-medium">Sun</div>
            </div>
            <div id="calendar" class="grid grid-cols-7">
                <!-- Calendar cells will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="dayModal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden overflow-y-auto px-4 py-8">
        <div class="bg-white w-full max-w-3xl p-6 rounded-2xl shadow-xl relative overflow-y-auto max-h-[90vh]">
            <button onclick="closeModal()"
                class="absolute top-4 right-4 text-gray-500 hover:text-black text-xl leading-none h-8 w-8 flex items-center justify-center rounded-full hover:bg-gray-100">×</button>

            <h2 class="text-2xl font-semibold mb-4 text-center text-gray-800" id="modalDateTitle">Outfit for...</h2>

            <input type="hidden" id="modalDateInput"> <!-- For storing currentDateStr -->
            <input type="hidden" id="modalGuestIdInput" value="{{ $guestId }}">

            <div class="bg-amber-50 p-4 rounded-lg mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label for="modalOccasionInput" class="block text-sm font-medium text-gray-700 mb-1">Occasion for
                            the
                            Day</label>
                        <input type="text" id="modalOccasionInput" name="occasion"
                            placeholder="e.g., Work Meeting, Casual Dinner"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-amber-300 text-sm" />
                    </div>
                    <div>
                        <label for="modalOutfitColorInput" class="block text-sm font-medium text-gray-700 mb-1">Dominant
                            Color</label>
                        <input type="text" id="modalOutfitColorInput" name="outfit_color"
                            placeholder="e.g., Blue, Neutral"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-amber-300 text-sm" />
                    </div>
                    <div class="md:col-span-3">
                        <label for="modalOutfitNameInput" class="block text-sm font-medium text-gray-700 mb-1">Outfit
                            Name</label>
                        <input type="text" id="modalOutfitNameInput" name="outfit_name"
                            placeholder="Give your outfit a name (optional)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-amber-300 text-sm" />
                    </div>
                </div>
            </div>

            <!-- Section 1: Current Day's Outfit -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-2 flex items-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg" class="mr-2">
                        <path d="M3 7L9 4L15 7L21 4V17L15 20L9 17L3 20V7Z" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M9 4V17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M15 7V20" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    Selected Items for This Outfit
                </h3>
                <div id="currentOutfitContainer"
                    class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3 p-4 border rounded-lg bg-gray-50 min-h-[140px]">
                    <p id="noOutfitMessage" class="text-gray-500 italic col-span-full text-center py-10 text-sm">No
                        clothes
                        selected yet. Add from your wardrobe below.</p>
                </div>
            </div>

            <!-- Section 2: Available Clothes (Wardrobe) -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-2 flex items-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg" class="mr-2">
                        <path
                            d="M3 6C3 4.34315 4.34315 3 6 3H18C19.6569 3 21 4.34315 21 6V18C21 19.6569 19.6569 21 18 21H6C4.34315 21 3 19.6569 3 18V6Z"
                            stroke="currentColor" stroke-width="2" />
                        <path d="M3 9H21" stroke="currentColor" stroke-width="2" />
                        <path d="M9 21V9" stroke="currentColor" stroke-width="2" />
                    </svg>
                    Add from Your Wardrobe
                </h3>
                <div class="flex flex-col sm:flex-row flex-wrap gap-2 mb-3 p-3 border-b rounded-t-lg bg-gray-50">
                    <div class="relative flex-grow">
                        <svg class="absolute left-2 top-2.5 text-gray-400" width="16" height="16"
                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <input type="text" id="clothingSearchFilter" placeholder="Search name..."
                            onkeyup="filterClothes()"
                            class="pl-8 p-2 border border-gray-300 rounded-md flex-grow text-sm focus:ring-amber-300 focus:border-amber-300 w-full">
                    </div>
                    <select id="clothingTypeFilter" onchange="filterClothes()"
                        class="p-2 border border-gray-300 rounded-md text-sm focus:ring-amber-300 focus:border-amber-300">
                        <option value="all">All Types</option>
                        <option value="top">Top</option>
                        <option value="bottom">Bottom</option>
                        <option value="outerwear">Outerwear</option>
                        <option value="dress">Dress</option>
                        <option value="shoes">Shoes</option>
                        <option value="accessory">Accessory</option>
                        <option value="other">Other</option>
                    </select>
                    <select id="clothingCategoryFilter" onchange="filterClothes()"
                        class="p-2 border border-gray-300 rounded-md text-sm focus:ring-amber-300 focus:border-amber-300">
                        <option value="all">All Categories</option>
                        <option value="casual">Casual</option>
                        <option value="formal">Formal</option>
                        <option value="sportswear">Sportswear</option>
                        <option value="business">Business</option>
                        <option value="nightwear">Nightwear</option>
                        <option value="unknown">Unknown</option>
                    </select>
                </div>
                <div id="availableClothesGridWrapper" class="bg-slate-100 rounded-b-lg border border-t-0 border-gray-200">
                    <div id="availableClothesGrid"
                        class="flex overflow-x-auto py-4 px-3 gap-3 min-h-[180px] items-center">
                        <!-- Available clothes will be populated here by JS, horizontally scrollable -->
                        <p class="text-gray-500 italic text-center w-full py-4">Loading available clothes...</p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Upload New Clothes to Wardrobe -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-2 flex items-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg" class="mr-2">
                        <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    Add a New Item to Your Wardrobe
                </h3>
                <form id="uploadForm" class="space-y-3 p-4 border rounded-lg bg-gray-50">
                    @csrf
                    <input type="hidden" name="guest_id" value="{{ $guestId }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label for="uploadItemName" class="block text-xs font-medium text-gray-600 mb-0.5">Item
                                Name</label>
                            <input type="text" id="uploadItemName" name="name"
                                class="w-full p-1.5 border border-gray-300 rounded-md text-sm focus:ring-amber-300 focus:border-amber-300">
                        </div>
                        <div>
                            <label for="uploadItemColor" class="block text-xs font-medium text-gray-600 mb-0.5">Item
                                Color</label>
                            <input type="text" id="uploadItemColor" name="color"
                                class="w-full p-1.5 border border-gray-300 rounded-md text-sm focus:ring-amber-300 focus:border-amber-300">
                        </div>
                        <div>
                            <label for="uploadImageType"
                                class="block text-xs font-medium text-gray-600 mb-0.5">Type</label>
                            <select id="uploadImageType" name="type"
                                class="w-full p-1.5 border border-gray-300 rounded-md text-sm h-[34px] focus:ring-amber-300 focus:border-amber-300">
                                <option value="top">Top</option>
                                <option value="bottom">Bottom</option>
                                <option value="outerwear">Outerwear</option>
                                <option value="dress">Dress</option>
                                <option value="shoes">Shoes</option>
                                <option value="accessory">Accessory</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label for="uploadImageCategory"
                                class="block text-xs font-medium text-gray-600 mb-0.5">Category</label>
                            <select id="uploadImageCategory" name="category"
                                class="w-full p-1.5 border border-gray-300 rounded-md text-sm h-[34px] focus:ring-amber-300 focus:border-amber-300">
                                <option value="casual">Casual</option>
                                <option value="formal">Formal</option>
                                <option value="sportswear">Sportswear</option>
                                <option value="business">Business</option>
                                <option value="nightwear">Nightwear</option>
                                <option value="unknown">Unknown</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-span-full">
                        <label for="uploadImagesInput" class="block text-xs font-medium text-gray-600 mb-0.5">Image
                            File</label>
                        <input type="file" name="image" id="uploadImagesInput" accept="image/*"
                            class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm file:mr-3 file:py-1.5 file:px-4 file:border-0 file:text-xs file:font-semibold file:bg-green-600 file:text-white hover:file:bg-green-700 file:rounded-md file:transition-colors">
                    </div>
                    <button type="button" onclick="uploadNewClothes()" id="uploadNewClothesBtn"
                        class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 text-sm flex items-center justify-center gap-2 transition-colors">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 5V19M5 12H19" stroke="white" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        Upload to Wardrobe
                    </button>
                </form>
            </div>

            <!-- Actions -->
            <div class="flex justify-end pt-4 border-t">
                <button type="button" onclick="closeModal()"
                    class="text-gray-600 mr-4 px-5 py-2 rounded-lg hover:bg-gray-100 border border-gray-300 text-sm transition-colors">Cancel</button>
                <button type="button" id="saveOutfitChangesBtn" onclick="saveOutfitChanges()"
                    class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors text-sm flex items-center justify-center gap-2 shadow-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 12L9 16L19 6" stroke="white" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    Save Outfit for Day
                </button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let plannerEntries = {};
        let guestId = document.getElementById('modalGuestIdInput').value;
        const assetBase = "{{ rtrim(asset(''), '/') }}"; // Ensure no trailing slash
        const calendarEl = document.getElementById('calendar');
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October',
            'November', 'December'
        ];

        let currentDateStr = ''; // YYYY-MM-DD for the currently open modal
        let currentPlannerEntry = null; // Store the full planner entry for the modal
        let availableClothes = []; // All clothes for the user
        let currentSelectedClothes = []; // Clothes selected for the outfit in the modal (full objects)

        const currentDate = new Date();
        let currentYear = currentDate.getFullYear();
        let currentMonth = currentDate.getMonth();


        function renderCalendar(monthIndex, year) {
            const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();
            const firstDayOfMonth = new Date(year, monthIndex, 1);
            const startDayOfWeek = firstDayOfMonth.getDay();
            const calendarContainer = document.getElementById('calendar');
            calendarContainer.innerHTML = '';

            const adjustedStartDay = (startDayOfWeek === 0) ? 6 : startDayOfWeek - 1;

            for (let i = 0; i < adjustedStartDay; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.classList.add('border', 'border-[#C2C2C2]', 'h-40', 'relative', 'bg-gray-50', 'opacity-50');
                calendarContainer.appendChild(emptyCell);
            }

            const today = new Date();
            const isCurrentViewingMonth = today.getMonth() === monthIndex && today.getFullYear() === year;
            const todayDate = today.getDate();

            for (let day = 1; day <= daysInMonth; day++) {
                const dayCell = document.createElement('div');
                dayCell.classList.add('border', 'border-[#C2C2C2]', 'h-40', 'relative', 'text-black', 'p-1',
                    'cursor-pointer', 'hover:bg-amber-50', 'transition-colors', 'flex', 'flex-col');

                if (isCurrentViewingMonth && day === todayDate) {
                    dayCell.classList.add('bg-amber-100');
                }

                const dateStr = `${year}-${String(monthIndex + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

                const dayNumber = document.createElement('div');
                dayNumber.classList.add('text-xs', 'font-semibold', 'self-start', 'mb-1', 'bg-white', 'rounded-full', 'w-5',
                    'h-5', 'flex', 'items-center', 'justify-center', 'shadow-sm');
                dayNumber.innerText = day;
                dayCell.appendChild(dayNumber);

                const contentArea = document.createElement('div');
                contentArea.classList.add('flex', 'flex-col', 'flex-grow', 'overflow-hidden');

                const imageGroup = document.createElement('div');
                imageGroup.classList.add('flex', 'justify-center', 'items-center', 'gap-1', 'mt-1', 'flex-wrap',
                    'overflow-hidden', 'max-h-[calc(100%-20px)]'); // Max height for images

                const plannerEntry = plannerEntries[dateStr];

                if (plannerEntry) {
                    if (plannerEntry.occasion) {
                        const occasionText = document.createElement('div');
                        occasionText.classList.add('text-xs', 'text-center', 'text-gray-600', 'w-full', 'truncate', 'mb-1');
                        occasionText.innerText = plannerEntry.occasion;
                        contentArea.appendChild(occasionText);
                    }

                    if (plannerEntry.outfit && plannerEntry.outfit.clothes && plannerEntry.outfit.clothes.length > 0) {
                        plannerEntry.outfit.clothes.slice(0, 3).forEach(cloth => { // Show max 3 items
                            if (cloth.image_url) {
                                const img = document.createElement('img');
                                let imageUrl = cloth.image_url.startsWith('http') || cloth.image_url.startsWith(
                                    '/') ? cloth.image_url : `${assetBase}/${cloth.image_url}`;

                                img.src = imageUrl;
                                img.alt = cloth.name || 'Outfit item';
                                img.classList.add('w-10', 'h-10', 'object-cover', 'rounded-lg', 'border',
                                    'border-gray-200', 'shadow-sm');
                                img.onerror = () => {
                                    img.src = `${assetBase}/api/placeholder/40/40`;
                                    img.alt = 'Error';
                                };
                                imageGroup.appendChild(img);
                            }
                        });
                        contentArea.appendChild(imageGroup);
                    } else {
                        const noOutfit = document.createElement('div');
                        noOutfit.classList.add('text-xs', 'text-gray-400', 'italic', 'text-center', 'flex-grow', 'flex',
                            'items-center', 'justify-center');
                        noOutfit.innerText = 'No outfit';
                        contentArea.appendChild(noOutfit);
                    }
                } else {
                    const noEntry = document.createElement('div');
                    noEntry.classList.add('text-xs', 'text-gray-400', 'italic', 'text-center', 'flex-grow', 'flex',
                        'items-center', 'justify-center');
                    noEntry.innerText = 'No entry';
                    contentArea.appendChild(noEntry);
                }
                dayCell.appendChild(contentArea);

                dayCell.addEventListener('click', () => {
                    openModal(dateStr);
                });
                calendarContainer.appendChild(dayCell);
            }

            const totalCells = adjustedStartDay + daysInMonth;
            const remainingCells = (totalCells % 7 === 0) ? 0 : 7 - (totalCells % 7);
            for (let i = 0; i < remainingCells; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.classList.add('border', 'border-[#C2C2C2]', 'h-40', 'relative', 'bg-gray-50', 'opacity-50');
                calendarContainer.appendChild(emptyCell);
            }
        }

        async function fetchPlannerEntries(monthIndex, year) {
            if (!guestId) {
                console.error("Guest ID is missing.");
                calendarEl.innerHTML =
                    '<div class="col-span-7 text-center py-10 text-red-500">Error: User session not found.</div>';
                return;
            }
            calendarEl.innerHTML = `<div class="col-span-7 text-center py-10 text-gray-500 flex justify-center items-center">
            <svg class="animate-spin h-8 w-8 text-gray-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg> Loading calendar...</div>`;

            try {
                const response = await fetch(`/planner-data?month=${monthIndex + 1}&year=${year}`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                plannerEntries = {};
                for (const dateKey in data) {
                    const entry = data[dateKey];
                    if (entry.outfit && (typeof entry.outfit.clothes === 'undefined' || entry.outfit.clothes ===
                            null)) {
                        entry.outfit.clothes = [];
                    }
                    plannerEntries[dateKey] = entry;
                }
                renderCalendar(monthIndex, year);
            } catch (error) {
                console.error("Failed to fetch planner entries:", error);
                calendarEl.innerHTML =
                    `<div class="col-span-7 text-center py-10 text-red-500">Failed to load calendar data.</div>`;
            }
        }

        async function fetchAvailableClothes(forceFetch = false) {
            if (!guestId) {
                console.error("Guest ID is missing. Cannot fetch available clothes.");
                return;
            }
            if (availableClothes.length > 0 && !forceFetch) {
                renderAvailableClothes(); // Use cached if available and not forcing fetch
                return;
            }
            document.getElementById('availableClothesGrid').innerHTML =
                '<p class="text-gray-500 italic text-center w-full py-4">Loading available clothes...</p>';
            try {
                const response = await fetch(`/available-clothes?guest_id=${guestId}`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                availableClothes = await response.json();
                filterClothes(); // This will call renderAvailableClothes
            } catch (error) {
                console.error("Failed to fetch available clothes:", error);
                availableClothes = [];
                document.getElementById('availableClothesGrid').innerHTML =
                    '<p class="text-red-500 text-center w-full py-4">Failed to load clothes. Please try again.</p>';
            }
        }

        async function openModal(dateString) {
            currentDateStr = dateString;
            currentPlannerEntry = plannerEntries[dateString] || null;

            const modal = document.getElementById('dayModal');
            const modalDateTitle = document.getElementById('modalDateTitle');
            const modalDateInput = document.getElementById('modalDateInput');

            const displayDate = new Date(dateString + 'T00:00:00'); // Ensure correct date parsing
            modalDateTitle.innerText =
                `Outfit for ${displayDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}`;
            modalDateInput.value = dateString;

            document.getElementById('modalOccasionInput').value = currentPlannerEntry?.occasion || '';
            document.getElementById('modalOutfitNameInput').value = currentPlannerEntry?.outfit?.name || '';
            document.getElementById('modalOutfitColorInput').value = currentPlannerEntry?.outfit?.color || '';

            currentSelectedClothes = currentPlannerEntry?.outfit?.clothes ? [...currentPlannerEntry.outfit.clothes] :
        [];

            document.getElementById('uploadForm').reset(); // Reset upload form fields

            renderCurrentOutfit();
            await fetchAvailableClothes(); // Ensures clothes are loaded/rendered for selection

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            modal.scrollTop = 0;
        }

        function closeModal() {
            document.getElementById('dayModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            currentDateStr = '';
            currentPlannerEntry = null;
            currentSelectedClothes = [];
            // Reset filter inputs
            document.getElementById('clothingSearchFilter').value = '';
            document.getElementById('clothingTypeFilter').value = 'all';
            document.getElementById('clothingCategoryFilter').value = 'all';
        }

        function renderCurrentOutfit() {
            const container = document.getElementById('currentOutfitContainer');
            const noOutfitMsg = document.getElementById('noOutfitMessage');
            container.innerHTML = ''; // Clear previous items

            if (currentSelectedClothes.length === 0) {
                noOutfitMsg.classList.remove('hidden');
                container.appendChild(noOutfitMsg); // Re-add if cleared
                return;
            }
            noOutfitMsg.classList.add('hidden');

            currentSelectedClothes.forEach((cloth, index) => {
                const clothCard = document.createElement('div');
                clothCard.className =
                    'relative p-2 border rounded-lg shadow-sm bg-white group flex flex-col items-center text-center';

                let imageUrl = cloth.image_url ?
                    (cloth.image_url.startsWith('http') || cloth.image_url.startsWith('/') ? cloth.image_url :
                        `${assetBase}/${cloth.image_url}`) :
                    `${assetBase}/api/placeholder/80/80`;

                clothCard.innerHTML = `
                <img src="${imageUrl}" alt="${cloth.name || 'Cloth item'}" class="w-20 h-20 object-cover rounded-md mb-2 border" 
                     onerror="this.src='${assetBase}/api/placeholder/80/80'; this.alt='Error';">
                <p class="text-xs font-medium truncate w-full" title="${cloth.name || 'Unnamed Item'}">${cloth.name || 'Unnamed Item'}</p>
                <p class="text-xxs text-gray-500 truncate w-full">${cloth.type || 'N/A'} / ${cloth.category || 'N/A'}</p>
                <button onclick="removeClothFromOutfit(${cloth.id})" 
                        class="absolute -top-1.5 -right-1.5 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity focus:opacity-100"
                        title="Remove item">
                    ×
                </button>
            `;
                container.appendChild(clothCard);
            });
        }

        function renderAvailableClothes(filteredList = null) {
            const grid = document.getElementById('availableClothesGrid');
            grid.innerHTML = '';

            const clothesToRender = filteredList !== null ? filteredList : availableClothes;

            if (availableClothes.length === 0 && filteredList === null) {
                grid.innerHTML =
                    '<p class="text-gray-500 italic text-center w-full py-4">No clothes in your wardrobe. Upload some below!</p>';
                return;
            }
            if (clothesToRender.length === 0) {
                grid.innerHTML =
                    '<p class="text-gray-500 italic text-center w-full py-4">No clothes match your filters.</p>';
                return;
            }

            clothesToRender.forEach(cloth => {
                const isSelected = currentSelectedClothes.some(selected => selected.id === cloth.id);

                const clothCard = document.createElement('div');
                // flex-shrink-0 is important for horizontal scroll items
                clothCard.className =
                    `p-2 border rounded-lg shadow-sm bg-white group relative flex flex-col items-center text-center w-32 flex-shrink-0 ${isSelected ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-md cursor-pointer transition-shadow'}`;

                let imageUrl = cloth.image_url ?
                    (cloth.image_url.startsWith('http') || cloth.image_url.startsWith('/') ? cloth.image_url :
                        `${assetBase}/${cloth.image_url}`) :
                    `${assetBase}/api/placeholder/80/80`;


                clothCard.innerHTML = `
                <img src="${imageUrl}" alt="${cloth.name || 'Cloth item'}" class="w-20 h-20 object-cover rounded-md mb-1 border" 
                    onerror="this.src='${assetBase}/api/placeholder/80/80'; this.alt='Error';">
                <p class="text-xs font-medium truncate w-full" title="${cloth.name || 'Unnamed Item'}">${cloth.name || 'Unnamed Item'}</p>
                <p class="text-xxs text-gray-500 truncate w-full">${cloth.type || 'N/A'} / ${cloth.category || 'N/A'}</p>
                ${isSelected 
                    ? `<div class="absolute top-1 right-1 bg-green-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xxs" title="Selected">✓</div>` 
                    : `<button onclick="addClothToOutfit(${cloth.id})" 
                                        class="absolute top-1 right-1 bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity focus:opacity-100" 
                                        title="Add item">
                                   +
                                </button>`
                }
            `;
                grid.appendChild(clothCard);
            });
        }

        function filterClothes() {
            const typeFilter = document.getElementById('clothingTypeFilter').value.toLowerCase();
            const categoryFilter = document.getElementById('clothingCategoryFilter').value.toLowerCase();
            const searchFilter = document.getElementById('clothingSearchFilter').value.toLowerCase().trim();

            const filtered = availableClothes.filter(cloth => {
                const nameMatch = cloth.name ? cloth.name.toLowerCase().includes(searchFilter) : (searchFilter ===
                    '');
                const typeMatch = typeFilter === 'all' || (cloth.type && cloth.type.toLowerCase() === typeFilter);
                const categoryMatch = categoryFilter === 'all' || (cloth.category && cloth.category
                    .toLowerCase() === categoryFilter);
                return nameMatch && typeMatch && categoryMatch;
            });
            renderAvailableClothes(filtered);
        }

        function addClothToOutfit(clothId) {
            const clothToAdd = availableClothes.find(c => c.id === clothId);
            if (!clothToAdd || currentSelectedClothes.some(c => c.id === clothId)) return;

            currentSelectedClothes.push(clothToAdd);
            renderCurrentOutfit();
            filterClothes(); // Re-render available list to update its state (e.g. show checkmark)
        }

        function removeClothFromOutfit(clothId) {
            currentSelectedClothes = currentSelectedClothes.filter(c => c.id !== clothId);
            renderCurrentOutfit();
            filterClothes(); // Re-render available list
        }

        async function uploadNewClothes() {
            const form = document.getElementById('uploadForm');
            const formData = new FormData(form);
            const uploadButton = document.getElementById('uploadNewClothesBtn');
            const originalButtonHTML = uploadButton.innerHTML;

            uploadButton.disabled = true;
            uploadButton.innerHTML =
                `<svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Uploading...`;

            try {
                // Ensure CSRF token is included, which it should be via @csrf in the form
                const response = await fetch('/clothes/upload', { // Make sure this route exists and handles uploads
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                    }
                });
                const result = await response.json();

                if (response.ok && result.success && result.cloth) { // Assuming single cloth upload
                    if (!availableClothes.some(c => c.id === result.cloth.id)) {
                        availableClothes.unshift(result.cloth); // Add to beginning of global list
                    }
                    filterClothes(); // Refresh available clothes grid
                    form.reset();
                    document.getElementById('uploadImagesInput').value = '';
                    alert('New item uploaded successfully to your wardrobe!');
                } else {
                    let errorMsg = result.message || 'Failed to upload item.';
                    if (result.errors) {
                        errorMsg += '\n' + Object.values(result.errors).flat().join('\n');
                    }
                    alert(errorMsg);
                }
            } catch (error) {
                console.error('Error uploading new item:', error);
                alert('An error occurred during upload: ' + error.message);
            } finally {
                uploadButton.disabled = false;
                uploadButton.innerHTML = originalButtonHTML;
            }
        }

        async function saveOutfitChanges() {
            const saveButton = document.getElementById('saveOutfitChangesBtn');
            const originalButtonHTML = saveButton.innerHTML;
            saveButton.disabled = true;
            saveButton.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg> Saving...`;

            const payload = {
                date: currentDateStr,
                guest_id: guestId,
                occasion: document.getElementById('modalOccasionInput').value.trim(),
                outfit_name: document.getElementById('modalOutfitNameInput').value.trim(),
                outfit_color: document.getElementById('modalOutfitColorInput').value.trim(),
                clothing_ids: currentSelectedClothes.map(c => c.id)
            };

            const csrfToken = document.querySelector('#uploadForm input[name="_token"]')
                .value; // Get CSRF from upload form

            try {
                const response = await fetch('/planner/save', { // Make sure this route exists
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();

                if (response.ok && result.success) {
                    if (result.plannerEntry) {
                        if (result.plannerEntry.outfit && (typeof result.plannerEntry.outfit.clothes === 'undefined' ||
                                result.plannerEntry.outfit.clothes === null)) {
                            result.plannerEntry.outfit.clothes = [];
                        }
                        plannerEntries[result.plannerEntry.date] = result.plannerEntry;
                        renderCalendar(currentMonth, currentYear);
                    } else {
                        await fetchPlannerEntries(currentMonth, currentYear); // Fallback
                    }
                    closeModal();
                    // Consider a toast notification for success
                } else {
                    let errorMsg = result.message || 'Failed to save outfit.';
                    if (result.errors) errorMsg += '\n' + Object.values(result.errors).flat().join('\n');
                    alert(errorMsg);
                }
            } catch (error) {
                console.error('Error saving outfit changes:', error);
                alert('An error occurred: ' + error.message);
            } finally {
                saveButton.disabled = false;
                saveButton.innerHTML = originalButtonHTML;
            }
        }

        async function generateMonthlyOutfits() {
            if (!guestId) {
                alert("Session information missing. Please refresh and try again.");
                return;
            }
            const generateBtn = document.getElementById('generateOutfitsBtn');
            const originalBtnHTML = generateBtn.innerHTML;
            generateBtn.innerHTML =
                `<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...`;
            generateBtn.disabled = true;

            calendarEl.innerHTML =
                `<div class="col-span-7 text-center py-10 text-gray-500 flex justify-center items-center"><svg class="animate-spin h-8 w-8 text-gray-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating new outfits...</div>`;
            const csrfToken = document.querySelector('input[name="_token"]').value;

            try {
                const response = await fetch('/planner/generate-monthly', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        month: currentMonth + 1,
                        year: currentYear,
                        guest_id: guestId
                    })
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Failed to generate outfits');

                if (result.entries && Array.isArray(result.entries)) {
                    plannerEntries = {};
                    result.entries.forEach(entry => {
                        if (entry.outfit && (typeof entry.outfit.clothes === 'undefined' || entry.outfit
                                .clothes === null)) {
                            entry.outfit.clothes = [];
                        }
                        plannerEntries[entry.date] = entry;
                    });
                    renderCalendar(currentMonth, currentYear);
                    alert(`Successfully generated outfits for ${months[currentMonth]} ${currentYear}.`);
                } else if (result.message) {
                    alert(result.message);
                    fetchPlannerEntries(currentMonth, currentYear);
                } else {
                    throw new Error('No entries returned or invalid format');
                }
            } catch (error) {
                console.error('Failed to generate outfits:', error);
                alert(`Failed to generate outfits: ${error.message}.`);
                fetchPlannerEntries(currentMonth, currentYear);
            } finally {
                generateBtn.innerHTML = originalBtnHTML;
                generateBtn.disabled = false;
            }
        }

        // --- Calendar Navigation & Initial Load ---
        function updateDisplay() {
            const display = document.getElementById("month-year-display");
            if (!display) return;
            display.classList.remove('opacity-100');
            display.classList.add('opacity-0');

            setTimeout(() => {
                display.innerText = `${months[currentMonth]} ${currentYear}`;
                display.classList.remove('opacity-0');
                display.classList.add('opacity-100');
                fetchPlannerEntries(currentMonth, currentYear);
            }, 150);
        }

        function prevMonth() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            updateDisplay();
        }

        function nextMonth() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            updateDisplay();
        }

        window.addEventListener('keydown', (e) => {
            const modal = document.getElementById('dayModal');
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (guestId) {
                updateDisplay();
                fetchAvailableClothes(true); // Pre-fetch all available clothes on load
            } else {
                console.error("Guest ID missing on initial load.");
                calendarEl.innerHTML =
                    '<div class="col-span-7 text-center py-10 text-red-500">Error: User session not found.</div>';
                document.getElementById('month-year-display').innerText = 'Error';
            }
        });
    </script>
@endsection
