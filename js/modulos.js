document.addEventListener('DOMContentLoaded', function() {
    // Seleccionamos todos los encabezados de los módulos
    const encabezados = document.querySelectorAll('.modulo-encabezado');

    encabezados.forEach(encabezado => {
        encabezado.addEventListener('click', function() {
            // Encuentra el elemento padre que contiene todo el módulo
            const modulo = this.closest('.modulo');

            // Alterna la clase 'modulo-activo'
            modulo.classList.toggle('modulo-activo');
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Código existente para el acordeón...

    // NUEVO CÓDIGO PARA EL MODAL
    const modal = document.getElementById('modal-video');
    const cerrarBtn = document.querySelector('.cerrar-modal');
    const videoContainer = modal.querySelector('.video-container');

    // Selecciona todos los enlaces de temas que tengan un ID de video
    const temasVideo = document.querySelectorAll('.tema-link');

    temasVideo.forEach(tema => {
        tema.addEventListener('click', function() {
            // Obtiene el ID del video del atributo de datos
            const videoId = this.dataset.videoId;
            if (videoId) {
                // Genera la URL de YouTube para incrustar
                const youtubeUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
                // Crea el iframe y lo añade al contenedor
                videoContainer.innerHTML = `<iframe src="${youtubeUrl}" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>`;
                // Muestra el modal
                modal.style.display = 'block';
            }
        });
    });

    // Cierra el modal cuando se hace clic en la 'X'
    cerrarBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        // Detiene el video al cerrar el modal
        videoContainer.innerHTML = '';
    });

    // Cierra el modal si el usuario hace clic fuera de la ventana
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            // Detiene el video al cerrar el modal
            videoContainer.innerHTML = '';
        }
    });
});