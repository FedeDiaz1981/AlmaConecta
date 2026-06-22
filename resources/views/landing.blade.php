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
  <style>
    .cta-pair {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      flex-wrap: nowrap;
    }

    .cta-pair > .btn {
      min-height: 46px;
      padding-top: 12px;
      padding-bottom: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      line-height: 1.1;
    }

    .cta-pair--left {
      justify-content: flex-start;
    }

    .footer-brand__logo img {
      display: block;
      width: min(280px, 100%);
      height: auto;
      margin-bottom: 18px;
    }

    .hero-buttons {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: center;
      gap: 16px;
    }

    @media (max-width: 640px) {
      .cta-pair {
        flex-wrap: wrap;
      }

      .hero-buttons {
        flex-direction: column;
        align-items: center;
      }
    }

    .modal-backdrop {
      position: fixed;
      inset: 0;
      z-index: 60;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      background: rgba(7, 26, 56, 0.72);
      backdrop-filter: blur(8px);
    }

    .modal-backdrop.is-open {
      display: flex;
    }

    .register-modal {
      position: relative;
      width: min(100%, 520px);
      border-radius: 22px;
      border: 1px solid rgba(216, 161, 61, 0.3);
      background: linear-gradient(180deg, #fffaf2 0%, #fff 100%);
      box-shadow: 0 28px 80px rgba(2, 6, 23, 0.34);
      color: #10243f;
      overflow: hidden;
    }

    .register-modal__header {
      padding: 24px 24px 10px;
    }

    .register-modal__eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 12px;
      color: #b67b20;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.18em;
      text-transform: uppercase;
    }

    .register-modal__title {
      margin: 0;
      font-family: 'Playfair Display', serif;
      font-size: 30px;
      line-height: 1.05;
      color: #071a38;
    }

    .register-modal__body {
      padding: 0 24px 24px;
    }

    .register-modal__text {
      margin: 12px 0 20px;
      color: #516175;
      line-height: 1.5;
    }

    .register-modal__choices {
      display: grid;
      gap: 12px;
    }

    .register-choice {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      width: 100%;
      padding: 16px 18px;
      border-radius: 16px;
      border: 1px solid #eadfce;
      background: #fff;
      color: #10243f;
      cursor: pointer;
      text-align: left;
      box-shadow: 0 10px 28px rgba(17, 34, 61, 0.08);
      transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
    }

    .register-choice:hover,
    .register-choice:focus-visible {
      transform: translateY(-1px);
      border-color: #d8a13d;
      box-shadow: 0 16px 34px rgba(216, 161, 61, 0.18);
      outline: none;
    }

    .register-choice strong {
      display: block;
      font-size: 16px;
      margin-bottom: 4px;
    }

    .register-choice span {
      display: block;
      font-size: 13px;
      color: #637083;
      line-height: 1.4;
    }

    .register-modal__close {
      position: absolute;
      top: 14px;
      right: 14px;
      width: 38px;
      height: 38px;
      border-radius: 999px;
      border: 1px solid #eadfce;
      background: #fff;
      color: #10243f;
      font-size: 22px;
      line-height: 1;
      cursor: pointer;
    }

    body.modal-open {
      overflow: hidden;
    }

    @media (max-width: 640px) {
      .register-modal__header,
      .register-modal__body {
        padding-left: 18px;
        padding-right: 18px;
      }

      .register-modal__title {
        font-size: 26px;
      }

      .register-choice {
        padding: 14px 16px;
      }
    }
  </style>
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
      <div class="cta-pair">
        <a class="btn btn-gold" href="{{ route('home') }}">Ir al portal</a>
        <button type="button" class="btn btn-outline" data-register-open>Registrarse</button>
      </div>
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
          <div class="cta-pair">
            <a class="btn btn-gold" href="{{ route('home') }}">Ir al portal</a>
            <button type="button" class="btn btn-outline" data-register-open>Registrarse</button>
          </div>
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
      <div class="cta-pair">
        <a class="btn btn-gold mid-cta" href="{{ route('home') }}">Ir al portal</a>
        <button type="button" class="btn btn-outline mid-cta" data-register-open>Registrarse</button>
      </div>
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
        <div class="cta-pair cta-pair--left">
          <a class="btn btn-gold" href="{{ route('home') }}">Ir al portal</a>
          <button type="button" class="btn btn-outline" data-register-open>Registrarse</button>
        </div>
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
        <div class="map-actions">
          <a class="btn btn-gold map-cta" href="{{ route('home') }}">Ir al portal</a>
          <button type="button" class="btn btn-outline map-cta" data-register-open>Registrarse</button>
        </div>
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
      <img class="footer-brand__logo" src="{{ asset('landing/assets/img/logo-navbar-footer-transparent.png') }}" alt="Alma Conecta">
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

  <div id="register-modal" class="modal-backdrop" aria-hidden="true">
    <div class="register-modal" role="dialog" aria-modal="true" aria-labelledby="register-modal-title">
      <button type="button" class="register-modal__close" data-register-close aria-label="Cerrar modal">×</button>
      <div class="register-modal__header">
        <div class="register-modal__eyebrow">Registrate</div>
        <h2 id="register-modal-title" class="register-modal__title">Elegí cómo querés sumarte</h2>
      </div>
      <div class="register-modal__body">
        <p class="register-modal__text">
          Te llevamos directo al formulario correcto según el tipo de cuenta que quieras crear.
        </p>
        <div class="register-modal__choices">
          <button
            type="button"
            class="register-choice"
            data-register-href="{{ route('register', ['account_type' => 'provider']) }}"
          >
            <span>
              <strong>Quiero publicar mi espacio</strong>
              <span>Crear una cuenta profesional para ofrecer terapias, talleres o servicios.</span>
            </span>
            <span aria-hidden="true">→</span>
          </button>

          <button
            type="button"
            class="register-choice"
            data-register-href="{{ route('register', ['account_type' => 'client']) }}"
          >
            <span>
              <strong>Busco un profesional</strong>
              <span>Crear una cuenta para buscar, contactar y guardar favoritos.</span>
            </span>
            <span aria-hidden="true">→</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('landing/assets/js/app.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const modal = document.getElementById('register-modal');
      const openButtons = document.querySelectorAll('[data-register-open]');
      const closeButton = document.querySelector('[data-register-close]');
      const choiceButtons = document.querySelectorAll('[data-register-href]');

      const openModal = () => {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
      };

      const closeModal = () => {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
      };

      openButtons.forEach((button) => button.addEventListener('click', openModal));
      closeButton?.addEventListener('click', closeModal);

      modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
          closeModal();
        }
      });

      choiceButtons.forEach((button) => {
        button.addEventListener('click', () => {
          const href = button.dataset.registerHref;
          if (href) {
            window.location.href = href;
          }
        });
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          closeModal();
        }
      });
    });
  </script>
</body>
</html>
