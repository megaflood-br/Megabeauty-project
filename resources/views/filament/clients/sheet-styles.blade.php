<style>
    .fi-resource-clients.fi-resource-create-record-page .fi-header,
    .fi-resource-clients.fi-resource-edit-record-page .fi-header {
        display: none;
    }
    .mb-client-sheet {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }
    .dark .mb-client-sheet {
        background: #111827;
        border-color: #374151;
    }
    .mb-client-sheet-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        font-size: 1.05rem;
        font-weight: 700;
        color: #111827;
    }
    .dark .mb-client-sheet-header {
        border-bottom-color: #374151;
        color: #f9fafb;
    }
    .mb-client-sheet-header svg {
        width: 1.35rem;
        height: 1.35rem;
        color: #6b7280;
    }
    .mb-client-sheet-split {
        display: grid;
        grid-template-columns: 1fr;
    }
    @media (min-width: 1024px) {
        .mb-client-sheet-split {
            grid-template-columns: 220px minmax(0, 1fr);
        }
    }
    .mb-client-nav {
        border-bottom: 1px solid #e5e7eb;
        padding: 0.75rem 0;
        background: #fafafa;
    }
    @media (min-width: 1024px) {
        .mb-client-nav {
            border-bottom: 0;
            border-right: 1px solid #e5e7eb;
            min-height: 36rem;
        }
    }
    .dark .mb-client-nav {
        background: #1f2937;
        border-color: #374151;
    }
    .mb-client-nav button {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 0.55rem 1.1rem;
        font-size: 0.86rem;
        color: #4b5563;
        background: transparent;
        border: 0;
        border-right: 3px solid transparent;
        text-align: left;
        cursor: pointer;
    }
    .mb-client-nav button.is-active {
        color: #059669;
        font-weight: 600;
        background: #ecfdf5;
        border-right-color: #059669;
    }
    .dark .mb-client-nav button {
        color: #d1d5db;
    }
    .dark .mb-client-nav button.is-active {
        color: #34d399;
        background: rgba(5, 150, 105, 0.12);
    }
    .mb-client-nav-badge {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: lowercase;
        color: #2563eb;
        background: #dbeafe;
        border-radius: 9999px;
        padding: 0.05rem 0.4rem;
    }
    .mb-client-sheet-main {
        padding: 1.15rem 1.25rem 0.5rem;
    }
    .mb-client-panel {
        min-height: 18rem;
        padding: 1.5rem 0.25rem;
        color: #6b7280;
        font-size: 0.9rem;
    }
    .mb-client-panel h3 {
        margin: 0 0 0.35rem;
        color: #111827;
        font-size: 1rem;
        font-weight: 700;
    }
    .dark .mb-client-panel h3 {
        color: #f9fafb;
    }
    .mb-client-panel-list {
        margin: 0.75rem 0 0;
        display: grid;
        gap: 0.5rem;
    }
    .mb-client-panel-list article {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.7rem 0.85rem;
        background: #fff;
    }
    .dark .mb-client-panel-list article {
        border-color: #374151;
        background: #111827;
    }
    .mb-client-sheet-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.6rem;
        padding: 0.9rem 1.25rem 1.1rem;
        border-top: 1px solid #e5e7eb;
        background: #fff;
    }
    .dark .mb-client-sheet-footer {
        border-top-color: #374151;
        background: #111827;
    }
    .mb-client-main .fi-fo-file-upload {
        margin-bottom: 0.5rem;
    }
</style>
