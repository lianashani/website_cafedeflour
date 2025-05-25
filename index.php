<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Café de Flour - Where Warmth Meets Flavor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #2c1810;
      --secondary-color: #8b4513;
      --accent-color: #d4a574;
      --accent-light: #e8d5b7;
      --text-light: #ffffff;
      --text-dark: #1e293b;
      --shadow-heavy: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      --gradient-overlay: linear-gradient(135deg, rgba(44, 24, 16, 0.7) 0%, rgba(139, 69, 19, 0.5) 50%, rgba(0, 0, 0, 0.3) 100%);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      font-family: 'Inter', sans-serif;
      overflow-x: hidden;
    }

    /* Loading Screen */
    .loading-screen {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      transition: opacity 0.8s ease, visibility 0.8s ease;
    }

    .loading-screen.hidden {
      opacity: 0;
      visibility: hidden;
    }

    .loading-content {
      text-align: center;
      color: white;
    }

    .loading-logo {
      font-family: 'Playfair Display', serif;
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 1rem;
      opacity: 0;
      animation: fadeInUp 1s ease 0.5s forwards;
    }

    .loading-text {
      font-size: 1.2rem;
      margin-bottom: 2rem;
      opacity: 0;
      animation: fadeInUp 1s ease 0.8s forwards;
    }

    .loading-spinner {
      width: 60px;
      height: 60px;
      border: 3px solid rgba(255, 255, 255, 0.3);
      border-top: 3px solid var(--accent-color);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto;
      opacity: 0;
      animation: fadeInUp 1s ease 1.1s forwards, spin 1s linear 1.1s infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Enhanced Carousel */
    .carousel {
      height: 100vh;
      position: relative;
    }

    .carousel-item {
      height: 100vh;
      background-size: cover;
      background-position: center;
      position: relative;
      transition: transform 1.5s ease-in-out;
    }

    .carousel-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: var(--gradient-overlay);
      z-index: 1;
    }

    .carousel-item::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 30% 70%, rgba(212, 165, 116, 0.2) 0%, transparent 50%);
      z-index: 1;
    }

    /* Floating Brand Logo */
    .brand-logo {
      position: absolute;
      top: 2rem;
      left: 2rem;
      z-index: 10;
      display: flex;
      align-items: center;
      gap: 0.8rem;
      color: white;
      text-decoration: none;
      transition: all 0.3s ease;
      opacity: 0;
      animation: slideInLeft 1s ease 2s forwards;
    }

    .brand-logo:hover {
      transform: translateY(-2px);
      color: var(--accent-color);
    }

    .brand-logo i {
      font-size: 2rem;
      color: var(--accent-color);
    }

    .brand-logo span {
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
      font-weight: 700;
    }

    /* Enhanced Carousel Caption */
    .carousel-caption {
      position: absolute;
      bottom: 8%;
      left: 6%;
      text-align: left;
      z-index: 5;
      max-width: 600px;
      transform: translateY(50px);
      opacity: 0;
    }

    .carousel-item.active .carousel-caption {
      animation: slideInUp 1.2s ease 0.5s forwards;
    }

    .carousel-caption-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(212, 165, 116, 0.2);
      color: var(--accent-color);
      padding: 0.5rem 1.5rem;
      border-radius: 50px;
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      border: 1px solid rgba(212, 165, 116, 0.3);
      backdrop-filter: blur(10px);
    }

    .carousel-caption h1 {
      font-family: 'Playfair Display', serif;
      font-size: 4rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      color: #fff;
      line-height: 1.1;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
      background: linear-gradient(135deg, #ffffff 0%, var(--accent-color) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .carousel-caption p {
      font-size: 1.3rem;
      color: #e2e8f0;
      margin-bottom: 2.5rem;
      line-height: 1.6;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
      max-width: 500px;
    }

    /* Enhanced Button */
    .btn-explore {
      background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-light) 100%);
      color: var(--primary-color);
      padding: 1.2rem 3rem;
      border-radius: 50px;
      font-weight: 700;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.8rem;
      transition: all 0.4s ease;
      box-shadow: var(--shadow-heavy);
      border: 2px solid transparent;
      position: relative;
      overflow: hidden;
      font-size: 1.1rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .btn-explore::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      transition: left 0.6s;
    }

    .btn-explore:hover::before {
      left: 100%;
    }

    .btn-explore:hover {
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 15px 35px rgba(212, 165, 116, 0.4);
      color: var(--primary-color);
      border-color: rgba(255, 255, 255, 0.3);
    }

    .btn-explore i {
      transition: transform 0.3s ease;
    }

    .btn-explore:hover i {
      transform: translateX(5px);
    }

    /* Enhanced Carousel Controls */
    .carousel-control-prev,
    .carousel-control-next {
      width: 80px;
      height: 80px;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 50%;
      border: 1px solid rgba(255, 255, 255, 0.2);
      top: 50%;
      transform: translateY(-50%);
      transition: all 0.3s ease;
      opacity: 0.8;
    }

    .carousel-control-prev {
      left: 2rem;
    }

    .carousel-control-next {
      right: 2rem;
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
      background: rgba(212, 165, 116, 0.3);
      transform: translateY(-50%) scale(1.1);
      opacity: 1;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
      width: 30px;
      height: 30px;
      background-size: 30px 30px;
    }

    /* Custom Carousel Indicators */
    .carousel-indicators {
      bottom: 2rem;
      margin-bottom: 0;
    }

    .carousel-indicators [data-bs-target] {
      width: 60px;
      height: 4px;
      border-radius: 2px;
      background: rgba(255, 255, 255, 0.3);
      border: none;
      margin: 0 5px;
      transition: all 0.3s ease;
    }

    .carousel-indicators .active {
      background: var(--accent-color);
      transform: scale(1.2);
    }

    /* Floating Elements */
    .floating-elements {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 2;
    }

    .floating-element {
      position: absolute;
      opacity: 0.1;
      animation: float 8s ease-in-out infinite;
      color: var(--accent-color);
    }

    .floating-element:nth-child(1) {
      top: 20%;
      left: 15%;
      animation-delay: 0s;
      font-size: 2rem;
    }

    .floating-element:nth-child(2) {
      top: 60%;
      right: 20%;
      animation-delay: 3s;
      font-size: 1.5rem;
    }

    .floating-element:nth-child(3) {
      bottom: 30%;
      left: 25%;
      animation-delay: 6s;
      font-size: 1.8rem;
    }

    .floating-element:nth-child(4) {
      top: 40%;
      right: 10%;
      animation-delay: 2s;
      font-size: 2.2rem;
    }

    @keyframes float {
      0%, 100% { 
        transform: translateY(0px) rotate(0deg); 
        opacity: 0.1;
      }
      33% { 
        transform: translateY(-20px) rotate(120deg); 
        opacity: 0.2;
      }
      66% { 
        transform: translateY(-10px) rotate(240deg); 
        opacity: 0.15;
      }
    }

    /* Social Links */
    .social-links {
      position: absolute;
      right: 2rem;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
      display: flex;
      flex-direction: column;
      gap: 1rem;
      opacity: 0;
      animation: slideInRight 1s ease 2.5s forwards;
    }

    .social-link {
      width: 50px;
      height: 50px;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-decoration: none;
      transition: all 0.3s ease;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .social-link:hover {
      background: var(--accent-color);
      color: var(--primary-color);
      transform: scale(1.1);
    }

    /* Scroll Indicator */
    .scroll-indicator {
      position: absolute;
      bottom: 2rem;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
      color: white;
      opacity: 0;
      animation: fadeInUp 1s ease 3s forwards;
    }

    .scroll-indicator span {
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .scroll-arrow {
      width: 2px;
      height: 30px;
      background: white;
      position: relative;
      animation: scrollBounce 2s infinite;
    }

    .scroll-arrow::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: -3px;
      width: 8px;
      height: 8px;
      border-right: 2px solid white;
      border-bottom: 2px solid white;
      transform: rotate(45deg);
    }

    @keyframes scrollBounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(10px); }
    }

    /* Animations */
    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideInLeft {
      from {
        opacity: 0;
        transform: translateX(-50px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(50px) translateY(-50%);
      }
      to {
        opacity: 1;
        transform: translateX(0) translateY(-50%);
      }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .brand-logo {
        top: 1rem;
        left: 1rem;
      }

      .brand-logo span {
        font-size: 1.4rem;
      }

      .brand-logo i {
        font-size: 1.5rem;
      }

      .carousel-caption {
        bottom: 15%;
        left: 4%;
        max-width: 90%;
      }

      .carousel-caption h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
      }

      .carousel-caption p {
        font-size: 1.1rem;
        margin-bottom: 2rem;
      }

      .btn-explore {
        padding: 1rem 2rem;
        font-size: 1rem;
      }

      .carousel-control-prev,
      .carousel-control-next {
        width: 60px;
        height: 60px;
      }

      .carousel-control-prev {
        left: 1rem;
      }

      .carousel-control-next {
        right: 1rem;
      }

      .social-links {
        right: 1rem;
        gap: 0.8rem;
      }

      .social-link {
        width: 45px;
        height: 45px;
      }

      .floating-element {
        display: none;
      }

      .loading-logo {
        font-size: 2rem;
      }

      .loading-text {
        font-size: 1rem;
      }
    }

    @media (max-width: 480px) {
      .carousel-caption h1 {
        font-size: 2rem;
      }

      .carousel-caption p {
        font-size: 1rem;
      }

      .btn-explore {
        padding: 0.8rem 1.5rem;
        font-size: 0.9rem;
      }

      .carousel-caption-badge {
        font-size: 0.8rem;
        padding: 0.4rem 1rem;
      }
    }

    /* Preload images */
    .preload {
      position: absolute;
      left: -9999px;
      top: -9999px;
      visibility: hidden;
    }
  </style>
