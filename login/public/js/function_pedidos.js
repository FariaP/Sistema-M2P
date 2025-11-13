(function () {
    const itemsBody = document.getElementById('items-body');
    const addItemBtn = document.getElementById('add-item');
    const totalCell = document.getElementById('total-cell');

    function calcTotal() {
        let total = 0;
        document.querySelectorAll('input[name="valor[]"]').forEach(i => {
            const v = parseFloat(i.value.replace(',', '.')) || 0;
            total += v;
        });
        if (totalCell) {
            totalCell.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
        }
    }

    function makeRow(desc = '', val = '') {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="descricao[]" value="${desc.replace(/"/g, '&quot;')}"></td>
            <td><input type="number" name="valor[]" step="0.01" value="${val}"></td>
            <td>
                <select name="status_item[]">
                    <option value="Aguardando">Aguardando</option>
                    <option value="Em andamento">Em andamento</option>
                    <option value="Concluído">Concluído</option>
                    <option value="Pausado">Pausado</option>
                    <option value="Cancelado">Cancelado</option>
                </select></td>
            <td style="text-align:center;">
                <button type="button" class="action-btn delete" data-action="remove">
                    Excluir
                </button>
            </td>`;
        itemsBody.appendChild(tr);
        tr.querySelectorAll('input').forEach(i => i.addEventListener('input', calcTotal));
        tr.querySelector('button[data-action="remove"]').addEventListener('click', function () { 
            tr.remove(); 
            calcTotal(); 
        });
        calcTotal();
    }

    // ✅ só adiciona o listener se o botão existir (ou seja, se for admin)
    if (addItemBtn) {
        addItemBtn.addEventListener('click', function () { makeRow('', ''); });
    }

    // attach listeners to existing inputs
    document.querySelectorAll('input[name="valor[]"]').forEach(i => i.addEventListener('input', calcTotal));

    // handle removal of existing items (which exist in DB)
    document.querySelectorAll('button[data-action="remove-existing"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const itemId = this.getAttribute('data-item-id');
            if (!confirm('Excluir este item?')) return;
            // enviar via fetch para excluir e remover a linha
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete_item&id=' + encodeURIComponent(itemId)
            }).then(r => r.text()).then(() => {
                const tr = this.closest('tr');
                if (tr) tr.remove();
                calcTotal();
            }).catch(() => { alert('Erro ao excluir item'); });
        });
    });

    calcTotal();
})();
