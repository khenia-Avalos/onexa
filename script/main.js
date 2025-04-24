// Forzar la recarga de la página al navegar hacia atrás
window.addEventListener('pageshow', function(event) {  
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {   //es una version vieja y usando el historial atras adelante
        location.reload(true); 
    }
});