</head>
<body>
  <!-- Loading Screen -->
  <div class="loading-screen" id="loadingScreen">
    <div class="loading-content">
      <div class="loading-logo">
        <i class="fas fa-coffee"></i> Café de Flour
      </div>
      <div class="loading-text">Where Warmth Meets Flavor</div>
      <div class="loading-spinner"></div>
    </div>
  </div>

  <!-- Preload Images -->
  <div class="preload">
    <img src="https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="preload">
    <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="preload">
    <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="preload">
  </div>

  <!-- Brand Logo -->
  <a href="#" class="brand-logo">
    <i class="fas fa-coffee"></i>
    <span>Café de Flour</span>
  </a>

  <!-- Social Links -->
  <div class="social-links">
    <a href="#" class="social-link" title="Instagram">
      <i class="fab fa-instagram"></i>
    </a>
    <a href="#" class="social-link" title="Facebook">
      <i class="fab fa-facebook-f"></i>
    </a>
    <a href="#" class="social-link" title="Twitter">
      <i class="fab fa-twitter"></i>
    </a>
    <a href="#" class="social-link" title="YouTube">
      <i class="fab fa-youtube"></i>
    </a>
  </div>

  <!-- Main Carousel -->
  <div id="landingCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4000">
    <!-- Carousel Indicators -->
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#landingCarousel" data-bs-slide-to="0" class="active"></button>
      <button type="button" data-bs-target="#landingCarousel" data-bs-slide-to="1"></button>
      <button type="button" data-bs-target="#landingCarousel" data-bs-slide-to="2"></button>
    </div>

    <div class="carousel-inner">
      <!-- Slide 1 -->
      <div class="carousel-item active" style="background-image: url('https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">
        <!-- Floating Elements -->
        <div class="floating-elements">
          <i class="fas fa-coffee floating-element"></i>
          <i class="fas fa-cookie-bite floating-element"></i>
          <i class="fas fa-leaf floating-element"></i>
          <i class="fas fa-heart floating-element"></i>
        </div>
        
        <div class="carousel-caption">
          <div class="carousel-caption-badge">
            <i class="fas fa-award"></i>
            <span>Premium Coffee Experience</span>
          </div>
          <h1>Where Warmth Meets Flavor</h1>
          <p>Experience the perfect blend of artisanal coffee and freshly baked pastries in our cozy corner of Bandung.</p>
          <a href="/website/" class="btn-explore">
            <i class="fas fa-compass"></i>
            <span>Explore Our World</span>
          </a>
        </div>
      </div>

      <!-- Slide 2 -->
      <div class="carousel-item" style="background-image: url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">
        <!-- Floating Elements -->
        <div class="floating-elements">
          <i class="fas fa-coffee floating-element"></i>
          <i class="fas fa-cookie-bite floating-element"></i>
          <i class="fas fa-leaf floating-element"></i>
          <i class="fas fa-heart floating-element"></i>
        </div>
        
        <div class="carousel-caption">
          <div class="carousel-caption-badge">
            <i class="fas fa-star"></i>
            <span>Artisan Crafted</span>
          </div>
          <h1>Crafted with Passion</h1>
          <p>Every cup tells a story, every bite creates a memory. Discover our handcrafted beverages and artisan pastries.</p>
          <a href="/website/" class="btn-explore">
            <i class="fas fa-utensils"></i>
            <span>View Our Menu</span>
          </a>
        </div>
      </div>

      <!-- Slide 3 -->
      <div class="carousel-item" style="background-image: url('https://images.unsplash.com/photo-1554118811-1e0d58224f24?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">
        <!-- Floating Elements -->
        <div class="floating-elements">
          <i class="fas fa-coffee floating-element"></i>
          <i class="fas fa-cookie-bite floating-element"></i>
          <i class="fas fa-leaf floating-element"></i>
          <i class="fas fa-heart floating-element"></i>
        </div>
        
        <div class="carousel-caption">
          <div class="carousel-caption-badge">
            <i class="fas fa-home"></i>
            <span>Cozy Atmosphere</span>
          </div>
          <h1>Suasana Nyaman</h1>
          <p>Tempat terbaik untuk berbagi cerita, bekerja, atau sekadar menikmati momen ketenangan dengan secangkir kopi.</p>
          <a href="/website/" class="btn-explore">
            <i class="fas fa-door-open"></i>
            <span>Visit Us Today</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Carousel Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#landingCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#landingCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>

  <!-- Scroll Indicator -->
  <div class="scroll-indicator">
    <span>Scroll Down</span>
    <div class="scroll-arrow"></div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  
  <script>
    // Loading Screen
    window.addEventListener('load', function() {
      setTimeout(() => {
        document.getElementById('loadingScreen').classList.add('hidden');
      }, 2000);
    });

    // Initialize AOS
    AOS.init({
      duration: 1000,
      once: true,
      offset: 100
    });

    // Enhanced carousel with custom transitions
    const carousel = document.getElementById('landingCarousel');
    const carouselInstance = new bootstrap.Carousel(carousel, {
      interval: 4000,
      wrap: true,
      touch: true
    });

    // Add custom slide transition effects
    carousel.addEventListener('slide.bs.carousel', function (e) {
      const activeCaption = document.querySelector('.carousel-item.active .carousel-caption');
      const nextCaption = e.relatedTarget.querySelector('.carousel-caption');
      
      if (activeCaption) {
        activeCaption.style.animation = 'none';
      }
    });

    carousel.addEventListener('slid.bs.carousel', function (e) {
      const activeCaption = document.querySelector('.carousel-item.active .carousel-caption');
      if (activeCaption) {
        activeCaption.style.animation = 'slideInUp 1.2s ease forwards';
      }
    });

    // Smooth scroll for scroll indicator
    document.querySelector('.scroll-indicator').addEventListener('click', function() {
      window.scrollTo({
        top: window.innerHeight,
        behavior: 'smooth'
      });
    });

    // Parallax effect for floating elements
    window.addEventListener('scroll', function() {
      const scrolled = window.pageYOffset;
      const parallax = document.querySelectorAll('.floating-element');
      const speed = scrolled * 0.5;

      parallax.forEach((element, index) => {
        const yPos = -(scrolled * (0.3 + index * 0.1));
        element.style.transform = `translateY(${yPos}px) rotate(${scrolled * 0.1}deg)`;
      });
    });

    // Enhanced button hover effects
    document.querySelectorAll('.btn-explore').forEach(button => {
      button.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-3px) scale(1.05)';
      });
      
      button.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
      });
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
      if (e.key === 'ArrowLeft') {
        carouselInstance.prev();
      } else if (e.key === 'ArrowRight') {
        carouselInstance.next();
      }
    });

    // Touch gestures for mobile
    let startX = 0;
    let endX = 0;

    carousel.addEventListener('touchstart', function(e) {
      startX = e.touches[0].clientX;
    });

    carousel.addEventListener('touchend', function(e) {
      endX = e.changedTouches[0].clientX;
      handleSwipe();
    });

    function handleSwipe() {
      const threshold = 50;
      const diff = startX - endX;

      if (Math.abs(diff) > threshold) {
        if (diff > 0) {
          carouselInstance.next();
        } else {
          carouselInstance.prev();
        }
      }
    }

    // Auto-hide controls on mobile after interaction
    let controlsTimeout;
    const controls = document.querySelectorAll('.carousel-control-prev, .carousel-control-next, .carousel-indicators');

    function showControls() {
      controls.forEach(control => {
        control.style.opacity = '1';
      });
      
      clearTimeout(controlsTimeout);
      controlsTimeout = setTimeout(() => {
        if (window.innerWidth <= 768) {
          controls.forEach(control => {
            control.style.opacity = '0.3';
          });
        }
      }, 3000);
    }

    carousel.addEventListener('touchstart', showControls);
    carousel.addEventListener('mousemove', showControls);

    // Preload images for better performance
    function preloadImages() {
      const images = [
        'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
        'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
        'https://images.unsplash.com/photo-1554118811-1e0d58224f24?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'
      ];

      images.forEach(src => {
        const img = new Image();
        img.src = src;
      });
    }

    // Initialize preloading
    preloadImages();

    // Add intersection observer for animations
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, observerOptions);

    // Performance optimization: Reduce animations on low-end devices
    const isLowEndDevice = navigator.hardwareConcurrency <= 2 || navigator.deviceMemory <= 2;
    
    if (isLowEndDevice) {
      document.querySelectorAll('.floating-element').forEach(element => {
        element.style.display = 'none';
      });
    }
  </script>
</body>
</html>
