<nav class=" sticky top-0 left-0 w-full flex justify-center items-center z-[9999] bg-[#FFEDD7] shadow-2xl">
    <div class="container xl:max-w-screen-xl">
        <div class="flex items-center justify-start relative w-full">
            <div class="px-4 h-20 flex items-center w-1/3">
                <a href="/" class="flex items-center">
                    <img src="{{ asset('images/logo.svg') }}" class="h-10 sm:h-12" alt="Younique Logo" />
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-10 px-4 w-1/3">
                <a href="/"
                class="relative text-gray-700 hover:text-gray-900 font-bold after:content-[''] after:absolute after:left-0 after:bottom-0 after:h-[2px] after:bg-gray-700 after:w-0 after:transition-all after:duration-500 hover:after:w-full {{ request()->routeIs('home.*') ? 'after:w-full' : '' }}">
                Home
             </a>  
                <a href="/planner"
                class="relative text-gray-700 hover:text-gray-900 font-bold after:content-[''] after:absolute after:left-0 after:bottom-0 after:h-[2px] after:bg-gray-700 after:w-0 after:transition-all after:duration-500 hover:after:w-full {{ request()->routeIs('planner.*') ? 'after:w-full' : '' }}">
                Planner
             </a>             
                <a href="/e-wardrobe" class="text-gray-700 hover:text-gray-900 font-bold">E-wardrobe</a>
                <a href="/analyzer" class="text-gray-700 hover:text-gray-900 font-bold">Analyzer</a>
            </div>

            <div class="w-1/3 flex justify-end items-center">                
                <svg width="62" height="60" viewBox="0 0 62 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="31" cy="30" rx="31" ry="30" fill="#D9D9D9"/>
                    <path d="M45.1428 47.6429V43.7223C45.1428 41.6426 44.3602 39.6482 42.9671 38.1776C41.5739 36.7071 39.6845 35.881 37.7143 35.881H22.8571C20.887 35.881 18.9975 36.7071 17.6043 38.1776C16.2112 39.6482 15.4286 41.6426 15.4286 43.7223V47.6429" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M30.2857 28.0397C34.3884 28.0397 37.7143 24.5291 37.7143 20.1984C37.7143 15.8678 34.3884 12.3572 30.2857 12.3572C26.183 12.3572 22.8571 15.8678 22.8571 20.1984C22.8571 24.5291 26.183 28.0397 30.2857 28.0397Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>                    
            </div>

            <!-- Mobile Menu Button -->
            <div class="flex items-center px-4 md:hidden">
                <button id="hamburger" name="hamburger" type="button" class="block absolute right-4 group">
                    <span class="hamburger-line transition duration-300 ease-in-out origin-top-left"></span>
                    <span class="hamburger-line transition duration-300 ease-in-out"></span>
                    <span class="hamburger-line transition duration-300 ease-in-out origin-bottom-left"></span>
                </button>
            </div>

            <!-- Mobile Menu (hidden by default) -->
            <div id="mobile-menu" class="hidden absolute top-20 right-4 bg-white rounded-lg shadow-lg p-4 space-y-4 md:hidden">
                <a href="/" class="block text-gray-700 hover:text-gray-900 font-medium {{ request()->routeIs('home') ? 'underline' : '' }}">Home</a>
                <a href="/planner" class="block text-gray-700 hover:text-gray-900 font-medium {{ request()->routeIs('planner') ? 'underline' : '' }} ">Planner</a>
                <a href="/e-wardrobe" class="block text-gray-700 hover:text-gray-900 font-medium">E-wardrobe</a>
                <a href="/analyzer" class="block text-gray-700 hover:text-gray-900 font-medium">Analyzer</a>
            </div>
        </div>
    </div>
</nav>

<script>
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobile-menu');

    hamburger.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
</script>
