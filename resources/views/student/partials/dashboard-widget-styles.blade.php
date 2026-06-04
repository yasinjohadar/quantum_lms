@include('admin.pages.dashboard.partials.widget-styles')
<style>
    .dashboard-panel {
        border-radius: 14px;
        border: 1px solid var(--default-border);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        transition: box-shadow 0.25s ease;
    }
    .dashboard-panel:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
    }
    [data-theme-mode="dark"] .dashboard-panel {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
    }
    .dashboard-panel .card-header {
        border-bottom: 1px solid var(--default-border);
        background: transparent;
    }
    .dashboard-panel .card-header h4,
    .dashboard-panel .card-header h5,
    .dashboard-panel .card-header h6 {
        margin-bottom: 0;
    }
    .dashboard-panel .progress {
        border-radius: 6px;
    }
    .dashboard-subject-card {
        border-radius: 12px;
        border: 1px solid var(--default-border);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .dashboard-subject-card:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }
    .reports-chart-wrap {
        min-height: 300px;
        width: 100%;
    }
    @media (max-width: 768px) {
        .reports-chart-wrap {
            min-height: 260px;
        }
    }
</style>
