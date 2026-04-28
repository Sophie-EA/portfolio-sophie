// ===== BURGER MENU =====
document.addEventListener('DOMContentLoaded', () => {
    const burgerBtn = document.getElementById('burgerBtn');
    const navMenu   = document.getElementById('navMenu');
    
    if (!burgerBtn || !navMenu) return;

    burgerBtn.addEventListener('click', () => {
        const isExpanded = burgerBtn.getAttribute('aria-expanded') === 'true';
        
        // Toggle classes CSS
        burgerBtn.classList.toggle('active');
        navMenu.classList.toggle('active');
        
        // Accessibilité
        burgerBtn.setAttribute('aria-expanded', !isExpanded);
        
        // Optionnel : bloquer le scroll du body quand le menu est ouvert
        document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
    });

    // Fermer le menu quand on clique sur un lien (ancres)
    navMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (navMenu.classList.contains('active')) {
                burgerBtn.classList.remove('active');
                navMenu.classList.remove('active');
                burgerBtn.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Fermer si on redimensionne en desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768 && navMenu.classList.contains('active')) {
            burgerBtn.classList.remove('active');
            navMenu.classList.remove('active');
            burgerBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    });
});


// ===== CARROUSEL MULTI-CARDS =====
const trackMulti = document.querySelector('.carousel-track-multi');
if (trackMulti) {
    const slides = Array.from(trackMulti.children);
    const nextBtn = document.querySelector('.carousel-arrow.next');
    const prevBtn = document.querySelector('.carousel-arrow.prev');
    
    let currentIndex = 0;
    const slidesPerView = window.innerWidth <= 600 ? 1 : window.innerWidth <= 992 ? 2 : 3;
    
    const moveToSlide = (index) => {
        const slideWidth = slides[0].getBoundingClientRect().width;
        const gap = 32; // 2rem en px
        const amountToMove = -index * (slideWidth + gap);
        trackMulti.style.transform = `translateX(${amountToMove}px)`;
        currentIndex = index;
    };
    nextBtn.addEventListener('click', () => {
        const maxIndex = slides.length - slidesPerView;
        const nextIndex = currentIndex >= maxIndex ? 0 : currentIndex + 1;
        moveToSlide(nextIndex);
    });
    
    prevBtn.addEventListener('click', () => {
        const maxIndex = slides.length - slidesPerView;
        const prevIndex = currentIndex <= 0 ? maxIndex : currentIndex - 1;
        moveToSlide(prevIndex);
    });
    
    // Swipe mobile
    let touchStartX = 0;
    trackMulti.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    }, {passive: true});
    
    trackMulti.addEventListener('touchend', e => {
        const touchEndX = e.changedTouches[0].screenX;
        if (touchEndX < touchStartX - 50) nextBtn.click();
        if (touchEndX > touchStartX + 50) prevBtn.click();
    }, {passive: true});
}


// ===== ANIMATION SVG (courbe subtile) =====
// Si tu veux garder une animation très douce sur la ligne :
const bezierPath = document.querySelector('.bezier-line path');

if (bezierPath) {
    // Animation d'onde très subtile (optionnelle)
    let offset = 0;
    const originalD = bezierPath.getAttribute('d');
    
    // Animation légère de l'opacité ou du stroke-dashoffset au lieu de modifier les points (évite les NaN)
    let growing = true;
    setInterval(() => {
        if (growing) {
            bezierPath.style.strokeOpacity = 0.25;
            bezierPath.style.strokeWidth = 1.5;
        }
        growing = !growing;
    }, 3000); // Change toutes les 3 secondes très doucement
    
    // OU si tu veux l'effet "dessin" de la ligne :
    const length = bezierPath.getTotalLength();
    bezierPath.style.strokeDasharray = length;
    bezierPath.style.strokeDashoffset = length;
    
    // Animation CSS-like en JS pour le dessin
    let progress = 0;
    const drawLine = () => {
        progress += 0.005; // Très lent
        if (progress > 1) progress = 1;
        bezierPath.style.strokeDashoffset = length * (1 - progress);
        if (progress < 1) requestAnimationFrame(drawLine);
    };
    
    // Démarre l'animation au scroll quand la section est visible
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                drawLine();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    observer.observe(document.querySelector('.bezier-line'));
}

document.addEventListener('DOMContentLoaded', function() {
    // Lightbox
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.querySelector('.lightbox-img');
    const lightboxCaption = document.querySelector('.lightbox-caption');
    const closeBtn = document.querySelector('.lightbox-close');
    
    // Ouvrir lightbox sur click image galerie
    document.querySelectorAll('.galerie figure').forEach(figure => {
        figure.addEventListener('click', function() {
            const img = this.querySelector('img');
            const caption = this.querySelector('figcaption');
            
            lightboxImg.src = img.src;
            lightboxCaption.textContent = caption ? caption.textContent : '';
            lightbox.classList.add('active');
        });
    });
    
    // Fermer lightbox
    if (closeBtn) {
        closeBtn.addEventListener('click', () => lightbox.classList.remove('active'));
    }
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) lightbox.classList.remove('active');
    });
    
    // Fermer avec Echap
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') lightbox.classList.remove('active');
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toast');
    
    if (toast) {
        // Disparition automatique après 2.5 secondes
        setTimeout(() => {
            toast.classList.remove('show');
            
            // Supprime complètement du DOM après l'animation de sortie
            setTimeout(() => {
                toast.remove();
            }, 500);
        }, 2500);
    }
});
