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

            </div>
        </div>
    </div>
    <!-- Modal -->
    <div id="dayModal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden overflow-y-auto px-4 py-8">
        <div class="bg-white w-full max-w-2xl p-6 rounded-2xl shadow-xl relative overflow-y-auto max-h-[90vh]">
            <button onclick="closeModal()"
                class="absolute top-3 right-3 text-gray-500 hover:text-black text-2xl">&times;</button>

            <h2 class="text-3xl font-semibold mb-6 text-center text-gray-800" id="modalDateTitle">Outfit for </h2>

            <div id="modalOutfitImages" class="flex flex-col gap-4 mb-6"></div>

            <form id="modalForm" onsubmit="submitOutfit(event)" class="space-y-4">
                <input type="hidden" name="date" id="modalDateInput">
                <input type="hidden" name="guest_id" id="modalGuestIdInput" value="{{ $guestId }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Outfit Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-black/30" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Add Outfit Images</label>
                    <input type="file" name="images[]" id="modalImagesInput" multiple
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800" />
                </div>

                <div class="flex justify-end">
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
    const guestId = @json($guestId);
    const assetBase = "{{ asset('') }}"; // Correctly gets the base URL
    const calendar = document.getElementById('calendar');
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']; // English names
    
    const currentDate = new Date();
    let currentYear = currentDate.getFullYear();
    let currentMonth = currentDate.getMonth(); // 0-indexed
    
    async function fetchPlannerEntries(monthIndex, year) {
        // Show loading state maybe
        try {
            const response = await fetch(`/planner-data?month=${monthIndex + 1}&year=${year}`); // monthIndex+1 for backend
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            console.log("Fetched Data:", data); // Debugging
            plannerEntries = data; // Assign the object keyed by date
            renderCalendar(monthIndex, year);
        } catch (error) {
            console.error("Failed to fetch planner entries:", error);
            calendar.innerHTML = '<div class="col-span-7 text-center py-10 text-red-500">Failed to load calendar data. Please try again.</div>';
            // Optionally, show an alert or more user-friendly message
            // alert('Failed to load calendar data.');
        } finally {
            // Hide loading state maybe
        }
    }
    
    
    function renderCalendar(monthIndex, year) {
        const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();
        const firstDayOfMonth = new Date(year, monthIndex, 1);
         // Correctly get the day of the week (0=Sun, 1=Mon, ..., 6=Sat)
        const startDayOfWeek = firstDayOfMonth.getDay();
        const calendarContainer = document.getElementById('calendar');
        calendarContainer.innerHTML = '';
    
        // Adjust startDayOfWeek to match Monday-first grid (0=Mon, 1=Tue, ..., 6=Sun)
        const adjustedStartDay = (startDayOfWeek === 0) ? 6 : startDayOfWeek - 1;
    
        // --- Create Empty Cells for previous month's days ---
        for (let i = 0; i < adjustedStartDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.classList.add('border', 'border-[#C2C2C2]', 'h-40', 'relative', 'bg-gray-50', 'opacity-50'); // Style empty cells
            calendarContainer.appendChild(emptyCell);
        }
    
        // --- Create Cells for each day of the current month ---
        for (let day = 1; day <= daysInMonth; day++) {
            const dayCell = document.createElement('div');
            dayCell.classList.add('border', 'border-[#C2C2C2]', 'h-40', 'relative', 'text-black', 'p-1', // Reduced padding slightly
                'cursor-pointer', 'hover:bg-amber-50', 'transition-colors', 'flex', 'flex-col'); // Added flex layout
    
            // Date String (YYYY-MM-DD) - IMPORTANT for matching keys
            const dateStr = `${year}-${String(monthIndex + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    
            // Day Number Display
            const dayNumber = document.createElement('div');
            dayNumber.classList.add('text-xs', 'font-semibold', 'self-start', 'mb-1'); // Adjusted styling
            dayNumber.innerText = day;
            dayCell.appendChild(dayNumber);
    
            // Find Planner Entry for this date
            const plannerEntry = plannerEntries[dateStr]; // Access using date string key
    
            // Outfit Images Display Area
            const imageGroup = document.createElement('div');
            imageGroup.classList.add('flex', 'justify-center', 'items-center', 'gap-1', 'mt-1', 'flex-wrap', 'overflow-hidden', 'flex-grow'); // Center images, allow wrap, fill space
    
             if (plannerEntry) {
                // Display Occasion
                 if (plannerEntry.occasion) {
                    const occasionText = document.createElement('div');
                    occasionText.classList.add('text-xs', 'text-center', 'text-gray-600', 'w-full', 'truncate'); // Show occasion briefly
                    occasionText.innerText = plannerEntry.occasion;
                    dayCell.insertBefore(occasionText, imageGroup); // Add occasion above images
                 }
    
                 // Display Outfit Images
                 if (plannerEntry.outfit && plannerEntry.outfit.clothes && plannerEntry.outfit.clothes.length > 0) {
                    plannerEntry.outfit.clothes.slice(0, 2).forEach(cloth => { // Show max 2 images for space
                        if (cloth.image_url) {
                            const img = document.createElement('img');
                            // Prepend assetBase only if image_url doesn't start with http/https
                            img.src = cloth.image_url.startsWith('http') ? cloth.image_url : assetBase + cloth.image_url;
                            img.alt = cloth.name || 'Outfit item';
                            img.classList.add('w-10', 'h-10', 'object-cover', 'rounded-sm'); // Smaller images
                            img.onerror = () => { img.style.display='none'; }; // Hide if image fails to load
                            imageGroup.appendChild(img);
                        }
                    });
                     if (plannerEntry.outfit.clothes.length > 2) {
                        const moreIndicator = document.createElement('span');
                        moreIndicator.classList.add('text-xs', 'text-gray-500');
                        moreIndicator.innerText = `+${plannerEntry.outfit.clothes.length - 2}`;
                        imageGroup.appendChild(moreIndicator);
                     }
                } else {
                     // Maybe indicate no outfit planned?
                     imageGroup.innerHTML = `<span class="text-xs text-gray-400 italic">No outfit</span>`;
                 }
             } else {
                 // No entry for this date (shouldn't happen if generation works, but good fallback)
                  imageGroup.innerHTML = `<span class="text-xs text-gray-400 italic"></span>`; // Empty or placeholder
             }
    
            dayCell.appendChild(imageGroup);
    
            // Click Event to Open Modal
            dayCell.addEventListener('click', () => {
                openModal(dateStr, plannerEntry); // Pass the whole entry object
            });
    
            calendarContainer.appendChild(dayCell);
        }
    
        // Calculate remaining cells needed for the grid (total cells = multiple of 7)
        const totalCells = adjustedStartDay + daysInMonth;
        const remainingCells = (totalCells % 7 === 0) ? 0 : 7 - (totalCells % 7);
    
        // --- Create Empty Cells for next month's days ---
        for (let i = 0; i < remainingCells; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.classList.add('border', 'border-[#C2C2C2]', 'h-40', 'relative', 'bg-gray-50', 'opacity-50');
            calendarContainer.appendChild(emptyCell);
        }
    }
    
    
    function updateDisplay() {
        // Add fade effect for transition
        const display = document.getElementById("month-year-display");
        display.classList.remove('opacity-100');
        display.classList.add('opacity-0');
    
        setTimeout(() => {
            display.innerText = `${months[currentMonth]} ${currentYear}`;
            display.classList.remove('opacity-0');
            display.classList.add('opacity-100');
            fetchPlannerEntries(currentMonth, currentYear); // Fetch data *after* updating display text
        }, 150); // Short delay to allow fade out before changing text and fading in
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
    
    function openModal(dateStr, plannerEntry = null) { // Accept the full entry
        const modal = document.getElementById('dayModal');
        const dateTitle = document.getElementById('modalDateTitle');
        const dateInput = document.getElementById('modalDateInput');
        const guestIdInput = document.getElementById('modalGuestIdInput');
        const imageContainer = document.getElementById('modalOutfitImages');
        const outfitNameInput = document.querySelector('#modalForm input[name="name"]'); // Get outfit name input
         const newImagesInput = document.getElementById('modalImagesInput'); // Input for NEW images
    
        dateTitle.innerText = `Outfit for ${dateStr}`;
        dateInput.value = dateStr;
        guestIdInput.value = guestId; // Set guest ID
        imageContainer.innerHTML = ''; // Clear previous content
        outfitNameInput.value = ''; // Clear outfit name
         newImagesInput.value = ''; // Clear file input for new images
    
        const clothes = plannerEntry?.outfit?.clothes || [];
        const occasion = plannerEntry?.occasion || '';
         outfitNameInput.value = plannerEntry?.outfit?.name || ''; // Set outfit name if exists
    
    
        if (clothes.length > 0) {
            clothes.forEach((cloth, index) => {
                const wrapper = document.createElement('div');
                wrapper.classList.add('border', 'p-3', 'rounded-lg', 'w-full', 'bg-gray-50', 'mb-3', 'shadow-sm');
    
                const imageUrl = cloth.image_url.startsWith('http') ? cloth.image_url : assetBase + cloth.image_url;
    
                // Simplified structure for existing clothes
                 wrapper.innerHTML = `
                    <div class="flex gap-4 items-start w-full">
                        <img src="${imageUrl}" class="w-24 h-24 object-cover rounded-md border" alt="${cloth.name || 'Cloth image'}" onerror="this.style.display='none'"/>
                        <div class="flex-1 space-y-2">
                            <input type="hidden" name="cloth_ids[${index}]" value="${cloth.id}" />
    
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Cloth Name:</label>
                                <input type="text" name="cloth_names[${index}]" value="${cloth.name || ''}" class="w-full p-1.5 border border-gray-300 rounded-md text-sm focus:ring-black/50 focus:border-black/50" />
                            </div>
    
                            ${index === 0 ? `
                            <div>
                                 <label class="block text-xs font-medium text-gray-600 mt-1">Day's Occasion:</label>
                                 <input type="text" name="occasions[0]" value="${occasion}" placeholder="e.g., Work Meeting, Casual Dinner" class="w-full p-1.5 border border-gray-300 rounded-md text-sm focus:ring-black/50 focus:border-black/50" />
                            </div>
                            ` : '<input type="hidden" name="occasions['+index+']" value="" />'}
    
                             <!-- Optional: Allow changing image for *existing* items -->
                             <!--
                             <div class="mt-2">
                                <label class="block text-xs font-medium text-gray-500">Change Image:</label>
                                <input type="file" name="cloth_images[${index}]" class="w-full text-xs file:mr-2 file:py-1 file:px-2 file:border-0 file:text-xs file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300 rounded" />
                             </div>
                             -->
                              <!-- Add button to remove existing item -->
                             <!-- <button type="button" onclick="removeClothItem(this, ${cloth.id})" class="text-red-500 text-xs hover:text-red-700 mt-1">Remove</button> -->
                        </div>
                    </div>
                `;
                imageContainer.appendChild(wrapper);
            });
        } else {
             // Show placeholder if no clothes
             imageContainer.innerHTML = '<p class="text-gray-500 text-center">No outfit assigned yet. Add images below.</p>';
              // Also set the first occasion input if the container is empty
              const occasionWrapper = document.createElement('div');
              occasionWrapper.innerHTML = `
                 <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Day's Occasion:</label>
                     <input type="text" name="occasions[0]" value="${occasion}" placeholder="e.g., Work Meeting, Casual Dinner" class="w-full p-2 border border-gray-300 rounded-md focus:ring-black/50 focus:border-black/50" />
                 </div>
              `;
              imageContainer.appendChild(occasionWrapper);
    
        }
    
        modal.classList.remove('hidden');
    }
    
    
    function closeModal() {
        document.getElementById('dayModal').classList.add('hidden');
        document.getElementById('modalForm').reset(); // Reset form fields
        document.getElementById('modalOutfitImages').innerHTML = ''; // Clear dynamic content
    }
    
    async function submitOutfit(event) {
        event.preventDefault();
        const form = document.getElementById('modalForm');
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Saving...';
    
        // Append CSRF token if using standard Laravel web routes
        formData.append('_token', '{{ csrf_token() }}');
    
        try {
            const response = await fetch('/planner/save', {
                method: 'POST',
                body: formData,
                headers: {
                     // Important: Don't set Content-Type when using FormData with files,
                     // the browser will set it correctly with the boundary.
                     'Accept': 'application/json', // Expect JSON response
                     'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token if needed
                }
            });
    
            const result = await response.json();
    
            if (response.ok && result.success) {
                alert('Outfit saved successfully!');
                closeModal();
                // --- Option 1: Update local data and re-render (faster) ---
                if (result.plannerEntry) {
                    const updatedDate = result.plannerEntry.date; // Assuming date is YYYY-MM-DD
                     plannerEntries[updatedDate] = result.plannerEntry; // Update the entry in our local store
                     renderCalendar(currentMonth, currentYear); // Re-render the calendar view
                } else {
                    // Fallback if entry isn't returned: Full refresh
                    fetchPlannerEntries(currentMonth, currentYear);
                }
                 // --- Option 2: Just refetch everything (simpler) ---
                 // fetchPlannerEntries(currentMonth, currentYear);
    
            } else {
                 console.error("Save failed:", result);
                 let errorMessage = 'Failed to save outfit.';
                 if (result.error) {
                    if (typeof result.error === 'string') {
                        errorMessage += '\n' + result.error;
                    } else if (typeof result.error === 'object') {
                        // Extract validation errors
                        errorMessage += '\n' + Object.values(result.error).flat().join('\n');
                    }
                 }
                 alert(errorMessage);
            }
        } catch (error) {
            console.error('Error submitting outfit:', error);
            alert('An error occurred while saving. Please try again.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Save Outfit';
        }
    }
    
    // Close modal on Escape key
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !document.getElementById('dayModal').classList.contains('hidden')) {
            closeModal();
        }
    });
    
    // Initial load
    updateDisplay();
    
    // Optional: Add removeClothItem function if you implement the remove button
    function removeClothItem(buttonElement, clothId) {
        if (confirm('Are you sure you want to remove this item from the outfit?')) {
            // Add logic here:
            // 1. Maybe add a hidden input `removed_cloth_ids[]` with the clothId value.
            // 2. Hide the parent wrapper element.
            buttonElement.closest('.border.p-3').style.display = 'none';
            // Backend 'save' method needs to handle detaching these IDs from the outfit.
        }
    }

    console.log(guestId)
    
        </script>
@endsection
