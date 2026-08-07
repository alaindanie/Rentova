/* =====================================================================
   ÉQUIPLOC — Interactions globales
   ===================================================================== */
(function () {
    'use strict';

    /* ------------------------- Navbar publique ------------------------- */
    var navbar = document.getElementById('navbar');
    if (navbar) {
        var onScroll = function () {
            navbar.classList.toggle('scrolled', window.scrollY > 24);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    var navToggle = document.getElementById('navToggle');
    var navLinks = document.getElementById('navLinks');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function () { navLinks.classList.toggle('open'); });
        navLinks.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () { navLinks.classList.remove('open'); });
        });
    }

    /* ------------------------- Sidebar (dashboard) ------------------------- */
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var openBtn = document.getElementById('sidebarOpen');
    var closeBtn = document.getElementById('sidebarClose');
    if (sidebar) {
        var openSide = function () { sidebar.classList.add('open'); if (overlay) overlay.classList.add('show'); };
        var closeSide = function () { sidebar.classList.remove('open'); if (overlay) overlay.classList.remove('show'); };
        if (openBtn) openBtn.addEventListener('click', openSide);
        if (closeBtn) closeBtn.addEventListener('click', closeSide);
        if (overlay) overlay.addEventListener('click', closeSide);
    }

    /* ------------------------- Révélation au scroll ------------------------- */
    var revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
        revealEls.forEach(function (el) { io.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('in'); });
    }

    /* ------------------------- Compteurs animés ------------------------- */
    function animateCounter(el) {
        var target = parseInt(el.dataset.count, 10) || 0;
        if (target === 0) { el.textContent = '0'; return; }
        var duration = 1200;
        var start = null;
        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(target * eased).toLocaleString('fr-FR');
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    var counters = document.querySelectorAll('.counter');
    if ('IntersectionObserver' in window) {
        var cio = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) { animateCounter(entry.target); cio.unobserve(entry.target); }
            });
        }, { threshold: 0.5 });
        counters.forEach(function (el) { cio.observe(el); });
    } else {
        counters.forEach(animateCounter);
    }

    /* ------------------------- Toasts ------------------------- */
    function dismissToast(toast) {
        toast.classList.add('hide');
        setTimeout(function () { toast.remove(); }, 350);
    }
    document.querySelectorAll('[data-toast]').forEach(function (toast) {
        setTimeout(function () { dismissToast(toast); }, 5200);
        var close = toast.querySelector('[data-toast-close]');
        if (close) close.addEventListener('click', function () { dismissToast(toast); });
    });

    /* ------------------------- Confirmations ------------------------- */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var message = form.dataset.confirm || 'Êtes-vous sûr de vouloir continuer ?';
            if (!window.confirm(message)) e.preventDefault();
        });
    });

    /* ------------------------- Remplissage démo (login) ------------------------- */
    document.querySelectorAll('.demo-pill').forEach(function (pill) {
        pill.addEventListener('click', function () {
            var value = pill.dataset.fill;
            var parts = value.split('|');
            var email = document.getElementById('email');
            var password = document.getElementById('password');
            if (email && password) {
                email.value = parts[0] || '';
                password.value = parts[1] || '';
                email.classList.add('pop');
                password.classList.add('pop');
                setTimeout(function () {
                    email.classList.remove('pop');
                    password.classList.remove('pop');
                }, 500);
            }
        });
    });

    /* ------------------------- Afficher/masquer mot de passe ------------------------- */
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.querySelector(btn.dataset.togglePassword);
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? '<i class="far fa-eye-slash"></i>' : '<i class="far fa-eye"></i>';
        });
    });

    /* ------------------------- Aperçu d'image upload ------------------------- */
    document.querySelectorAll('[data-upload-input]').forEach(function (input) {
        input.addEventListener('change', function () {
            var scope = input.closest('form') || input.parentElement;
            var preview = scope.querySelector('[data-upload-preview]');
            if (!preview) return;
            var file = input.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu">';
            };
            reader.readAsDataURL(file);
        });
    });

    /* ------------------------- Vue catalogue (grille/liste) ------------------------- */
    var viewToggle = document.querySelector('[data-view-toggle]');
    var grid = document.querySelector('[data-grid]');
    if (viewToggle && grid) {
        viewToggle.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                viewToggle.querySelectorAll('button').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                grid.classList.toggle('view-grid', btn.dataset.view === 'grid');
                grid.classList.toggle('view-list', btn.dataset.view === 'list');
            });
        });
    }

    /* ------------------------- Recherche automatique (debounce) ------------------------- */
    document.querySelectorAll('[data-filter-form]').forEach(function (form) {
        var timer = null;
        form.querySelectorAll('[data-filter-search]').forEach(function (input) {
            input.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () { form.submit(); }, 700);
            });
        });
    });

    /* ------------------------- Carrousel héros ------------------------- */
    var hero = document.querySelector('[data-hero-slider]');
    if (hero) {
        var heroSlides = hero.querySelectorAll('.hero-slide');
        var heroDots = hero.querySelectorAll('.hero-dot');
        var heroIndex = 0;
        var heroTimer = null;
        var HERO_INTERVAL = 15000;

        var heroShow = function (i) {
            heroIndex = (i + heroSlides.length) % heroSlides.length;
            heroSlides.forEach(function (s, k) { s.classList.toggle('active', k === heroIndex); });
            heroDots.forEach(function (d, k) { d.classList.toggle('active', k === heroIndex); });
        };
        var heroNext = function () { heroShow(heroIndex + 1); };
        var heroStart = function () {
            if (heroTimer) return;
            if (heroSlides.length > 1) heroTimer = setInterval(heroNext, HERO_INTERVAL);
        };
        var heroStop = function () { if (heroTimer) { clearInterval(heroTimer); heroTimer = null; } };

        heroDots.forEach(function (d, k) {
            d.addEventListener('click', function () { heroShow(k); heroStop(); heroStart(); });
        });
        hero.addEventListener('mouseenter', heroStop);
        hero.addEventListener('mouseleave', heroStart);
        heroStart();
    }

    /* ------------------------- Bouton retour en haut ------------------------- */
    var fab = document.querySelector('[data-top]');
    if (fab) {
        window.addEventListener('scroll', function () {
            fab.classList.toggle('show', window.scrollY > 400);
        });
    }
})();
