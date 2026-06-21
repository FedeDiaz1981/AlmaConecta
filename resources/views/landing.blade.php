<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Alma Conecta - Bienestar holístico</title>
  <meta name="description" content="Conectamos personas con terapeutas, facilitadores y espacios holísticos de confianza.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('landing/assets/css/styles.css') }}">
  @include('partials.tracking-head')
</head>
<body>
  @include('partials.tracking-body')

  <header class="header">
    <a class="brand" href="{{ route('landing') }}">
      <img src="{{ asset('landing/assets/img/logo-navbar.png') }}" alt="Alma Conecta">
    </a>
    <button class="menu-btn" aria-label="Abrir menú">☰</button>
    <nav class="nav">
      <a href="#como-funciona">Cómo funciona</a>
      <a href="#categorias">Categorías</a>
      <a href="#pro">Para profesionales</a>
      <a href="#historias">Historias reales</a>
      <a href="#comunidad">Comunidad</a>
    </nav>
    <div class="header-actions">
      <a class="btn btn-gold" href="{{ route('home') }}">Ir al portal</a>
    </div>
  </header>

  <main>
    <section class="hero">
      <picture class="hero-bg">
        <source media="(max-width: 640px)" srcset="{{ asset('landing/assets/img/hero-home-mobile.png') }}">
        <img src="{{ asset('landing/assets/img/hero_home.png') }}" alt="Paisaje de bienestar holístico">
      </picture>
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <img class="hero-logo" src="{{ asset('landing/assets/img/logo-simbolo-hero.png') }}" alt="">
        <h1>Encontrá el <span>bienestar</span><br>que estás buscando</h1>
        <p>Conectamos personas con terapeutas, facilitadores<br>y espacios holísticos de confianza.</p>
        <div class="hero-buttons">
          <a class="btn btn-gold" href="{{ route('home') }}">Ingresar al portal</a>
          <a class="btn btn-outline" href="#como-funciona">Cómo funciona</a>
        </div>
      </div>
    </section>

    <section class="section how" id="como-funciona">
      <h2>¿Cómo funciona?</h2>
      <div class="steps">
        <article><i>1</i>
          <div class="round">⌕</div>
          <h3>Buscar</h3>
          <p>Explorá cientos de profesionales<br>y espacios holísticos.</p>
        </article>
        <span class="arrow">→</span>
        <article><i>2</i>
          <div class="round">💬</div>
          <h3>Conectar</h3>
          <p>Contactá directamente<br>con el profesional.</p>
        </article>
        <span class="arrow">→</span>
        <article><i>3</i>
          <div class="round">♧</div>
          <h3>Transformar</h3>
          <p>Comenzá tu camino<br>de bienestar.</p>
        </article>
      </div>
    </section>

    <div class="mid-cta-wrap">
      <a class="btn btn-gold mid-cta" href="{{ route('home') }}">Ir al portal</a>
    </div>

    <section class="section" id="categorias">
      <div class="section-title">
        <h2>Categorías destacadas</h2><a href="{{ route('search', ['all' => 1]) }}">Ver todas</a>
      </div>
      <div class="cat-grid">
        <article><img src="{{ asset('landing/assets/img/categoria-reiki.png') }}"><span>Reiki</span></article>
        <article><img src="{{ asset('landing/assets/img/categoria-yoga.png') }}"><span>Yoga</span></article>
        <article><img src="{{ asset('landing/assets/img/categoria-acupuntura.png') }}"><span>Acupuntura</span></article>
        <article><img src="{{ asset('landing/assets/img/categoria-meditacion.png') }}"><span>Meditación</span></article>
        <article><img src="{{ asset('landing/assets/img/categoria-constelaciones.png') }}"><span>Constelaciones</span></article>
        <article><img src="{{ asset('landing/assets/img/categoria-masajes.png') }}"><span>Masajes</span></article>
        <article><img src="{{ asset('landing/assets/img/categoria-feng-shui.png') }}"><span>Feng Shui</span></article>
        <article><img src="{{ asset('landing/assets/img/categoria-sonoterapia.png') }}"><span>Sonoterapia</span></article>
      </div>
    </section>

    <section class="professional" id="pro">
      <img src="{{ asset('landing/assets/img/profesional-terapias-cuencos.png') }}" alt="Profesional holística">
      <div>
        <span>Para profesionales</span>
        <h2>Hacé crecer tu práctica</h2>
        <div class="benefits">
          <p><b>◕ Publicá tu perfil</b><br>Mostrá quién sos y qué hacés.</p>
          <p><b>♧ Mostrá tus servicios</b><br>Detallá tus terapias, talleres y actividades.</p>
          <p><b>♡ Recibí consultas</b><br>Conectá con personas que buscan tu ayuda.</p>
          <p><b>◇ Generá confianza</b><br>Construí tu reputación con valoraciones.</p>
        </div>
        <a class="btn btn-gold" href="{{ route('home') }}">Ir al portal</a>
      </div>
    </section>

    <section class="stories" id="historias">
      <div class="testimonials">
        <h2>Historias reales</h2>
        <article>
          <img src="{{ asset('landing/assets/img/avatar-carla.jpg') }}" alt="Carla G.">
          <div>
            <b>★★★★★</b>
            <p>“Encontré a mi terapeuta ideal en pocos minutos. Alma Conecta cambió mi bienestar.”</p>
            <small>Carla G.</small>
          </div>
        </article>
        <article>
          <img src="{{ asset('landing/assets/img/avatar-diego.jpg') }}" alt="Diego M.">
          <div>
            <b>★★★★★</b>
            <p>“Me permitió dar a conocer mis servicios y conseguir nuevos consultantes.”</p>
            <small>Diego M.</small>
          </div>
        </article>
      </div>
      <div class="map">
        <div>
          <h2>Un mapa de bienestar</h2>
          <p>Más de 2.000 profesionales y espacios distribuidos en todo el país.</p>
        </div>
        <img src="{{ asset('landing/assets/img/mapa-argentina-bienestar.png') }}" alt="Mapa Argentina">
        <a class="btn btn-gold map-cta" href="{{ route('home') }}">Ir al portal</a>
      </div>
    </section>

    <section class="community" id="comunidad">
      <div>
        <span>Comunidad Alma Conecta</span>
        <h2>No estás buscando una terapia.<br>Estás buscando sentirte mejor.</h2>
      </div>
      <article><b data-count="2000" data-suffix="+">2.000+</b><small>Profesionales</small></article>
      <article><b data-count="50" data-suffix="+">50+</b><small>Especialidades</small></article>
      <article><b data-count="120" data-suffix="+">120+</b><small>Ciudades</small></article>
      <article><b data-count="15000" data-suffix="+">15.000+</b><small>Consultas realizadas</small></article>
    </section>
  </main>

  <footer class="footer">
    <div class="footer-brand">
      <img src="{{ asset('landing/assets/img/logo-navbar.png') }}" alt="Alma Conecta">
      <p>El punto de encuentro entre quienes buscan bienestar y quienes acompañan caminos de transformación.</p>
    </div>
    <nav>
      <b>Secciones</b>
      <a href="#como-funciona">Cómo funciona</a>
      <a href="#categorias">Categorías</a>
      <a href="#pro">Para profesionales</a>
      <a href="#historias">Historias reales</a>
      <a href="#comunidad">Comunidad</a>
    </nav>
    <div class="social">
      <b>Seguinos</b>
      <a href="#" aria-label="Instagram" class="social-link">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <rect x="3" y="3" width="18" height="18" rx="5"></rect>
          <circle cx="12" cy="12" r="4"></circle>
          <circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"></circle>
        </svg>
        Instagram
      </a>
      <a href="#" aria-label="Facebook" class="social-link">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <path d="M14 8h2.2V5.6H14c-2.1 0-3.8 1.7-3.8 3.8V11H8v2.7h2.2V19H13v-5.3h2.2L15.6 11H13V9.8c0-.9.6-1.8 1-1.8z"></path>
        </svg>
        Facebook
      </a>
    </div>
  </footer>

  <script src="{{ asset('landing/assets/js/app.js') }}"></script>
</body>
</html>
