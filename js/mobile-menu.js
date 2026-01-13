// mobile-menu.js - Script para menú móvil responsive
document.addEventListener('DOMContentLoaded', function () {
    // Crear botón hamburguesa
    const menuToggle = document.createElement('button');
    menuToggle.className = 'menu-toggle';
    menuToggle.innerHTML = '<span class="hamburger-icon">☰</span>';
    menuToggle.setAttribute('aria-label', 'Abrir menú de navegación');
    menuToggle.setAttribute('aria-expanded', 'false');
    menuToggle.setAttribute('aria-controls', 'sidebar');

    // Crear overlay
    const overlay = document.createElement('div');
    overlay.className = 'menu-overlay';
    overlay.setAttribute('aria-hidden', 'true');

    // Insertar en el DOM
    document.body.prepend(overlay);
    document.body.prepend(menuToggle);

    const sidebar = document.querySelector('.barra-lateral');

    if (!sidebar) {
        console.warn('No se encontró .barra-lateral');
        return;
    }

    // Agregar ID para accesibilidad
    sidebar.id = 'sidebar';
    sidebar.setAttribute('role', 'navigation');
    sidebar.setAttribute('aria-label', 'Menú principal');

    // Función para toggle del menú
    function toggleMenu() {
        const isActive = sidebar.classList.toggle('active');
        overlay.classList.toggle('active');

        // Actualizar icono
        menuToggle.innerHTML = isActive ? '<span class="hamburger-icon">✕</span>' : '<span class="hamburger-icon">☰</span>';

        // Actualizar ARIA
        menuToggle.setAttribute('aria-expanded', isActive);
        overlay.setAttribute('aria-hidden', !isActive);

        // Prevenir scroll del body cuando el menú está abierto
        if (isActive) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }

    // Event listeners
    menuToggle.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', toggleMenu);

    // Cerrar menú al hacer clic en un enlace (solo en móvil)
    const menuLinks = sidebar.querySelectorAll('a');
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                toggleMenu();
            }
        });
    });

    // Cerrar menú con tecla ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            toggleMenu();
        }
    });

    // Cerrar menú al cambiar de orientación o redimensionar
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.innerWidth > 768 && sidebar.classList.contains('active')) {
                toggleMenu();
            }
        }, 250);
    });

    console.log('Mobile menu initialized');
});
