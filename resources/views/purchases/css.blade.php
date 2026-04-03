<style>
    /* Custom DataTable Dark Mode Support */
    [data-bs-theme="dark"] .purchase-datatable {
        --bs-table-bg: #1e1e1e;
        --bs-table-striped-bg: #2b2b2b;
        --bs-table-border-color: #444;
        color: #e0e0e0;
    }

    [data-bs-theme="dark"] .page-link {
        background-color: #2b2b2b;
        border-color: #444;
    }

    /* Ensure the responsive child row matches the theme */
    tr.dtrg-group,
    tr.child {
        background-color: var(--bs-tertiary-bg) !important;
    }

    /* Make action buttons stay visible */
    .all {
        min-width: 80px;
    }
</style>
