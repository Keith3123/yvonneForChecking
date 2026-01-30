<footer class="bg-white text-black pt-10 pb-6">

    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-10 animate-fade-up">

        <!-- LOGO + ABOUT -->
        <div>
            <div class="flex items-center space-x-4">
                <img src="{{ asset('images/logo.png') }}" 
                     class="h-14 w-14 rounded-full object-cover border border-pink-400 shadow-md
                            transition-transform duration-300 hover:scale-105">
                <h2 class="text-xl font-semibold text-pink-400 leading-tight">
                    Yvonne's Cakes <br><span>& Pastries</span>
                </h2>
            </div>

            <p class="mt-4 text-sm leading-relaxed text-gray-700">
                Crafting delicious memories since day one.  
                We specialize in custom cakes, pastries, and food trays  
                for all your celebrations.
            </p>

            <!-- Social Icons -->
            <div class="flex space-x-4 mt-4">
                <a href="https://www.facebook.com/erika.yvonne1008" 
                   class="p-2 rounded-full bg-pink-400
                          transition-all duration-300
                          hover:-translate-y-1 hover:scale-110 hover:shadow-lg">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22 12.07C22 6.54 17.52 2 12 2S2 6.54 2 12.07c0 5.03 3.66 9.2 
                        8.44 9.93v-7.03H7.9v-2.9h2.54V9.41c0-2.5 1.49-3.89 3.77-3.89 
                        1.09 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 
                        2.9h-2.34V22C18.34 21.27 22 17.1 22 12.07z"/>
                    </svg>
                </a>

                <a href="https://instagram.com/erika_yumang" 
                   class="p-2 rounded-full bg-pink-400
                          transition-all duration-300
                          hover:-translate-y-1 hover:scale-110 hover:shadow-lg">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 2C4.243 2 2 4.243 2 7v10c0 2.757 2.243 5 5 5h10c2.757 
                        0 5-2.243 5-5V7c0-2.757-2.243-5-5-5H7zm0 2h10c1.654 0 3 1.346 
                        3 3v10c0 1.654-1.346 3-3 3H7c-1.654 0-3-1.346-3-3V7c0-1.654 
                        1.346-3 3-3zm5 3.5A4.5 4.5 0 1 0 16.5 12 4.505 4.505 0 0 0 
                        12 7.5zm0 2A2.5 2.5 0 1 1 9.5 12 2.503 2.503 0 0 1 
                        12 9.5zM18.8 6.2a.8.8 0 1 1-.8-.8.8.8 0 0 1 .8.8z"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <!-- QUICK LINKS -->
            <div>
                <h3 class="text-lg font-semibold text-pink-400 mb-3">
                    Quick Links
                </h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="#" class="inline-block transition-all duration-300 hover:translate-x-1 hover:text-pink-400">
                            Order Now
                        </a>
                    </li>
                    <li>
                        <a href="#" class="inline-block transition-all duration-300 hover:translate-x-1 hover:text-pink-400">
                            Our Menu
                        </a>
                    </li>
                    <li>
                        <a href="#" class="inline-block transition-all duration-300 hover:translate-x-1 hover:text-pink-400">
                            Paluwagan
                        </a>
                    </li>
                    <li>
                        <a href="#" class="inline-block transition-all duration-300 hover:translate-x-1 hover:text-pink-400">
                            My Order
                        </a>
                    </li>
                    <li>
                        <a href="#" class="inline-block transition-all duration-300 hover:translate-x-1 hover:text-pink-400">
                            Catalog
                        </a>
                    </li>
                </ul>
            </div>

            <!-- CONTACT -->
            <div>
                <h3 class="text-lg font-semibold text-pink-400 mb-3">
                    Contact Us
                </h3>
                <ul class="space-y-3 text-sm text-gray-700">
                    <li class="flex space-x-3 items-start">
                        <span class="text-pink-400">📍</span>
                        <span>123 Bakery Street, Barangay San Juan, Manila City</span>
                    </li>
                    <li class="flex space-x-3 items-center">
                        <span class="text-pink-400">📞</span>
                        <span>0907 421 7589</span>
                    </li>
                    <li class="flex space-x-3 items-center">
                        <span class="text-pink-400">✉️</span>
                        <span>erika_yvonne@yahoo.com.ph</span>
                    </li>
                </ul>
            </div>

        </div>

    </div>

    <div class="border-t border-gray-200 mt-10 pt-6">
        <div class="max-w-7xl mx-auto px-6 text-center text-sm text-gray-500">
            © {{ date('Y') }} Yvonne's Cakes, Pastries & Food Trays.  
            Made with ❤️ for our valued customers.
        </div>
    </div>

</footer>
