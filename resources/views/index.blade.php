<!doctype html>
<html class="no-js" lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- SEO Meta Tags -->
  <meta name="description" content="GP-INFRAS - Spécialiste en gestion de projet et infrastructure de pays. Découvrez nos services professionnels.">
  <meta property="og:title" content="GP-INFRAS - Gestion de projet et infrastructure">
  <meta property="og:description" content="Experts en gestion de projet et infrastructure de pays.">
  <meta property="og:image" content="{{ asset('social-image.jpg') }}">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta name="twitter:card" content="summary_large_image">
  
  @include('layouts.lurl')

  <!-- Preload Resources -->
  <link rel="preload" href="{{ asset('video.mp4') }}" as="video">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  
  <!-- Bootstrap CSS -->
  <link href="{{ asset('betsa/assets/css/bootstrap.min.css') }}" rel="stylesheet">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;700;800;900&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-color: #ff9900;
      --primary-hover: #e67e00;
      --text-light: #ffffff;
      --text-muted: rgba(255, 255, 255, 0.8);
      --transition-speed: 0.4s;
    }

    /* === POLICE === */
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      overflow-x: hidden;
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
    }

    /* === VIDEO === */
    .video-container {
      position: relative;
      width: 100%;
      height: 100vh;
      overflow: hidden;
    }

    video#bgVideo {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      min-width: 100%;
      min-height: 100%;
      width: auto;
      height: auto;
      object-fit: cover;
      z-index: -1;
      transition: opacity 1s ease;
    }

    /* === SUPERPOSITION === */
    .overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
    }

    /* === TITRE & CONTENU === */
    .overlay-wrapper {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      color: var(--text-light);
      opacity: 0;
      z-index: 2;
      width: 90%;
      max-width: 1200px;
      padding: 20px;
      pointer-events: none;
      transition: opacity var(--transition-speed) ease;
    }

    .overlay-wrapper.visible {
      opacity: 1;
      pointer-events: auto;
    }

    .overlay-title {
      font-size: clamp(4rem, 1vw, -1rem);
      font-weight: 900;
      margin-bottom: 0.6rem;
      opacity: 0;
      transform: translateY(20px);
      animation: fadeInUp 1s forwards;
      animation-delay: 0.3s;
      text-shadow: 0 6px 24px rgba(0, 0, 0, 0.45);
      line-height: 1.1;
      letter-spacing: 0.02em;
      text-transform: uppercase;
    }

    .overlay-subtitle {
      font-size: clamp(1.25rem, 2.8vw, 8.2rem);
      font-weight: 600;
      margin-bottom: 2.2rem;
      opacity: 0;
      transform: translateY(20px);
      animation: fadeInUp 1s forwards;
      animation-delay: 0.8s;
      color: var(--text-muted);
      text-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
      max-width: 1000px;
      margin-left: auto;
      margin-right: auto;
      letter-spacing: 0.01em;
    }

    .cta-button {
        display: inline-block;
        background: var(--primary-color);
        color: var(--text-light);
        padding: 15px 35px;
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        transition: all var(--transition-speed) ease;
        font-size: 1.1rem;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 1s forwards;
        animation-delay: 1.2s;
        position: relative;
        overflow: hidden;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(255, 153, 0, 0.3);
    }

    .cta-button:hover {
      background: var(--primary-hover);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(255, 153, 0, 0.4);
    }

    .cta-button:active {
      transform: translateY(1px);
    }

    .cta-button::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, 
        transparent, 
        rgba(255, 255, 255, 0.2), 
        transparent);
      transform: translateX(-100%);
      transition: transform 0.6s ease;
    }

    .cta-button:hover::after {
      transform: translateX(100%);
    }

    /* Scroll indicator */
    .scroll-indicator {
      position: absolute;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      color: var(--text-light);
      opacity: 0.8;
      animation: bounce 2s infinite;
      cursor: pointer;
      z-index: 10;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-decoration: none;
      font-size: 0.9rem;
      transition: opacity 0.3s ease;
    }

    .scroll-indicator:hover {
      opacity: 1;
    }

    .scroll-indicator svg {
      margin-top: 5px;
    }

    /* Skip video link for accessibility */
    .skip-video {
      position: absolute;
      top: 10px;
      left: 10px;
      padding: 8px 15px;
      background: #fff;
      color: #000;
      z-index: 100;
      opacity: 0;
      transition: opacity 0.3s;
      text-decoration: none;
      font-weight: 600;
      border-radius: 4px;
    }

    .skip-video:focus {
      opacity: 1;
      outline: 2px solid var(--primary-color);
    }

    /* Loading state */
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: #000;
      z-index: 9999;
      display: flex;
      justify-content: center;
      align-items: center;
      transition: opacity 0.5s ease;
    }

    .loading-spinner {
      width: 50px;
      height: 50px;
      border: 5px solid rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      border-top-color: var(--primary-color);
      animation: spin 1s linear infinite;
    }

    /* === ANIMATIONS === */
    @keyframes fadeInUp {
      0% {
        opacity: 0;
        transform: translateY(20px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { 
        transform: translateY(0) translateX(-50%); 
      }
      40% { 
        transform: translateY(-15px) translateX(-50%); 
      }
      60% { 
        transform: translateY(-7px) translateX(-50%); 
      }
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    /* === RESPONSIVE === */
    @media (max-width: 768px) {
      .cta-button {
        padding: 12px 25px;
        font-size: 1rem;
      }
      
      .overlay-wrapper {
        padding: 15px;
      }
    }

    @media (max-height: 600px) {
     
      
      .cta-button {
        padding: 10px 20px;
      }
    }
  </style>
</head>

<body>
  <!-- Loading overlay -->
  <div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
  </div>

  <!-- Header -->
  <header>
    @include('layouts.menu')
  </header>

  <main>
    <!-- Skip video link for accessibility -->
    <a href="#services" class="skip-video">Passer la vidéo d'introduction</a>

    <section id="home">
      <div class="video-container">
        <video id="bgVideo" autoplay muted loop playsinline poster="{{ asset('poster-image.jpg') }}">
          <source src="{{ asset('video.mp4') }}" type="video/mp4">
          <source src="{{ asset('video.webm') }}" type="video/webm">
          Votre navigateur ne supporte pas la vidéo HTML5.
        </video>

        <div class="overlay"></div>

        <div id="title" class="overlay-wrapper" aria-live="polite" aria-atomic="true">
          <h1 class="overlay-title" tabindex="0">GP-INFRAS</h1>
          <p class="overlay-subtitle" tabindex="0">Gestion de projet et infrastructure de pays</p>
          {{--<a href="{{ url('/login') }}" class="cta-button" aria-label="Connexion">Connexion</a>--}}
        </div>

        {{-- <a href="#services" class="scroll-indicator" aria-label="Aller à la section suivante">
          <span>Découvrir</span>
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M7 13l5 5 5-5M7 6l5 5 5-5"/>
          </svg>
        </a> --}}
      </div>
    </section>
  </main>

  <footer></footer>

  <!-- JS -->
  <script src="{{ asset('betsa/assets/js/jquery.js')}}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
  <script src="{{ asset('betsa/assets/js/bootstrap.min.js')}}"></script>
  <script src="{{ asset('betsa/assets/js/bootsnav.js')}}"></script>
  <script src="{{ asset('betsa/assets/js/owl.carousel.min.js')}}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
  <script src="{{ asset('betsa/assets/js/custom.js')}}"></script>

  <!-- Custom Scripts -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Hide loading overlay
      setTimeout(function() {
        document.getElementById('loadingOverlay').style.opacity = '0';
        setTimeout(function() {
          document.getElementById('loadingOverlay').style.display = 'none';
        }, 500);
      }, 1000);

      // Video and content animation
      const video = document.getElementById('bgVideo');
      const content = document.getElementById('title');
      
      // Try to play video (required for some mobile browsers)
      const playPromise = video.play();
      
      if (playPromise !== undefined) {
        playPromise.then(_ => {
          // Video started automatically
          showContent();
        })
        .catch(error => {
          // Auto-play was prevented
          console.log("Autoplay prevented, showing content anyway");
          showContent();
          
          // Add play button overlay if needed
          addPlayButton();
        });
      } else {
        // Fallback for browsers that don't support playPromise
        showContent();
      }
      
      function showContent() {
        content.classList.add('visible');
        
        // Preload fonts
        const font = new FontFace('Poppins', 'url(https://fonts.gstatic.com/s/poppins/v15/pxiByp8kv8JHgFVrLCz7Z1xlFQ.woff2)');
        font.load().then(() => document.fonts.add(font));
      }
      
      function addPlayButton() {
        const playBtn = document.createElement('button');
        playBtn.innerHTML = '▶ Lire la vidéo';
        playBtn.style.position = 'absolute';
        playBtn.style.bottom = '100px';
        playBtn.style.left = '50%';
        playBtn.style.transform = 'translateX(-50%)';
        playBtn.style.padding = '10px 20px';
        playBtn.style.background = 'rgba(255, 153, 0, 0.8)';
        playBtn.style.color = '#fff';
        playBtn.style.border = 'none';
        playBtn.style.borderRadius = '30px';
        playBtn.style.cursor = 'pointer';
        playBtn.style.zIndex = '10';
        playBtn.addEventListener('click', function() {
          video.play();
          playBtn.style.display = 'none';
        });
        
        document.querySelector('.video-container').appendChild(playBtn);
      }
      
      // Scroll to section
      function scrollToSection(sectionId) {
        document.querySelector(sectionId).scrollIntoView({
          behavior: 'smooth'
        });
      }
      
      // Add event listeners for scroll buttons
      document.querySelector('.scroll-indicator').addEventListener('click', function(e) {
        e.preventDefault();
        scrollToSection('#services');
      });
      
      document.querySelector('.skip-video').addEventListener('click', function(e) {
        e.preventDefault();
        scrollToSection('#services');
      });
      
      // Preload important images
      const imagesToPreload = [
        'service1.jpg',
        'service2.jpg',
        'team.jpg'
      ];
      
      imagesToPreload.forEach(imgSrc => {
        const img = new Image();
        img.src = imgSrc;
      });
      
      // Adjust video playback rate for slower connections
      if (navigator.connection) {
        if (navigator.connection.saveData || navigator.connection.effectiveType.includes('2g')) {
          video.playbackRate = 0.85;
        }
      }
    });
  </script>
</body>
</html>