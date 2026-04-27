document.addEventListener('DOMContentLoaded', () => {
    const lightbox = document.getElementById('lightbox');
    if (!lightbox) return;

    const lbImg      = lightbox.querySelector('.lightbox-img');
    const lbCaption  = lightbox.querySelector('.lightbox-caption');
    const closeBtn   = lightbox.querySelector('.lightbox-close');
    const figures    = document.querySelectorAll('.galerie-item');
    
    if (figures.length === 0) return;

    const images = Array.from(figures).map(f => f.querySelector('img')).filter(Boolean);
    let currentIndex = 0;

    function open(index) {
        if (index < 0 || index >= images.length) return;
        currentIndex = index;
        const img = images[index];
        
        lbImg.src = img.src;
        lbImg.alt = img.alt || '';
        lbCaption.textContent = img.getAttribute('alt') || '';
        
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden'; // Empêche le scroll de fond
    }

    function close() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
        lbImg.src = '';
    }

    function next() { open((currentIndex + 1) % images.length); }
    function prev() { open((currentIndex - 1 + images.length) % images.length); }

    // Ouverture
    figures.forEach((fig, i) => {
        fig.style.cursor = 'zoom-in';
        fig.addEventListener('click', () => open(i));
    });

    // Fermeture
    closeBtn.addEventListener('click', close);
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) close();
    });

    // Clavier
    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('active')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowRight') next();
        if (e.key === 'ArrowLeft') prev();
    });
});
