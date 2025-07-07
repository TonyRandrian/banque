console.log("API configuration loaded.");

// Point d'accès API centralisé pour tout le front
if (typeof window.apiBase === 'undefined') {
    window.apiBase = "http://localhost/Web/S4/banque/ws";
}
