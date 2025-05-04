@extends('components.layout')

@section('content')
<div class="flex flex-col min-h-screen w-full px-12 py-12 bg-[#F5F0E6]">
    <div class="mb-10 text-center">
        <h1 class="text-4xl font-bold text-[#2E2E2E]">Planner Calendar</h1>
        <p class="text-xl text-[#6B6B6B] mt-2">Organize your outfits for every day of the month.</p>
    </div>
    <div class="flex items-center gap-2 mb-8">
        <button onclick="prevMonth5)" class="text-gray-600 hover:text-black">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path d="M15 18L9 12L15 6" stroke="black" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </button>

        <div id="month-year-display" class="text-3xl font-medium transition-opacity duration-300 opacity-100">
                       
        </div>

        <button onclick="nextMonth()" class="text-gray-600 hover:text-black">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
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
            <!-- Dates will be rendered here -->
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    const calendar = document.getElementById('calendar');
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    const currentDate = new Date();
    let currentYear = currentDate.getFullYear();
    let currentMonth = currentDate.getMonth();

    function renderCalendar(monthIndex, year) {
        const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();
        const startDay = new Date(year, monthIndex, 1).getDay();
        const calendarContainer = document.getElementById('calendar');
        calendarContainer.innerHTML = '';

        const adjustedStartDay = (startDay === 0) ? 6 : startDay - 1;

        for (let i = 0; i < adjustedStartDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.classList.add('border','border-[#C2C2C2]', 'h-40', 'relative', 'text-black');
            calendarContainer.appendChild(emptyCell);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dayCell = document.createElement('div');
            dayCell.classList.add('border','border-[#C2C2C2]', 'h-40', 'relative', 'text-black', 'p-2');

            const dayNumber = document.createElement('div');
            dayNumber.classList.add('text-sm', 'absolute', 'top-2', 'left-2', 'text-black');
            dayNumber.innerText = day;
            dayCell.appendChild(dayNumber);

            if (monthIndex === 0 && (day === 1 || day === 18)) {
                const eventBox = document.createElement('div');
                eventBox.classList.add('bg-[#F2F4FC]', 'text-[#434DB2]', 'rounded', 'p-1', 'text-sm', 'mt-6', 'cursor-pointer');
                eventBox.innerHTML = 'Webinar Nasional<br/>09.30 - 17.00';
                dayCell.appendChild(eventBox);
            }

            calendarContainer.appendChild(dayCell);
        }
    }

    function updateDisplay() {
        document.getElementById("month-year-display").innerText = `${months[currentMonth]} ${currentYear}`;
        renderCalendar(currentMonth, currentYear);
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

    updateDisplay();
</script>
@endsection
