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

    /* ------------------------- Barre de progression au scroll ------------------------- */
    var progress = document.querySelector('[data-scroll-progress]');
    if (progress) {
        var onProgress = function () {
            var h = document.documentElement.scrollHeight - window.innerHeight;
            progress.style.width = (h > 0 ? (window.scrollY / h) * 100 : 0) + '%';
        };
        window.addEventListener('scroll', onProgress, { passive: true });
        onProgress();
    }

    /* ------------------------- Graphique : croissance du donut ------------------------- */
    document.querySelectorAll('[data-chart]').forEach(function (chart) {
        var segs = chart.querySelectorAll('.donut-seg[data-len]');
        if (!segs.length) return;
        var run = function () {
            segs.forEach(function (seg) {
                var len = parseFloat(seg.dataset.len) || 0;
                var c = parseFloat(seg.dataset.c) || 1;
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        seg.style.strokeDasharray = len + ' ' + c;
                    });
                });
            });
        };
        if ('IntersectionObserver' in window) {
            var chartIo = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) { run(); chartIo.unobserve(chart); }
                });
            }, { threshold: 0.3 });
            chartIo.observe(chart);
        } else { run(); }
    });

    /* ------------------------- Bouton retour en haut ------------------------- */
    var fab = document.querySelector('[data-top]');
    if (fab) {
        window.addEventListener('scroll', function () {
            fab.classList.toggle('show', window.scrollY > 400);
        });
        fab.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ------------------------- Particules flottantes ------------------------- */
    document.querySelectorAll('[data-particles]').forEach(function (scope) {
        var count = parseInt(scope.dataset.particles, 10) || 28;
        var colors = ['', 'blue', 'violet'];
        var frag = document.createDocumentFragment();
        for (var i = 0; i < count; i++) {
            var p = document.createElement('span');
            p.className = 'particle' + (colors[i % 3] ? ' ' + colors[i % 3] : '');
            var size = (Math.random() * 4 + 2).toFixed(1);
            p.style.width = size + 'px';
            p.style.height = size + 'px';
            p.style.left = (Math.random() * 100).toFixed(2) + '%';
            p.style.top = (Math.random() * 90 + 10).toFixed(2) + '%';
            p.style.setProperty('--po', (Math.random() * 0.45 + 0.15).toFixed(2));
            p.style.animationDuration = (Math.random() * 9 + 9).toFixed(1) + 's';
            p.style.animationDelay = (Math.random() * 8).toFixed(1) + 's';
            frag.appendChild(p);
        }
        scope.appendChild(frag);
    });

    /* ------------------------- Halo suivant le curseur ------------------------- */
    document.querySelectorAll('[data-glow]').forEach(function (scope) {
        var spot = document.createElement('span');
        spot.className = 'glow-spot';
        scope.appendChild(spot);
        scope.addEventListener('mousemove', function (e) {
            var r = scope.getBoundingClientRect();
            spot.style.left = (e.clientX - r.left) + 'px';
            spot.style.top = (e.clientY - r.top) + 'px';
        });
    });

    /* ------------------------- Cartes en tilt 3D ------------------------- */
    document.querySelectorAll('[data-tilt]').forEach(function (card) {
        var max = parseFloat(card.dataset.tilt) || 8;
        var active = false;
        card.addEventListener('mousemove', function (e) {
            if (!active) { card.style.transition = 'transform .08s linear'; active = true; }
            var r = card.getBoundingClientRect();
            var x = (e.clientX - r.left) / r.width - 0.5;
            var y = (e.clientY - r.top) / r.height - 0.5;
            card.style.transform = 'perspective(900px) rotateX(' + (-y * max).toFixed(2) + 'deg) rotateY(' + (x * max).toFixed(2) + 'deg) translateY(-5px)';
        });
        card.addEventListener('mouseleave', function () {
            card.style.transition = '';
            card.style.transform = '';
            active = false;
        });
    });

    /* ------------------------- Boutons magnétiques ------------------------- */
    document.querySelectorAll('[data-magnetic]').forEach(function (btn) {
        var strength = parseFloat(btn.dataset.magnetic) || 0.3;
        var active = false;
        btn.addEventListener('mousemove', function (e) {
            if (!active) { btn.style.transition = 'transform .06s linear'; active = true; }
            var r = btn.getBoundingClientRect();
            var x = (e.clientX - r.left - r.width / 2) * strength;
            var y = (e.clientY - r.top - r.height / 2) * strength;
            btn.style.transform = 'translate(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px)';
        });
        btn.addEventListener('mouseleave', function () {
            btn.style.transition = '';
            btn.style.transform = '';
            active = false;
        });
    });

    /* ------------------------- Preloader ------------------------- */
    var preloader = document.getElementById('preloader');
    if (preloader) {
        var hidePre = function () {
            preloader.classList.add('done');
            setTimeout(function () { preloader.style.display = 'none'; }, 650);
        };
        if (document.readyState === 'complete') {
            setTimeout(hidePre, 350);
        } else {
            window.addEventListener('load', hidePre);
        }
        setTimeout(hidePre, 4000);
    }

    /* ------------------------- Confettis ------------------------- */
    function runConfetti(duration) {
        duration = duration || 3500;
        var canvas = document.createElement('canvas');
        canvas.className = 'confetti-canvas';
        document.body.appendChild(canvas);
        var ctx = canvas.getContext('2d');
        var W = canvas.width = window.innerWidth;
        var H = canvas.height = window.innerHeight;
        var colors = ['#2563eb', '#7c3aed', '#38bdf8', '#a855f7', '#f59e0b', '#22c55e', '#f43f5e', '#eab308'];
        var pieces = [];

        function spawn(n, x, y) {
            for (var i = 0; i < n; i++) {
                var angle = Math.random() * Math.PI * 2;
                var speed = 3 + Math.random() * 7;
                var bursty = x !== undefined;
                pieces.push({
                    x: bursty ? x : Math.random() * W,
                    y: bursty ? y : -20 - Math.random() * H * 0.4,
                    w: 6 + Math.random() * 8,
                    h: 9 + Math.random() * 12,
                    color: colors[(Math.random() * colors.length) | 0],
                    vx: (bursty ? Math.cos(angle) : (Math.random() - 0.5)) * speed,
                    vy: (bursty ? Math.sin(angle) : 1.5 + Math.random() * 2) * (bursty ? speed * 0.4 : 1),
                    rot: Math.random() * Math.PI * 2,
                    vr: (Math.random() - 0.5) * 0.25,
                    life: 1
                });
            }
        }
        spawn(90);
        spawn(45, W * 0.2, H * 0.15);
        spawn(45, W * 0.8, H * 0.12);

        var start = null;
        function frame(ts) {
            if (!start) start = ts;
            var elapsed = ts - start;
            ctx.clearRect(0, 0, W, H);
            for (var i = 0; i < pieces.length; i++) {
                var p = pieces[i];
                p.x += p.vx;
                p.y += p.vy;
                p.vy += 0.08;
                p.rot += p.vr;
                p.vx *= 0.99;
                if (p.y > H + 40) p.life = 0;
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.rot);
                ctx.globalAlpha = Math.max(0, p.life);
                ctx.fillStyle = p.color;
                ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
                ctx.restore();
            }
            pieces = pieces.filter(function (q) { return q.life > 0; });
            if (elapsed < duration && pieces.length) {
                requestAnimationFrame(frame);
            } else {
                canvas.parentNode.removeChild(canvas);
            }
        }
        requestAnimationFrame(frame);
        window.addEventListener('resize', function () {
            W = canvas.width = window.innerWidth;
            H = canvas.height = window.innerHeight;
        });
    }
    if (document.body.hasAttribute('data-confetti')) {
        setTimeout(function () { runConfetti(); }, 600);
    }

    /* --------------------- Toggle champ prix promo --------------------- */
    function syncPromoField(toggle) {
        var field = document.querySelector('.promo-price-field');
        if (!field) return;
        var priceInput = document.querySelector('[data-promo-price]');
        if (toggle.checked) {
            field.classList.remove('hidden');
            if (priceInput && priceInput.value === '') priceInput.focus();
        } else {
            field.classList.add('hidden');
        }
    }
    document.querySelectorAll('[data-promo-toggle]').forEach(function (toggle) {
        toggle.addEventListener('change', function () { syncPromoField(toggle); });
        syncPromoField(toggle);
    });

    /* --------------------- Confirmation des formulaires --------------------- */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var message = form.getAttribute('data-confirm') || 'Confirmer cette action ?';
            if (!window.confirm(message)) {
                e.preventDefault();
            }
        });
    });
})();
