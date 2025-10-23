// Manejo del modal
const modal = document.getElementById('modal');
const openModal = document.getElementById('openModal');
const closeModal = document.getElementById('closeModal');

openModal?.addEventListener('click', () => modal.classList.remove('hidden'));
closeModal?.addEventListener('click', () => modal.classList.add('hidden'));

// Búsqueda en tiempo real
const search = document.getElementById('search');
const table = document.getElementById('viajesTable');

search?.addEventListener('input', () => {
    const q = search.value.toLowerCase();
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        r.style.display = text.includes(q) ? '' : 'none';
    });
});

// Paginación
(function(){
    if (!table) return;
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const perPage = 10;
    if (rows.length <= perPage) return;
    
    let current = 0;
    const pager = document.createElement('div');
    pager.className = 'mt-4 flex gap-2 justify-center';
    
    function render() {
        // Mostrar/ocultar filas según la página actual
        table.querySelectorAll('tbody tr').forEach((r,i)=> 
            r.style.display = (i>=current && i<current+perPage) ? '' : 'none'
        );
        
        // Actualizar botones de paginación
        pager.innerHTML = '';
        const pages = Math.ceil(rows.length / perPage);
        for (let i=0; i<pages; i++){
            const btn = document.createElement('button');
            btn.textContent = i+1;
            btn.className = 'px-2 py-1 border rounded-sm ' + 
                          (i===Math.floor(current/perPage) ? 'bg-[color:var(--accent)] text-white' : '');
            btn.onclick = () => { 
                current = i*perPage; 
                render(); 
            };
            pager.appendChild(btn);
        }
    }
    
    table.parentNode.appendChild(pager);
    render();
})();