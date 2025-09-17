document.addEventListener('DOMContentLoaded', function() {
    // Toggle de status da tarefa
    document.querySelectorAll('.status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const form = this.closest('form');
            form.submit();
        });
    });

    // Confirmação de exclusão
    document.querySelectorAll('.btn-excluir').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Tem certeza que deseja excluir esta tarefa?')) {
                e.preventDefault();
            }
        });
    });

    // Filtro responsivo
    const filtroSelect = document.getElementById('filtro');
    if (filtroSelect) {
        filtroSelect.addEventListener('change', function() {
            window.location.href = `dashboard.php?filtro=${this.value}`;
        });
    }
});

// Função para mostrar/ocultar formulário de edição
function toggleEditForm(taskId) {
    const form = document.getElementById(`edit-form-${taskId}`);
    const card = document.getElementById(`task-${taskId}`);
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        card.style.display = 'none';
    } else {
        form.style.display = 'none';
        card.style.display = 'block';
    }
}