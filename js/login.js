// Toggle mostrar/ocultar contraseña
const toggle = document.getElementById('togglePwd');
const pwd = document.getElementById('contrasena');

toggle?.addEventListener('click', () => {
    if (pwd.type === 'password') { 
        pwd.type = 'text'; 
        toggle.textContent = 'Ocultar'; 
    } else { 
        pwd.type = 'password'; 
        toggle.textContent = 'Mostrar'; 
    }
});