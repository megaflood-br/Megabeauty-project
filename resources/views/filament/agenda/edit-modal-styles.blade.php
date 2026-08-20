<style>
    .mb-agenda-edit-modal {
        overflow: hidden;
    }
    .mb-agenda-edit-modal .fi-modal-header {
        position: absolute;
        inset-inline-end: 0;
        top: 0;
        z-index: 30;
        padding: 0.85rem 1rem 0 0;
        background: transparent;
        width: auto;
    }
    .mb-agenda-edit-modal .fi-modal-heading,
    .mb-agenda-edit-modal .fi-modal-header h2 {
        display: none;
    }
    .mb-agenda-edit-modal .fi-modal-content {
        padding: 0;
        gap: 0;
        overflow: hidden;
    }
    .mb-agenda-edit-modal .fi-modal-footer {
        border-top: 1px solid #e5e7eb;
        background: #fff;
        padding: 0.85rem 1.25rem;
    }
    .dark .mb-agenda-edit-modal .fi-modal-footer {
        border-top-color: #374151;
        background: #111827;
    }
    .mb-agenda-edit-modal .fi-fo-grid {
        gap: 0;
    }
    .mb-agenda-client-pane {
        height: 100%;
    }
    .mb-agenda-client-pane .fi-fo-field-wrp {
        margin: 0;
    }
    .mb-client-card {
        background: #f3f4f6;
        border-right: 1px solid #e5e7eb;
        padding: 1.25rem 1.1rem 1.5rem;
        min-height: 36rem;
        height: 100%;
    }
    .dark .mb-client-card {
        background: #1f2937;
        border-right-color: #374151;
    }
    .mb-client-card-profile {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }
    .mb-client-card-avatar {
        width: 48px;
        height: 48px;
        border-radius: 9999px;
        background: #d1d5db;
        color: #374151;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .mb-client-card-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: #111827;
        line-height: 1.25;
    }
    .dark .mb-client-card-name {
        color: #f9fafb;
    }
    .mb-client-card-phone {
        font-size: 0.8rem;
        color: #6b7280;
        margin-top: 0.15rem;
    }
    .mb-client-card-whatsapp {
        margin-top: 0.9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        width: 100%;
        border-radius: 9999px;
        background: #16a34a;
        color: #fff;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.45rem 0.75rem;
        text-decoration: none;
    }
    .mb-client-card-whatsapp svg {
        width: 16px;
        height: 16px;
    }
    .mb-client-card-list {
        margin: 1.1rem 0 0;
        display: grid;
        gap: 0.7rem;
    }
    .mb-client-card-list div {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        font-size: 0.8rem;
    }
    .mb-client-card-list dt {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #4b5563;
        font-weight: 500;
    }
    .dark .mb-client-card-list dt {
        color: #d1d5db;
    }
    .mb-client-card-list dt svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }
    .mb-client-card-list dd {
        color: #111827;
        font-weight: 600;
        white-space: nowrap;
    }
    .dark .mb-client-card-list dd {
        color: #f9fafb;
    }
    .mb-client-card-section {
        margin-top: 1.15rem;
        padding-top: 0.85rem;
        border-top: 1px solid #e5e7eb;
    }
    .dark .mb-client-card-section {
        border-top-color: #374151;
    }
    .mb-client-card-section header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.78rem;
        font-weight: 700;
        color: #111827;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .dark .mb-client-card-section header {
        color: #f9fafb;
    }
    .mb-client-card-section header a {
        color: #059669;
        text-decoration: none;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0;
    }
    .mb-client-card-section p {
        margin: 0.35rem 0 0;
        font-size: 0.8rem;
        color: #6b7280;
    }
    .mb-agenda-edit-main {
        padding: 1.15rem 1.35rem 0.5rem;
    }
    .mb-agenda-edit-title {
        margin: 0 2rem 0.85rem 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: #111827;
    }
    .dark .mb-agenda-edit-title {
        color: #f9fafb;
    }
    .mb-agenda-items-head {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: #111827;
    }
    .dark .mb-agenda-items-head {
        color: #f9fafb;
    }
    .mb-agenda-items-cols {
        display: none;
        grid-template-columns: minmax(0, 5fr) minmax(0, 3fr) minmax(0, 2fr) minmax(0, 2fr) 2.25rem;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    @media (min-width: 768px) {
        .mb-agenda-items-cols {
            display: grid;
        }
    }
    .mb-agenda-edit-modal .mb-agenda-items .fi-fo-repeater-item {
        background: #fff;
        box-shadow: none;
        border: 1px solid #e5e7eb;
        padding: 0.55rem 0.65rem;
    }
    .dark .mb-agenda-edit-modal .mb-agenda-items .fi-fo-repeater-item {
        background: #111827;
        border-color: #374151;
    }
    .mb-agenda-color-option,
    .mb-agenda-status-option {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }
    .mb-agenda-color-dot,
    .mb-agenda-status-dot {
        width: 0.65rem;
        height: 0.65rem;
        border-radius: 9999px;
        display: inline-block;
    }
    .mb-agenda-status-dot.is-scheduled { background: #9ca3af; }
    .mb-agenda-status-dot.is-confirmed { background: #16a34a; }
    .mb-agenda-status-dot.is-in_progress { background: #f59e0b; }
    .mb-agenda-status-dot.is-completed { background: #059669; }
    .mb-agenda-status-dot.is-cancelled { background: #ef4444; }
    .mb-agenda-status-dot.is-no_show { background: #b91c1c; }
</style>
