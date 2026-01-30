<section class="bg-gradient-to-b from-pink-500 to-pink-400 text-white py-14">
  <div class="max-w-6xl mx-auto px-6 text-center animate-fade-up">
    
    <h2 class="text-3xl md:text-4xl font-bold mb-3">
        Ready to Place Your Order?
    </h2>

    <p class="mb-6 text-sm md:text-base opacity-90">
      Join thousands of satisfied customers who trust Yvonne's for their celebrations
    </p>

    <div class="flex justify-center mb-4">
      <a href="{{ route(session('logged_in_user') ? 'catalog' : 'register') }}"
         class="bg-white text-pink-600 font-semibold px-6 py-3 rounded-full shadow
                transition-all duration-300
                hover:-translate-y-1 hover:scale-105 hover:shadow-2xl">
        Get Started Now
      </a>
    </div>

    <div class="text-sm opacity-95">
      <span class="inline-block mr-3">Self-Service Kiosk</span>
      <span class="mx-2">•</span>
      <span class="inline-block mr-3">Easy Ordering</span>
      <span class="mx-2">•</span>
      <span class="inline-block">Track Your Orders</span>
    </div>

  </div>
</section>
