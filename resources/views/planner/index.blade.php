@extends('components.layout')

@section('content')
    <div class="flex flex-col min-h-screen w-full px-12 py-12 bg-[#F5F0E6]">
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-bold text-[#2E2E2E]">Planner Calendar</h1>
            <p class="text-xl text-[#6B6B6B] mt-2">Organize your outfits for every day of the month.</p>
        </div>
        <div class="flex items-center gap-2 mb-8">
            <button onclick="prevMonth()" class="text-gray-600 hover:text-black">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 18L9 12L15 6" stroke="black" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>

            <div id="month-year-display" class="text-3xl font-medium transition-opacity duration-300 opacity-100">

            </div>

            <button onclick="nextMonth()" class="text-gray-600 hover:text-black">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="black" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        <div class="w-full border-collapse border border-[#C2C2C2]">
            <div class= "grid grid-cols-7">
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-left px-2 bg-[#F3D4B5]">Mon</div>
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-left px-2 bg-[#F3D4B5]">Tue</div>
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-left px-2 bg-[#F3D4B5]">Wed</div>
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-left px-2 bg-[#F3D4B5]">Thu</div>
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-left px-2 bg-[#F3D4B5]">Fri</div>
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-left px-2 bg-[#F3D4B5]">Sat</div>
                <div class="border border-[#C2C2C2] py-2 text-[#585858] text-left px-2 bg-[#F3D4B5]">Sun</div>
            </div>
            <div id="calendar" class="grid grid-cols-7">
                {{-- Calendar grid filled by JavaScript --}}
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div id="dayModal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden overflow-y-auto px-4 py-8">
        <div class="bg-white w-full max-w-2xl p-6 rounded-2xl shadow-xl relative overflow-y-auto max-h-[90vh]">
            <button onclick="closeModal()"
                class="absolute top-3 right-3 text-gray-500 hover:text-black text-2xl">×</button>

            <h2 class="text-3xl font-semibold mb-6 text-center text-gray-800" id="modalDateTitle">Outfit for </h2>

            <div id="modalOutfitImages" class="flex flex-col gap-4 mb-6">
                 {{-- Existing outfit images filled by JavaScript --}}
            </div>

            <form id="modalForm" onsubmit="submitOutfit(event)" class="space-y-4">
                <input type="hidden" name="date" id="modalDateInput">
                {{-- Ensure guestId is correctly passed from controller --}}
                <input type="hidden" name="guest_id" id="modalGuestIdInput" value="{{ $guestId ?? '' }}">
                {{-- Add CSRF token for web routes --}}
                @csrf

                <div>
                    <label for="outfitName" class="block text-sm font-medium text-gray-700 mb-1">Outfit Name</label>
                    <input type="text" id="outfitName" name="name" value="{{ old('name') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-black/30" />
                </div>

                 {{-- Fields to specify Type and Category for NEW uploads --}}
                 {{-- These are REQUIRED for the backend 'save' function to work correctly --}}
                 <div class="grid grid-cols-2 gap-4">
                     <div>
                         <label for="newImageType" class="block text-sm font-medium text-gray-700 mb-1">Type for New Images</label>
                         <select id="newImageType" name="new_image_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-black/30">
                             <option value="top">Top</option>
                             <option value="bottom">Bottom</option>
                             <option value="shoes">Shoes</option>
                             <option value="accessory">Accessory</option>
                             <option value="outerwear">Outerwear</option>
                             <option value="unknown">Unknown</option>
                         </select>
                     </div>
                     <div>
                        <label for="newImageCategory" class="block text-sm font-medium text-gray-700 mb-1">Category for New Images</label>
                        <select id="newImageCategory" name="new_image_category" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-black/30">
                            <option value="casual">Casual</option>
                            <option value="formal">Formal</option>
                            <option value="sportswear">Sportswear</option>
                            <option value="business">Business</option>
                            <option value="nightwear">Nightwear</option>
                            <option value="unknown">Unknown</option>
                        </select>
                    </div>
                 </div>


                <div>
                    <label for="modalImagesInput" class="block text-sm font-medium text-gray-700 mb-1">Add New Outfit Images</label>
                    <input type="file" name="images[]" id="modalImagesInput" multiple
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800" />
                    <p class="text-xs text-gray-500 mt-1">Select Type/Category above before uploading new images.</p>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="button" onclick="closeModal()" class="text-gray-600 mr-4 px-4 py-2 rounded-lg hover:bg-gray-100">Cancel</button>
                    <button type="submit"
                        class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                        Save Outfit
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let plannerEntries = {}; // Store as an object keyed by date string
        const guestId = document.getElementById('modalGuestIdInput')?.value || @json($guestId ?? null); // Try to get from input or Blade
        const assetBase = "{{ asset('') }}"; // Correctly gets the base URL (ends with / if needed)
        const calendar = document.getElementById('calendar');
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October',
            'November', 'December'
        ];

        const currentDate = new Date();
        let currentYear = currentDate.getFullYear();
        let currentMonth = currentDate.getMonth(); // 0-indexed

        // Add this debugging function to better understand the data structure
function debugPlannerData() {
    console.log("Current month/year:", currentMonth + 1, currentYear);
    console.log("All planner entries:", plannerEntries);
    
    // Check a specific date (e.g., May 9th)
    const testDate = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-09`;
    console.log(`Entry for ${testDate}:`, plannerEntries[testDate]);
    
    // Log all keys to see what dates are actually available
    console.log("All available dates:", Object.keys(plannerEntries));
}

// Replace the original renderCalendar function with this improved version
function renderCalendar(monthIndex, year) {
    // Call debug function at the start
    debugPlannerData();
    
    const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();
    const firstDayOfMonth = new Date(year, monthIndex, 1);
    const startDayOfWeek = firstDayOfMonth.getDay(); // 0=Sun, 1=Mon...
    const calendarContainer = document.getElementById('calendar');
    calendarContainer.innerHTML = '';

    const adjustedStartDay = (startDayOfWeek === 0) ? 6 : startDayOfWeek - 1; // 0=Mon, 1=Tue...

    // --- Empty Cells (Previous Month) ---
    for (let i = 0; i < adjustedStartDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.classList.add('border', 'border-[#C2C2C2]', 'h-40', 'relative', 'bg-gray-50', 'opacity-50');
        calendarContainer.appendChild(emptyCell);
    }

    // --- Day Cells (Current Month) ---
    for (let day = 1; day <= daysInMonth; day++) {
        const dayCell = document.createElement('div');
        dayCell.classList.add('border', 'border-[#C2C2C2]', 'h-40', 'relative', 'text-black', 'p-1',
            'cursor-pointer', 'hover:bg-amber-50', 'transition-colors', 'flex', 'flex-col');

        // Ensure proper formatting for single-digit dates (leading zeros)
        const dateStr = `${year}-${String(monthIndex + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        
        // Debug for specific dates (remove in production)
        if (day === 9) {
            console.log(`Checking May 9th specifically: ${dateStr}`, plannerEntries[dateStr]);
        }

        // --- Day Number ---
        const dayNumber = document.createElement('div');
        dayNumber.classList.add('text-xs', 'font-semibold', 'self-start', 'mb-1');
        dayNumber.innerText = day;
        dayCell.appendChild(dayNumber);

        // --- Create imageGroup (always create, populate later) ---
        const imageGroup = document.createElement('div');
        imageGroup.classList.add('flex', 'justify-center', 'items-center', 'gap-1', 'mt-1', 'flex-wrap', 'overflow-hidden', 'flex-grow');
        
        // Get planner entry for this date
        const plannerEntry = plannerEntries[dateStr];
        
        if (plannerEntry) {
            console.log(`Found entry for ${dateStr}:`, plannerEntry);
            
            // --- Display Occasion (if exists) ---
            if (plannerEntry.occasion) {
                const occasionText = document.createElement('div');
                occasionText.classList.add('text-xs', 'text-center', 'text-gray-600', 'w-full', 'truncate');
                occasionText.innerText = plannerEntry.occasion;
                dayCell.appendChild(occasionText);
            }

            // --- Display Outfit Images ---
            if (plannerEntry.outfit && plannerEntry.outfit.clothes && plannerEntry.outfit.clothes.length > 0) {
                console.log(`Found ${plannerEntry.outfit.clothes.length} clothes for ${dateStr}`);
                
                // Show max 2 images
                plannerEntry.outfit.clothes.slice(0, 2).forEach(cloth => {
                    if (cloth.image_url) {
                        console.log(`Processing image: ${cloth.image_url}`);
                        const img = document.createElement('img');
                        
                        // Ensure proper URL construction
                        const imageUrl = cloth.image_url.startsWith('http') 
                            ? cloth.image_url 
                            : (assetBase.endsWith('/') ? assetBase : assetBase + '/') + cloth.image_url;
                        
                        console.log(`Displaying image at: ${imageUrl}`);
                        
                        img.src = imageUrl;
                        img.alt = cloth.name || 'Outfit item';
                        img.classList.add('w-10', 'h-10', 'object-cover', 'rounded-sm');
                        
                        // Add error handling with visible feedback
                        img.onerror = () => { 
                            console.warn(`Image failed to load: ${imageUrl}`);
                            img.src = '/api/placeholder/40/40'; // Fallback image
                            img.classList.add('border', 'border-red-300');
                        };
                        
                        imageGroup.appendChild(img);
                    } else {
                        console.warn(`Cloth item for ${dateStr} has no image_url:`, cloth);
                    }
                });
                
                // Indicator for more images
                if (plannerEntry.outfit.clothes.length > 2) {
                    const moreIndicator = document.createElement('span');
                    moreIndicator.classList.add('text-xs', 'text-gray-500');
                    moreIndicator.innerText = `+${plannerEntry.outfit.clothes.length - 2}`;
                    imageGroup.appendChild(moreIndicator);
                }
            } else {
                console.log(`No clothes found for ${dateStr} entry`);
                imageGroup.innerHTML = `<span class="text-xs text-gray-400 italic">No outfit</span>`;
            }
        } else {
            console.log(`No planner entry for ${dateStr}`);
            imageGroup.innerHTML = `<span class="text-xs text-gray-400 italic"></span>`;
        }

        // --- Append the imageGroup ---
        dayCell.appendChild(imageGroup);

        // --- Click Event ---
        dayCell.addEventListener('click', () => {
            openModal(dateStr, plannerEntry);
        });

        calendarContainer.appendChild(dayCell);
    }

    // --- Empty Cells (Next Month) ---
    const totalCells = adjustedStartDay + daysInMonth;
    const remainingCells = (totalCells % 7 === 0) ? 0 : 7 - (totalCells % 7);
    for (let i = 0; i < remainingCells; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.classList.add('border', 'border-[#C2C2C2]', 'h-40', 'relative', 'bg-gray-50', 'opacity-50');
        calendarContainer.appendChild(emptyCell);
    }
}

// Add this function to check if there are issues with the API response
async function fetchPlannerEntries(monthIndex, year) {
    if (!guestId) {
        console.error("Guest ID is missing. Cannot fetch planner data.");
        calendar.innerHTML = '<div class="col-span-7 text-center py-10 text-red-500">Error: User session not found.</div>';
        return;
    }
    
    calendar.innerHTML = '<div class="col-span-7 text-center py-10 text-gray-500">Loading calendar...</div>';
    
    try {
        console.log(`Fetching data for month ${monthIndex + 1}, year ${year}`);
        
        // Construct URL carefully
        const response = await fetch(`/planner-data?month=${monthIndex + 1}&year=${year}`);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error("Response not OK:", response.status, errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Try to parse JSON response
        try {
            const data = await response.json();
            console.log("Fetched planner data:", data);
            
            // Check if data is empty object
            if (Object.keys(data).length === 0) {
                console.warn("Received empty data object from API");
            }
            
            plannerEntries = data;
            renderCalendar(monthIndex, year);
        } catch (parseError) {
            console.error("Failed to parse JSON response:", parseError);
            const responseText = await response.text();
            console.log("Raw response:", responseText);
            throw new Error("Invalid JSON response from server");
        }
    } catch (error) {
        console.error("Failed to fetch planner entries:", error);
        calendar.innerHTML = `<div class="col-span-7 text-center py-10 text-red-500">Failed to load calendar data: ${error.message}</div>`;
    }
}
        function updateDisplay() {
            const display = document.getElementById("month-year-display");
            if (!display) return; // Guard clause
            display.classList.remove('opacity-100');
            display.classList.add('opacity-0');

            setTimeout(() => {
                display.innerText = `${months[currentMonth]} ${currentYear}`;
                display.classList.remove('opacity-0');
                display.classList.add('opacity-100');
                fetchPlannerEntries(currentMonth, currentYear); // Fetch data *after* updating display text
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

        // --- Modal Functions ---
        function openModal(dateStr, plannerEntry = null) {
            const modal = document.getElementById('dayModal');
            const dateTitle = document.getElementById('modalDateTitle');
            const dateInput = document.getElementById('modalDateInput');
            const guestIdInput = document.getElementById('modalGuestIdInput');
            const imageContainer = document.getElementById('modalOutfitImages');
             const modalForm = document.getElementById('modalForm'); // Get form for resetting
            const outfitNameInput = modalForm.querySelector('input[name="name"]');
            const newImagesInput = document.getElementById('modalImagesInput');

             // Reset form elements before populating
             modalForm.reset(); // Resets file input, text inputs, selects etc.
             imageContainer.innerHTML = ''; // Clear previous dynamic images

            dateTitle.innerText = `Outfit for ${dateStr}`;
            dateInput.value = dateStr;
            guestIdInput.value = guestId; // Ensure guestId is set

            const clothes = plannerEntry?.outfit?.clothes || [];
            const occasion = plannerEntry?.occasion || '';
            outfitNameInput.value = plannerEntry?.outfit?.name || ''; // Set outfit name AFTER reset


            if (clothes.length > 0) {
                clothes.forEach((cloth, index) => {
                    const wrapper = document.createElement('div');
                    wrapper.classList.add('border', 'p-3', 'rounded-lg', 'w-full', 'bg-gray-50', 'mb-3', 'shadow-sm');
                    const imageUrl = cloth.image_url && cloth.image_url.startsWith('http')
                        ? cloth.image_url
                        : (assetBase.endsWith('/') ? assetBase : assetBase + '/') + (cloth.image_url || '');

                    wrapper.innerHTML = `
                        <div class="flex gap-4 items-start w-full">
                            <img src="${imageUrl}" class="w-24 h-24 object-cover rounded-md border" alt="${cloth.name || 'Cloth image'}" onerror="this.style.display='none'"/>
                            <div class="flex-1 space-y-2">
                                <input type="hidden" name="cloth_ids[${index}]" value="${cloth.id}" />
                                <div>
                                    <label class="block text-xs font-medium text-gray-600" for="cloth_name_${index}">Cloth Name:</label>
                                    <input type="text" id="cloth_name_${index}" name="cloth_names[${index}]" value="${cloth.name || ''}" class="w-full p-1.5 border border-gray-300 rounded-md text-sm focus:ring-black/50 focus:border-black/50" />
                                </div>
                                ${index === 0 ? `
                                        <div>
                                             <label class="block text-xs font-medium text-gray-600 mt-1" for="occasion_${index}">Day's Occasion:</label>
                                             <input type="text" id="occasion_${index}" name="occasions[0]" value="${occasion}" placeholder="e.g., Work Meeting, Casual Dinner" class="w-full p-1.5 border border-gray-300 rounded-md text-sm focus:ring-black/50 focus:border-black/50" />
                                        </div>
                                        ` : '<input type="hidden" name="occasions['+index+']" value="" />'}
                                {{-- Button to remove existing item (Requires backend logic) --}}
                                {{-- <button type="button" onclick="removeClothItem(this, ${cloth.id})" class="text-red-500 text-xs hover:text-red-700 mt-1">Remove</button> --}}
                            </div>
                        </div>
                    `;
                    imageContainer.appendChild(wrapper);
                });
            } else {
                 // Show placeholder if no clothes, and provide occasion input
                 imageContainer.innerHTML = `
                     <p class="text-gray-500 text-center mb-3">No outfit assigned yet. Add images below.</p>
                     <div>
                         <label class="block text-sm font-medium text-gray-700 mb-1" for="occasion_new">Day's Occasion:</label>
                         <input type="text" id="occasion_new" name="occasions[0]" value="${occasion}" placeholder="e.g., Work Meeting, Casual Dinner" class="w-full p-2 border border-gray-300 rounded-md focus:ring-black/50 focus:border-black/50" />
                     </div>
                 `;
            }

            modal.classList.remove('hidden');
            modal.scrollTop = 0; // Scroll modal to top
        }

        function closeModal() {
            const modal = document.getElementById('dayModal');
            if (modal) {
                modal.classList.add('hidden');
            }
             // Optionally reset form if not done in openModal
             // const modalForm = document.getElementById('modalForm');
             // if (modalForm) modalForm.reset();
             // const imageContainer = document.getElementById('modalOutfitImages');
             // if (imageContainer) imageContainer.innerHTML = '';
        }

        async function submitOutfit(event) {
            event.preventDefault();
            const form = document.getElementById('modalForm');
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');

             // Ensure CSRF token exists before appending
             const csrfTokenInput = form.querySelector('input[name="_token"]');
             if (!csrfTokenInput) {
                 console.error("CSRF token input not found in form!");
                 alert("Error: Security token missing. Cannot save.");
                 return;
             }
            // formData already includes CSRF from @csrf directive

            submitButton.disabled = true;
            submitButton.textContent = 'Saving...';

            try {
                const response = await fetch('/planner/save', { // Make sure this route is defined in web.php or api.php
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json', // Expect JSON response
                        // CSRF token is often sent via header OR form data - check Laravel docs/setup
                         'X-CSRF-TOKEN': csrfTokenInput.value // Send via header as well (common practice)
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // alert('Outfit saved successfully!'); // Use less intrusive confirmation later maybe
                    closeModal();
                    if (result.plannerEntry) {
                        const updatedDate = result.plannerEntry.date;
                        if (updatedDate) {
                            plannerEntries[updatedDate] = result.plannerEntry;
                            renderCalendar(currentMonth, currentYear); // Re-render the calendar view
                        } else {
                            console.warn("Save successful but date missing in response, doing full refresh.");
                            fetchPlannerEntries(currentMonth, currentYear); // Fallback
                        }
                    } else {
                         console.warn("Save successful but plannerEntry missing in response, doing full refresh.");
                         fetchPlannerEntries(currentMonth, currentYear); // Fallback
                    }
                } else {
                     console.error("Save failed:", result);
                     let errorMessage = 'Failed to save outfit.';
                     if (result.message) { // Standard Laravel error message key
                         errorMessage = result.message;
                     }
                     if (result.errors) { // Standard Laravel validation error key
                         errorMessage += '\n' + Object.values(result.errors).flat().join('\n');
                     } else if (result.error && typeof result.error === 'string') { // Custom error key
                         errorMessage += '\n' + result.error;
                     }
                     alert(errorMessage);
                }
            } catch (error) {
                console.error('Error submitting outfit:', error);
                alert('An error occurred while saving. Please check your connection and try again.');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Save Outfit';
            }
        }

        // Close modal on Escape key
        window.addEventListener('keydown', (e) => {
            const modal = document.getElementById('dayModal');
            if (modal && e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        // Initial load
        if (guestId) {
             console.log("Starting initial display update with guestId:", guestId);
             updateDisplay();
        } else {
            console.error("Guest ID missing on initial load.");
             calendar.innerHTML = '<div class="col-span-7 text-center py-10 text-red-500">Error: User session not found. Cannot load planner.</div>';
        }

        // Optional: Function to handle removing an existing cloth item (requires backend implementation)
        function removeClothItem(buttonElement, clothId) {
            if (confirm('Are you sure you want to remove this item from the outfit? This cannot be undone immediately.')) {
                 // 1. Add a hidden input to signal removal to the backend
                 const form = document.getElementById('modalForm');
                 const hiddenInput = document.createElement('input');
                 hiddenInput.type = 'hidden';
                 hiddenInput.name = 'removed_cloth_ids[]';
                 hiddenInput.value = clothId;
                 form.appendChild(hiddenInput);

                 // 2. Visually remove the item from the modal
                 buttonElement.closest('.border.p-3').remove(); // Use remove() for modern browsers
                 alert('Item marked for removal. Save the outfit to confirm changes.');
            }
        }
    </script>
@endsection