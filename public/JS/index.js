// ===== CARROUSEL MULTI-CARDS (corrigé) =====
const trackMulti = document.querySelector('.carousel-track-multi');
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
