<?php
// ============================================================
// includes/footer.php
// ============================================================
?>
    </div><!-- /page-body -->
</div><!-- /main-content -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js (for reports) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
// Mobile sidebar toggle
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar   = document.getElementById('sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
    }

    // Auto-dismiss alerts after 4s
    document.querySelectorAll('.alert').forEach(el => {
        setTimeout(() => el && el.remove(), 4000);
    });

    // DataTable-like sorting
    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.style.cursor = 'pointer';
        th.addEventListener('click', () => {
            const table   = th.closest('table');
            const idx     = Array.from(th.parentElement.children).indexOf(th);
            const asc     = th.dataset.asc !== 'true';
            th.dataset.asc = asc;
            const rows    = Array.from(table.querySelectorAll('tbody tr'));
            rows.sort((a, b) => {
                const va = a.children[idx]?.textContent.trim() ?? '';
                const vb = b.children[idx]?.textContent.trim() ?? '';
                return asc ? va.localeCompare(vb, undefined, {numeric:true}) : vb.localeCompare(va, undefined, {numeric:true});
            });
            table.querySelector('tbody').append(...rows);
        });
    });

    // Live table search
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q   = searchInput.value.toLowerCase();
            const tbl = document.getElementById('mainTable');
            if (!tbl) return;
            tbl.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }
});
</script>
<?php if (isset($extraJs)) echo $extraJs; ?>
</body>
</html>
