<!DOCTYPE html>
<html lang="en">
<head>    
    <title>Login - Younique</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="flex w-[1000px] h-[550px] bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Left Section -->
        <div class="w-1/2 bg-[#F4EEE4] p-8 flex flex-col justify-top">
            <h2 class="text-2xl font-bold text-[#5D3A00] mt-3 mb-4">Discover Your<br><span class="text-4xl">Unique Style!</span></h2>
            <p class="text-[#333] mt-3">Mix and match your wardrobe effortlessly, express your personality, and shine every day with Younique!</p>
        </div>

        <!-- Right Section -->
        <div class="w-1/2 p-8 flex flex-col justify-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-3">Register</h1>
            <p class="text-sm text-gray-600 mb-6">Welcome, register to unlock more exciting features!</p>

            <form action="{{ route('register.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-semibold text-gray-700">Username</label>
                    <input type="text" id="name" name="name" class="w-full px-4 py-2 rounded-md bg-gray-200 focus:outline-none focus:ring-2 focus:ring-brown-500">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 rounded-md bg-gray-200 focus:outline-none focus:ring-2 focus:ring-brown-500">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
                    <input type="email" id="email" name="email" class="w-full px-4 py-2 rounded-md bg-gray-200 focus:outline-none focus:ring-2 focus:ring-brown-500">
                </div>    
                
                <input type="hidden" name="role" value="free">

                <button type="submit" class="w-full bg-[#5D3A00] text-white py-2 rounded-md font-semibold hover:bg-[#4a2f00] transition">REGISTER</button>
            </form>
        </div>
    </div>

</body>
</html>
