// Acessibilidade
function toggleAccessibility() {
    document.body.classList.toggle('high-contrast');
}

function toggleFontSize() {
    const currentSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
    const newSize = currentSize >= 18 ? 16 : currentSize + 2;
    document.documentElement.style.fontSize = newSize + 'px';
}

// Mobile Menu
function toggleMobileMenu() {
    const nav = document.querySelector('.main-nav');
    nav.classList.toggle('active');
}

// Smooth Scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Loading Animation
window.addEventListener('load', () => {
    document.body.classList.add('loaded');
});

// Cookie Banner (se necessário)
if (!localStorage.getItem('cookieAccepted')) {
    // Implementar banner de cookies se necessário
}

console.log('Site carregado com sucesso!');
