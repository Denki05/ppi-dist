<style>
    /* Navbar with black background */
.navbar {
    background-color: #343a40!important; /* Black background */
    color: white !important; /* White text */
}

.navbar .navbar-brand, 
.navbar .nav-link, 
.navbar .dropdown-item {
    color: white !important; /* Make text white */
}

.navbar .navbar-toggler-icon {
    background-color: white; /* White icon for the toggler */
}

/* Hover and active states for links */
.navbar .nav-link:hover, 
.navbar .nav-link:focus,
.navbar .dropdown-item:hover,
.navbar .dropdown-item:focus {
    background-color: #444; /* Darker background on hover */
    color: white !important; /* Ensure text stays white */
}

.navbar .dropdown-menu {
    background-color: #343a40!important; /* Ensure dropdown has black background */
}

.navbar .dropdown-item:hover {
    background-color: #555; /* Darken hover effect for dropdown items */
}

/* Adjust color of any borders or dividers to match the black theme */
.dropdown-divider {
    border-color: #333; /* Darken the divider line */
}

/* Profile and Notification dropdown aligned to the right */
.navbar .navbar-nav.ml-auto {
    margin-left: auto; /* Align navbar items to the right */
}

.navbar .dropdown-menu {
    left: auto;
    right: 0;
}

/* Customize profile and notification icons */
.navbar .dropdown-menu .dropdown-item {
    display: flex;
    align-items: center;
}

/* Notification and profile icons */
.navbar .dropdown-item i {
    margin-right: 8px;
}

/* Center main menu items */
.navbar .navbar-nav.centered {
    margin-left: auto;
    margin-right: auto;
    text-align: center;
}

.navbar .navbar-nav.centered .nav-item {
    margin: 0 15px; /* Add spacing between items */
}

@media (min-width: 768px) {
    /* Profile and Notification icons on the right */
    .navbar .ml-auto .nav-item {
        margin-left: 20px;
    }

    /* Center the navbar items only on large screens */
    .navbar .navbar-nav.centered {
        display: flex;
        justify-content: center;
        flex-grow: 1;
    }
}

/* ===============================
   Notifikasi — prioritas, lonceng, popup tengah
   Mendesak #dc2626 · Perlu tindakan #d97706 · Informasi #2563eb
================================= */

:root {
    --nz-urgent: #dc2626;
    --nz-urgent-bg: #fef2f2;
    --nz-action: #d97706;
    --nz-action-bg: #fffbeb;
    --nz-info: #2563eb;
    --nz-info-bg: #eff6ff;
    --nz-ink: #0f172a;
    --nz-muted: #64748b;
    --nz-line: #eef2f7;
}

/* ---------- Lonceng ---------- */
.notification-wrapper {
    position: relative;
}

.notification-wrapper .notification-badge {
    position: absolute;
    top: 1px;
    right: 0;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    font-size: 11px;
    font-weight: 700;
    border-radius: 50px;
    background: var(--nz-info);
    color: #fff;
    display: none;
    align-items: center;
    justify-content: center;
    transform-origin: center;
}

.notification-wrapper .notification-badge.show {
    display: inline-flex;
}

.notification-badge.pulse {
    animation: nzPulse .45s ease-in-out 3;
}

.notif-bell-ring {
    display: inline-block;
    transform-origin: 50% 15%;
}

.notif-bell-ring.pulse {
    animation: nzRing 1.1s ease-in-out 3;
}

/* Lonceng berkedip terus selama masih ada notifikasi belum dibaca. */
.notif-bell-ring.blink {
    animation: nzBellBlink 1.5s ease-in-out infinite;
}

.notification-badge.blink {
    animation: nzBadgeBlink 1.5s ease-in-out infinite;
}

@keyframes nzPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.4); }
}

@keyframes nzRing {
    0%, 100% { transform: rotate(0); }
    20% { transform: rotate(13deg); }
    45% { transform: rotate(-11deg); }
    70% { transform: rotate(6deg); }
    85% { transform: rotate(-3deg); }
}

@keyframes nzBellBlink {
    0%, 100% { opacity: 1; transform: rotate(0); }
    50% { opacity: .35; transform: rotate(-12deg); }
}

@keyframes nzBadgeBlink {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.18); }
}

@media (prefers-reduced-motion: reduce) {
    .notification-badge.pulse,
    .notification-badge.blink,
    .notif-bell-ring.pulse,
    .notif-bell-ring.blink {
        animation: none;
    }
}

/* ---------- Panel lonceng (dropdown) ---------- */
.notif-panel {
    position: absolute;
    top: calc(100% + 12px);
    right: 0;
    z-index: 2040;
    width: 440px;
    max-width: calc(100vw - 16px);
    max-height: min(72vh, 620px);
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid #e7ecf3;
    border-radius: 18px;
    box-shadow: 0 28px 60px -16px rgba(15, 23, 42, .38), 0 2px 8px rgba(15, 23, 42, .06);
    overflow: hidden;
    text-align: left;
    color: var(--nz-ink);
    transform-origin: top right;
    animation: nzDrop .16s ease-out;
}

.notif-panel[hidden] {
    display: none;
}

.np-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 12px 10px 16px;
    background: linear-gradient(180deg, #f9fbfe 0%, #fff 100%);
}

.np-title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: -.01em;
    color: var(--nz-ink);
    line-height: 1.25;
}

.np-sub {
    margin-top: 2px;
    font-size: 11.5px;
    color: #8b95a5;
}

.np-acts {
    display: flex;
    align-items: center;
    gap: 2px;
    flex: 0 0 auto;
    margin-top: -2px;
}

.nz-ibtn {
    width: 32px;
    height: 32px;
    border: 1px solid transparent;
    background: transparent;
    color: #64748b;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    line-height: 1;
}

.nz-ibtn:hover {
    background: #eef2f7;
    color: #334155;
}

.nz-ibtn.on {
    color: var(--nz-info);
    background: var(--nz-info-bg);
}

.np-tabs {
    display: flex;
    gap: 18px;
    padding: 0 16px;
    border-bottom: 1px solid var(--nz-line);
    overflow-x: auto;
    scrollbar-width: none;
}

.np-tabs::-webkit-scrollbar {
    display: none;
}

.np-tab {
    position: relative;
    flex: 0 0 auto;
    border: 0;
    background: none;
    padding: 9px 0 10px;
    font-size: 12.5px;
    font-weight: 600;
    color: #8592a3;
    white-space: nowrap;
    transition: color .15s ease;
}

.np-tab:hover {
    color: #334155;
}

.np-tab.active {
    color: var(--nz-ink);
}

.np-tab.active::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: -1px;
    height: 2px;
    border-radius: 2px;
    background: var(--nz-ink);
}

.np-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-width: thin;
    scrollbar-color: #cfd6de transparent;
    background: #fff;
}

.np-body::-webkit-scrollbar {
    width: 8px;
}

.np-body::-webkit-scrollbar-thumb {
    background: #cfd6de;
    border-radius: 8px;
}

/* Pemisah hari di daftar */
.np-day {
    position: sticky;
    top: 0;
    z-index: 2;
    padding: 7px 16px 5px;
    background: #fff;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #9aa6b4;
    border-bottom: 1px solid #f6f8fb;
}

/* Baris notifikasi */
.notification-item {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 11px;
    padding: 12px 12px 12px 16px;
    border-bottom: 1px solid #f4f7fa;
    background: #fff;
    cursor: pointer;
    transition: background .14s ease;
}

.notification-item:hover {
    background: #f8fafc;
}

.notification-item.unread {
    background: #fbfdff;
}

.notification-item.unread:hover {
    background: #f4f8fd;
}

/* Aksen kiri hanya untuk yang belum dibaca */
.notification-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: transparent;
}

.notification-item.unread::before {
    background: var(--nz-info);
}

.notification-item.unread[data-priority="urgent"]::before {
    background: var(--nz-urgent);
}

.notification-item.unread[data-priority="action"]::before {
    background: var(--nz-action);
}

.notification-item.is-read .notif-title {
    font-weight: 600;
    color: #475569;
}

.notification-item.is-read .notif-icon {
    opacity: .72;
}

.notif-icon {
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    background: #f1f5f9;
    color: #64748b;
    margin-top: 1px;
}

.notification-item[data-priority="urgent"] .notif-icon {
    background: #fef2f2;
    color: #b91c1c;
}

.notification-item[data-priority="action"] .notif-icon {
    background: #fffbeb;
    color: #b45309;
}

.notification-item[data-priority="info"] .notif-icon {
    background: #eff6ff;
    color: #1d4ed8;
}

.notif-main {
    flex: 1 1 auto;
    min-width: 0;
}

.notif-title {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0 5px;
    font-size: 13px;
    font-weight: 700;
    color: var(--nz-ink);
    line-height: 1.45;
}

.notif-sub {
    font-size: 11.8px;
    color: var(--nz-muted);
    margin-top: 1px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.notif-code {
    display: inline-block;
    padding: 0 6px;
    border-radius: 5px;
    background: #eef1f5;
    color: #475569;
    font-size: 10.5px;
    font-weight: 700;
    vertical-align: 1px;
}

.notif-otp {
    display: inline-flex;
    align-items: center;
    margin-top: 5px;
    padding: 1px 9px;
    border-radius: 7px;
    background: #fef2f2;
    color: #b91c1c;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 3px;
    line-height: 1.6;
}

.nz-flag {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 1px 7px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 700;
    vertical-align: 1px;
    white-space: nowrap;
}

.nz-flag.urgent {
    background: #fee2e2;
    color: #b91c1c;
}

.nz-flag.action {
    background: #fef3c7;
    color: #b45309;
}

.notif-meta {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 4px;
    font-size: 10.8px;
    color: #98a2b3;
}

.notif-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--nz-info);
    flex: 0 0 7px;
    margin: 7px 2px 0 0;
}

.notification-item[data-priority="urgent"] .notif-dot {
    background: var(--nz-urgent);
}

.notification-item[data-priority="action"] .notif-dot {
    background: var(--nz-action);
}

.notif-quick {
    flex: 0 0 auto;
    align-self: center;
    border: none;
    background: #fff;
    color: #64748b;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .15s ease, background .15s ease;
}

.notification-item:hover .notif-quick,
.notification-item:focus-within .notif-quick {
    opacity: 1;
}

.notif-quick:hover {
    background: #e8eef6;
    color: var(--nz-info);
}

@media (hover: none) {
    .notif-quick {
        opacity: 1;
    }
}

.notif-loadmore {
    width: 100%;
    border: none;
    border-top: 1px solid var(--nz-line);
    background: #f8fafc;
    color: #374151;
    font-size: 12.5px;
    font-weight: 600;
    padding: 10px;
}

.notif-loadmore:hover {
    background: #f1f5f9;
}

.notif-empty {
    padding: 42px 16px;
    text-align: center;
    color: #94a3b8;
    font-size: 12.5px;
}

.notif-empty i {
    display: block;
    font-size: 26px;
    margin-bottom: 7px;
    opacity: .5;
}

.np-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 14px;
    border-top: 1px solid var(--nz-line);
    background: #fff;
}

.np-foot .nz-hint {
    font-size: 11.5px;
    color: #94a3b8;
}

.np-foot a {
    font-size: 12.5px;
    font-weight: 600;
    color: var(--nz-info);
    text-decoration: none;
}

.np-foot a:hover {
    text-decoration: underline;
}

.nz-sound-warn {
    padding: 7px 14px;
    background: #fffbeb;
    border-top: 1px solid #fde68a;
    color: #92400e;
    font-size: 11.5px;
    display: none;
}

body.nz-audio-locked .nz-sound-warn {
    display: block;
}

/* ---------- Chip suara tertahan (browser butuh sentuhan) ---------- */
.nz-audio-chip {
    position: fixed;
    left: 50%;
    bottom: 18px;
    z-index: 2035;
    transform: translateX(-50%);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border: 1px solid #fde68a;
    border-radius: 999px;
    background: #fffbeb;
    color: #92400e;
    font-size: 12.5px;
    font-weight: 600;
    line-height: 1;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .16);
    cursor: pointer;
    animation: nzUp .24s ease-out;
}

.nz-audio-chip[hidden] {
    display: none;
}

.nz-audio-chip:hover {
    background: #fef3c7;
}

.nz-audio-chip i {
    font-size: 15px;
}

/* HP: panel jadi lembar bawah yang mudah dijangkau */
@media (max-width: 991.98px) {
    .notif-panel {
        position: fixed;
        top: auto;
        left: 10px;
        right: 10px;
        bottom: 10px;
        width: auto;
        max-width: none;
        max-height: min(80vh, 640px);
        border-radius: 18px;
        transform-origin: bottom right;
        animation: nzUp .2s ease-out;
    }

    .notification-item {
        padding: 13px 12px 13px 16px;
    }
}

/* ---------- Modal pengingat (tengah / bottom sheet) ---------- */
.nzrem-backdrop {
    position: fixed;
    inset: 0;
    z-index: 2060;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(4, 10, 24, .55);
    -webkit-backdrop-filter: blur(4px);
    backdrop-filter: blur(4px);
    animation: nzFadeIn .16s ease-out;
}

.nzrem-backdrop[hidden] {
    display: none;
}

.nzrem {
    position: relative;
    width: 500px;
    max-width: 100%;
    max-height: min(86vh, 620px);
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 40px 90px -20px rgba(2, 6, 23, .55);
    overflow: hidden;
    animation: nzPop .2s cubic-bezier(.2, .8, .3, 1);
}

.nzrem-grip {
    display: none;
    width: 38px;
    height: 4px;
    border-radius: 4px;
    background: #e2e8f0;
    margin: 8px auto 0;
}

.nzrem-accent {
    flex: 0 0 4px;
    height: 4px;
    background: linear-gradient(90deg, #60a5fa, #2563eb);
}

.nzrem[data-priority="urgent"] .nzrem-accent {
    background: linear-gradient(90deg, #f87171, #dc2626);
}

.nzrem[data-priority="action"] .nzrem-accent {
    background: linear-gradient(90deg, #fbbf24, #d97706);
}

.nzrem-head {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px 16px 12px;
}

.nzrem-ico {
    position: relative;
    flex: 0 0 44px;
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    background: #eff6ff;
    color: #1d4ed8;
}

.nzrem[data-priority="urgent"] .nzrem-ico {
    background: #fef2f2;
    color: #b91c1c;
}

.nzrem[data-priority="action"] .nzrem-ico {
    background: #fffbeb;
    color: #b45309;
}

.nzrem-ico-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    min-width: 19px;
    height: 19px;
    padding: 0 5px;
    border-radius: 999px;
    background: #dc2626;
    color: #fff;
    font-size: 10.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
}

.nzrem-titles {
    flex: 1 1 auto;
    min-width: 0;
}

.nzrem-eyebrow {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #98a2b3;
}

.nzrem-title {
    margin-top: 1px;
    font-size: 16.5px;
    font-weight: 700;
    letter-spacing: -.01em;
    color: var(--nz-ink);
    line-height: 1.25;
}

.nzrem-sub {
    margin-top: 3px;
    font-size: 12px;
    color: #8b95a5;
}

.nzrem-x {
    flex: 0 0 30px;
    width: 30px;
    height: 30px;
    border: 0;
    border-radius: 9px;
    background: #f4f6f9;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}

.nzrem-x:hover {
    background: #e9edf2;
    color: #334155;
}

.nzrem-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-width: thin;
    scrollbar-color: #cfd6de transparent;
}

.nzrem-body::-webkit-scrollbar {
    width: 8px;
}

.nzrem-body::-webkit-scrollbar-thumb {
    background: #cfd6de;
    border-radius: 8px;
}

.nzrem-item {
    display: flex;
    gap: 10px;
    padding: 11px 16px;
    border-top: 1px solid #f4f7fa;
    cursor: pointer;
    transition: background .14s ease;
}

.nzrem-item:first-child {
    border-top: 0;
}

.nzrem-item:hover {
    background: #f8fafc;
}

.nzrem-item.is-read {
    opacity: .62;
}

.nzrem-bullet {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex: 0 0 8px;
    margin-top: 6px;
    background: var(--nz-info);
}

.nzrem-item[data-priority="urgent"] .nzrem-bullet {
    background: var(--nz-urgent);
    box-shadow: 0 0 0 3px rgba(220, 38, 38, .12);
}

.nzrem-item[data-priority="action"] .nzrem-bullet {
    background: var(--nz-action);
    box-shadow: 0 0 0 3px rgba(217, 119, 6, .12);
}

.nzrem-item.is-read .nzrem-bullet {
    background: #cbd5e1;
    box-shadow: none;
}

.nzrem-item-main {
    flex: 1 1 auto;
    min-width: 0;
}

.nzrem-item-title {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0 5px;
    font-size: 12.8px;
    font-weight: 700;
    color: var(--nz-ink);
    line-height: 1.4;
}

.nzrem-item-sub {
    margin-top: 1px;
    font-size: 11.6px;
    color: var(--nz-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.nzrem-item-when {
    flex: 0 0 auto;
    margin-top: 3px;
    font-size: 10.5px;
    color: #9aa6b4;
    white-space: nowrap;
}

.nzrem-more {
    display: flex;
    gap: 9px;
    align-items: flex-start;
    margin: 4px 16px 12px;
    padding: 10px 12px;
    border: 1px solid #eef2f7;
    border-radius: 12px;
    background: #f8fafc;
    font-size: 11.8px;
    line-height: 1.55;
    color: #475569;
}

.nzrem-more > i {
    color: #94a3b8;
    font-size: 14px;
    margin-top: 1px;
}

.nzrem-more b {
    color: var(--nz-ink);
}

.nzrem-more-links {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 7px;
}

.nzrem-more-links button,
.nzrem-more-links a {
    border: 1px solid #dbe3ec;
    background: #fff;
    color: #1d4ed8;
    font-size: 11.5px;
    font-weight: 600;
    border-radius: 8px;
    padding: 3px 9px;
    line-height: 1.5;
    text-decoration: none;
}

.nzrem-more-links button:hover,
.nzrem-more-links a:hover {
    background: #eff6ff;
    border-color: #c7dbfc;
}

.nzrem-empty {
    padding: 34px 16px;
    text-align: center;
    color: #94a3b8;
    font-size: 12.5px;
}

.nzrem-foot {
    display: flex;
    gap: 8px;
    padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
    border-top: 1px solid var(--nz-line);
    background: #fff;
}

.nzrem-btn {
    flex: 1 1 0;
    height: 40px;
    border-radius: 11px;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border: 1px solid transparent;
}

.nzrem-btn.ghost {
    background: #fff;
    border-color: #e2e8f0;
    color: #475569;
}

.nzrem-btn.ghost:hover {
    background: #f8fafc;
}

.nzrem-btn.primary {
    background: #0f172a;
    color: #fff;
}

.nzrem-btn.primary:hover {
    background: #1e293b;
}

@media (max-width: 575.98px) {
    .nzrem-backdrop {
        padding: 0;
        align-items: flex-end;
        background: rgba(4, 10, 24, .5);
    }

    .nzrem {
        width: 100%;
        max-width: none;
        max-height: 88vh;
        border-radius: 20px 20px 0 0;
        animation: nzUp .22s ease-out;
    }

    .nzrem-grip {
        display: block;
    }

    .nzrem-head {
        padding-top: 12px;
    }

    .nzrem-title {
        font-size: 17px;
    }
}

@keyframes nzFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes nzPop {
    from { opacity: 0; transform: translateY(10px) scale(.98); }
    to { opacity: 1; transform: none; }
}

@keyframes nzDrop {
    from { opacity: 0; transform: translateY(-6px) scale(.99); }
    to { opacity: 1; transform: none; }
}

@keyframes nzUp {
    from { opacity: .6; transform: translateY(24px); }
    to { opacity: 1; transform: none; }
}

</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">UNIFRA</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav centered">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('superuser.index') }}">
                        <i class="fa-solid fa-house"></i> Dashboard
                    </a>
                </li>

                <!-- Master -->
                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-folder-open"></i> Master
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('superuser.master.warehouse.index') }}"><i class="fa-solid fa-warehouse"></i> Gudang</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.master.contact.index') }}"><i class="fa-solid fa-address-book"></i> Kontak</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.master.mitra.index') }}"><i class="fa-solid fa-code-branch"></i> Mitra</a></li>
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-copyright"></i> Produk
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.master.product.index') }}"><i class="fa-solid fa-list"></i> List</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.master.sh_produk.index') }}"><i class="fa-solid fa-certificate"></i> SH Produk</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.master.product_category.index') }}"><i class="fa-solid fa-layer-group"></i> Kategori</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.master.sub_brand_reference.index') }}"><i class="fa-solid fa-timeline"></i> Searah</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.master.product_type.index') }}"><i class="fa-solid fa-file-lines"></i> Tipe</a></li>
                            </ul>
                        </li>
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-store"></i> Customer
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.master.customer.index') }}"><i class="fa-solid fa-list"></i> List</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.master.customer_category.index') }}"><i class="fa-solid fa-layer-group"></i> Kategori</a></li>
                            </ul>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('superuser.master.vendor.index') }}"><i class="fa-solid fa-building-columns"></i> Vendor</a></li>
                    </ul>
                </li>
                @endif

                <!-- Penjualan -->
                @if(!in_array($superuser->id, [41, 29]))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-basket-shopping"></i> Penjualan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('superuser.finance.invoicing.index') }}"><i class="fa-solid fa-warehouse"></i> Invoice</a></li>
                        @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                        <li><a class="dropdown-item" href="{{ route('superuser.master.contact.index') }}"><i class="fa-solid fa-address-book"></i> Pengaturan Harga</a></li>
                        @endif
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-cart-plus"></i> Sales Order
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order_indent.index') }}"><i class="fa-solid fa-indent"></i> Indent</a></li>
                                @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer")
                                <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order_kontrak.index') }}"><i class="fa-solid fa-file-signature"></i> Kontrak</a></li>
                                @endif
                                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                                <li><a class="dropdown-item" href="{{ route('superuser.penjualan.so_proforma.index') }}"><i class="fa-solid fa-file-prescription"></i> Proforma</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.penjualan.pengajuan_proforma.index') }}"><i class="fa-solid fa-file-circle-plus"></i> Pengajuan Proforma</a></li>
                                @endif
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-file-invoice"></i> Regular</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order.index_awal') }}"><i class="fa-solid fa-arrow-up-wide-short"></i> Awal</a></li>
                                        @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                                        <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}"><i class="fa-solid fa-arrow-down-wide-short"></i> Lanjutan</a></li>
                                        @endif
                                    </ul>
                                </li>
                                @if(
                                    $superuser->can('superuser-manage') || 
                                    $superuser->division == "Admin" || 
                                    $superuser->division == "Management" || 
                                    $superuser->id == 38 ||
                                    $superuser->id == 35
                                )
                                    <li class="submenu submenu-md dropend">
                                        <a class="dropdown-item dropdown-toggle" role="button"
                                            data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-file-invoice"></i> PPN</a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order_ppn.index_ppn_awal') }}"><i class="fa-solid fa-arrow-up-wide-short"></i> Awal</a></li>
                                            @if(
                                                $superuser->can('superuser-manage') || 
                                                $superuser->division == "Admin" || 
                                                $superuser->division == "Management"
                                            )
                                            <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order_ppn.index_ppn_lanjutan') }}"><i class="fa-solid fa-arrow-down-wide-short"></i> Lanjutan</a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        
                        @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                        <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sale_return.index') }}"><i class="fa-solid fa-file-prescription"></i> Nota Kredit</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                <!-- Gudang -->
                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse"  OR $superuser->division == "Finance")
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-boxes-stacked"></i> Logistik
                    </a>
                    <ul class="dropdown-menu">

                        {{-- ================= DOCUMENT ================= --}}
                        @if(
                            ($superuser->can('superuser-manage') 
                            OR $superuser->division == "Admin" 
                            OR $superuser->division == "Management" 
                            OR $superuser->division == "Warehouse" 
                            OR $superuser->division == "Finance")
                            AND !in_array($superuser->id, [29, 41])
                        )
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-file-lines"></i> Document
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.purchase_order_spk.index') }}">
                                        Order Industri (SPK)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.purchase_order.index') }}">
                                        Order Pusat (PO)
                                    </a>
                                </li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">Mutasi</a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('superuser.gudang.mutasi_out.index') }}">
                                                Gudang Utama
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('superuser.gudang.mutasi_showroom.index') }}">
                                                Showroom
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        @endif


                        {{-- ================= WAREHOUSE ================= --}}
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-warehouse"></i> Warehouse
                            </a>
                            <ul class="dropdown-menu">
                                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse" OR $superuser->division == "Finance")
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.penjualan.delivery_order.index') }}">
                                        Checker Transaksi (DO)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.sj_mutasi_internal.index') }}">
                                        Checker Mutasi
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.receiving.index') }}">
                                        Receiving
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.quality_control.index') }}">
                                        Receiving - Transaksi
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.quality_control_2.index') }}">
                                        Receiving - Komplain
                                    </a>
                                </li>
                                
                                @endif
                            </ul>
                        </li>


                        {{-- ================= INVENTORY ================= --}}
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-cubes"></i> Inventory
                            </a>
                            <ul class="dropdown-menu">
                                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse" OR $superuser->division == "Finance")
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.stock.index') }}">
                                        Stock
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.stock_adjustment.index') }}">
                                        Stock Adjustment
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>

                    </ul>
                </li>
                @endif

                <!-- FAT -->
                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Finance")
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-chart-line"></i> FAT
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('superuser.finance.payable.index') }}"><i class="fa-solid fa-credit-card"></i> Payment</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.finance.nota_kredit_finance.index') }}"><i class="fa-solid fa-file-prescription"></i> Nota TT</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.finance.nota_kredit_finance.refund_page') }}"><i class="fa-solid fa-file-prescription"></i> Refund</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.finance.cashback.index') }}"><i class="fa-solid fa-code-branch"></i> Araya</a></li>
                        <!-- <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-chart-simple"></i> Accounting
                            </a>
                            <ul class="dropdown-menu">
                                <li class=s"ubmenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-code-branch"></i> Unifra</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.accounting.invoice_tax.index_jual') }}">Jual</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.accounting.invoice_tax.index_beli') }}">Beli</a></li>
                                    </ul>
                                </li>
                                
                            </ul>
                        </li> -->
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-repeat"></i> CV
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.accounting.product_finance.index') }}"><i class="fa-solid fa-copyright"></i> Product UV</a></li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"> Araya</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.accounting.finance_simulation.index_araya') }}">Invoice</a></li>
                                        <li><a class="dropdown-item" href="">Payment</a></li>
                                        <li><a class="dropdown-item" href="">Report</a></li>
                                    </ul>
                                </li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"> Unifra</a>
                                    <ul class="dropdown-menu">
                                        <!-- <li><a class="dropdown-item" href="">Set Mitra</a></li> -->
                                        <li><a class="dropdown-item" href="{{ route('superuser.accounting.finance_simulation.index_mitra') }}">Invoice</a></li>
                                        <li><a class="dropdown-item" href="">Payment</a></li>
                                        <li><a class="dropdown-item" href="">Report</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                @endif

                <!-- Laporan -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-file-export"></i> Laporan
                    </a>
                    <ul class="dropdown-menu">
                        @if($superuser->division == "Developer" OR $superuser->division == "Management" OR $superuser->division == "Finance")
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-chart-simple"></i> Accounting
                            </a>
                            <ul class="dropdown-menu">
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-code-branch"></i> Unifra Report</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.accounting.invoice_tax.pageReportJual') }}">Jual</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.accounting.invoice_tax.pageReportBeli') }}">Beli</a></li>
                                    </ul>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('superuser.report.revenue.index') }}"> Laporan Pendapatan</a></li>
                            </ul>
                        </li>
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-coins"></i> Finance
                            </a>
                            <ul class="dropdown-menu">
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-code-branch"></i> Araya Report</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.finance.cashback.pageReport') }}">Cashback</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.finance.cashback.pageReportBeli') }}">Beli</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.finance.cashback.pageReportJual') }}">Jual</a></li>
                                    </ul>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('superuser.finance.payable.pageReport') }}">Laporan Pembayaran</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.finance.invoicing.pageReport') }}">Piutang Faktur</a></li>
                            </ul>
                        </li>
                        @endif
                        @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer" OR $superuser->division == "Finance")
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-file-import"></i> Operasional
                            </a>
                            <ul class="dropdown-menu">
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">Customer</a>
                                    <ul class="dropdown-menu">
                                        <!-- <li><a class="dropdown-item" href="{{ route('superuser.report.customer_order_variant_v2.index') }}">Customer - Produk</a></li> -->
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.customer_order_variant.index') }}">Customer History</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.customer_type_zone.index') }}">Customer - Zoning</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.summary_customer_product.index') }}">Summary Customer - Produk</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.sales.index') }}">Penjualan</a></li>
                                    </ul>
                                </li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">Produk</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.product_performance.index') }}">Produk - Customer</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.product_high_sell.index') }}">Produk Penjualan Tertinggi</a></li>
                                        @if($superuser->division == "Management" OR $superuser->division == "Developer")
                                        <li><a class="dropdown-item" href="{{ route('superuser.master.product.pageReport') }}">Produk - Material</a></li>
                                        @endif
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @if($superuser->division == "Developer" OR $superuser->division == "Management")
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-file-import"></i> Management
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.report.forecast_supplier.index') }}">Forecasting Principle</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.report.customer_type_brand.index') }}">Register Customer</a></li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">Penjualan Sales</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.employee_performance.index') }}">Omset Sales</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.employee_performance_product.index') }}">Kinerja Sales</a></li>
                                    </ul>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('superuser.report.customer_type_brand_uv.index') }}">UV</a></li>
                            </ul>
                        </li>
                        @endif
                    </ul>
                </li>

                <!-- Setting -->
                @if($superuser->canAny(['superuser-manage', 'salesperson-manage']))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-gears"></i> Pengaturan
                    </a>
                    <ul class="dropdown-menu">
                        @if($superuser->can('superuser-manage'))
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-user-gear"></i> Akun
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.account.log_activity.index') }}">Aktivitas Log</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.account.user.index') }}">Akun</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.account.sales_person.index') }}">Sales Person</a></li>
                            </ul>
                        </li>
                        @endif
                        @role('Developer|SuperAdmin', 'superuser')
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-bars"></i> Menu
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.setting.menu.index') }}">Daftar Menu</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.utility.settings.index') }}">Maintenance Mode</a></li>
                            </ul>
                        </li>
                        @endrole
                        @role('Developer|SuperAdmin', 'superuser')
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-brands fa-connectdevelop"></i> Zona Pengembang
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.backup.index') }}">Backup DB</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.boilerplate.index') }}">Boilrplate</a></li>
                                <li><a class="dropdown-item" href="{{ url('superuser/telescope') }}">Telescope</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.terminal') }}">Terminal</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.gate.index') }}">Gate (Authorization)</a></li>
                            </ul>
                        </li>
                        @endrole
                    </ul>
                </li>
                @endif
            </ul>
        <ul class="navbar-nav ml-auto"> <!-- ml-auto aligns to the right -->
        <!-- Notification Popup -->
        <li class="nav-item notification-wrapper">
            <a class="nav-link position-relative" href="#" id="notifBell" role="button"
               aria-haspopup="dialog" aria-expanded="false" aria-label="Notifikasi">
                <span class="notif-bell-ring {{ $notifCount > 0 ? 'blink' : '' }}" id="notifBellRing"><i class="bi bi-bell fs-5"></i></span>
                <span id="notifCount"
                    class="notification-badge {{ $notifCount > 0 ? 'show blink' : '' }}">
                    {{ $notifCount > 99 ? '99+' : $notifCount }}
                </span>
            </a>

            {{-- Panel notifikasi: dropdown di bawah lonceng --}}
            <div class="notif-panel" id="notifPanel" role="dialog" aria-label="Daftar notifikasi" hidden>
                <div class="np-head">
                    <div>
                        <div class="np-title">Notifikasi</div>
                        <div class="np-sub" id="notifMSub">Memuat&hellip;</div>
                    </div>
                    <div class="np-acts">
                        <button type="button" class="nz-ibtn" id="notifTestSound" title="Tes suara notifikasi">
                            <i class="bi bi-volume-up"></i>
                        </button>
                        <button type="button" class="nz-ibtn" id="notifEnableOs" title="Aktifkan notifikasi desktop" hidden>
                            <i class="bi bi-display"></i>
                        </button>
                        <button type="button" class="nz-ibtn {{ $notifCount > 0 ? '' : 'd-none' }}" id="markAllAsReadBtn"
                                title="Tandai semua dibaca">
                            <i class="bi bi-check2-all"></i>
                        </button>
                        <button type="button" class="nz-ibtn" id="notifClose" title="Tutup">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="np-tabs" id="notifFilters">
                    <button type="button" class="np-tab active" data-type="">Semua</button>
                    <button type="button" class="np-tab" data-type="so">Sales Order</button>
                    <button type="button" class="np-tab" data-type="do">Delivery Order</button>
                    <button type="button" class="np-tab" data-type="pay">Keuangan</button>
                    <button type="button" class="np-tab" data-type="otp">Revisi</button>
                </div>

                <div id="notifList" class="np-body"></div>

                <button type="button" id="notifLoadMore" class="notif-loadmore" hidden>
                    Muat lebih banyak
                </button>

                <div class="nz-sound-warn">
                    <i class="bi bi-volume-mute"></i> Suara belum aktif. Klik sekali di halaman, lalu tekan tombol tes suara.
                </div>

                <div class="np-foot">
                    <span class="nz-hint" id="notifTotalInfo"></span>
                    <a href="{{ route('superuser.penjualan.notification.index') }}">
                        Arsip notifikasi <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
        </li>

        <!-- Profile Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> {{ $superuser->username }}
          </a>
          <ul class="dropdown-menu" aria-labelledby="profileDropdown">
            <li><a class="dropdown-item" href="{{ route('superuser.profile.index') }}">My Profile</a></li>
            <li><a class="dropdown-item" href="{{ route('superuser.logout') }}">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

{{-- Pengingat mendesak: menetap sampai user membukanya --}}
<div id="notifBanner" role="status" aria-live="polite" hidden>
    <span class="nz-banner-dot"></span>
    <span class="nz-banner-text"><b id="notifBannerCount">0</b> notifikasi mendesak menunggu tindakan Anda</span>
    <button type="button" class="nz-banner-open" id="notifBannerOpen">Buka</button>
    <button type="button" class="nz-banner-close" id="notifBannerClose" aria-label="Sembunyikan untuk halaman ini">&times;</button>
</div>

{{-- Modal pengingat: hanya notifikasi hari ini & kemarin --}}
<div class="nzrem-backdrop" id="notifReminder" hidden>
    <div class="nzrem" role="dialog" aria-modal="true" aria-labelledby="nzremTitle" data-priority="info">
        <div class="nzrem-grip"></div>
        <div class="nzrem-accent"></div>

        <div class="nzrem-head">
            <div class="nzrem-ico">
                <i class="bi bi-bell-fill"></i>
                <span class="nzrem-ico-badge" id="nzremBadge" hidden>0</span>
            </div>
            <div class="nzrem-titles">
                <div class="nzrem-eyebrow">Pengingat</div>
                <div class="nzrem-title" id="nzremTitle">Notifikasi terbaru</div>
                <div class="nzrem-sub" id="nzremSub">Hari ini &amp; kemarin</div>
            </div>
            <button type="button" class="nzrem-x" id="notifReminderClose" aria-label="Tutup pengingat">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="nzrem-body" id="notifReminderList"></div>

        <div class="nzrem-more" id="notifReminderMore" hidden></div>

        <div class="nzrem-foot">
            <button type="button" class="nzrem-btn ghost" id="notifReminderLater">Nanti saja</button>
            <button type="button" class="nzrem-btn primary" id="notifReminderOpenAll">
                Buka daftar <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {

    let notifInterval = null;
    let currentRequest = null;

    const seenNotifIds = new Set();
    let notifFirstLoad = true;
    let notifAudioCtx = null;
    let notifPendingSound = '';
    let notifPendingSeenAt = '';
    let notifAudioUnlocking = false;

    const NZ_COLORS = { urgent: '#dc2626', action: '#d97706', info: '#2563eb' };
    const NZ_LABELS = { urgent: 'Mendesak', action: 'Perlu tindakan', info: 'Informasi' };
    const NZ_RANK = { urgent: 0, action: 1, info: 2 };
    const NZ_ICON = '{{ asset('superuser_assets/media/logo_ppi.png') }}';

    function priorityRank(priority) {
        return NZ_RANK[priority] == null ? 9 : NZ_RANK[priority];
    }

    // Penanda "sudah ditutup sampai waktu ini" memakai created_at (bukan id,
    // karena id notifikasi berupa UUID).
    // Disimpan di sessionStorage: otomatis hilang saat tab ditutup, jadi
    // pengingat muncul lagi ketika user membuka tab baru.
    const REMINDER_KEY = 'notif_reminder_seen_at';
    const SOUND_KEY = 'notif_sound_seen_at';
    const NZ_SESSION_KEY = 'notif_browser_session';
    const NZ_SESSION_MARK = @json(session()->getId());
    const nzMemStore = {};
    let lastUnreadMaxAt = '';

    function storageGet(key) {
        try {
            const value = sessionStorage.getItem(key);
            return value == null ? (nzMemStore[key] || '') : value;
        } catch (e) {
            return nzMemStore[key] || '';
        }
    }

    function storageSet(key, value) {
        nzMemStore[key] = value;
        try { sessionStorage.setItem(key, value); } catch (e) {}
    }

    // Logout/login di tab yang sama juga harus memunculkan pengingat lagi.
    // Sesi Laravel berubah saat login/logout, jadi penanda lama dibuang.
    function sessionReady() {
        if (!NZ_SESSION_MARK) return true;
        if (storageGet(NZ_SESSION_KEY) === NZ_SESSION_MARK) return true;
        try {
            sessionStorage.removeItem(REMINDER_KEY);
            sessionStorage.removeItem(SOUND_KEY);
        } catch (e) {}
        delete nzMemStore[REMINDER_KEY];
        delete nzMemStore[SOUND_KEY];
        storageSet(NZ_SESSION_KEY, NZ_SESSION_MARK);
        return false;
    }

    function reminderSeenAt() { return sessionReady() ? storageGet(REMINDER_KEY) : ''; }

    function rememberReminderClosed() {
        if (lastUnreadMaxAt) storageSet(REMINDER_KEY, lastUnreadMaxAt);
    }

    function soundSeenAt() { return sessionReady() ? storageGet(SOUND_KEY) : ''; }

    function rememberSound(at) {
        if (at) storageSet(SOUND_KEY, at);
    }

    /* ================================
       SUBMENU AUTO COLLAPSE
    ================================= */
    const autoCollapseSubmenu = (event) => {

        if (!event.target.matches('li.submenu>a.dropdown-item.dropdown-toggle')) return;

        event.stopPropagation();

        const navbar = event.target.closest('nav.navbar');
        const targetSubmenu = event.target.parentElement.querySelector('ul.dropdown-menu');

        if (!targetSubmenu) return;

        navbar.querySelectorAll('li.submenu>ul.dropdown-menu.show')
            .forEach((subMenu) => {

                if (!subMenu.contains(targetSubmenu)) {

                    subMenu.classList.remove('show');

                    const toggle = subMenu.parentElement
                        .querySelector('a[aria-expanded="true"].dropdown-item.dropdown-toggle');

                    if (toggle) {
                        toggle.classList.remove('show');
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });
    };

    document.querySelectorAll('nav.navbar')
        .forEach(navbar => navbar.addEventListener('click', autoCollapseSubmenu));


    /* ================================
       NOTIFICATION CORE
    ================================= */

    function esc(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    // Nilai data-type pada tab filter dipetakan ke FQCN notifikasi.
    // Tab "so" sengaja memakai kunci grup supaya SO dan Pengajuan Proforma
    // (dua-duanya pekerjaan admin sales) muncul di satu tab.
    const NOTIF_TYPES = {
        so: 'so',
        do: 'App\\Notifications\\DoNotification',
        pay: 'App\\Notifications\\PayableNotification',
        otp: 'App\\Notifications\\InternalRevisionOtpNotification'
    };

    const NZ_MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    const NZ_REMINDER_TITLE = {
        urgent: 'Ada yang mendesak',
        action: 'Menunggu tindakan Anda',
        info: 'Notifikasi terbaru'
    };

    // Halaman arsip tidak perlu pengingat otomatis.
    const NZ_ARCHIVE_PAGE = /\/notification(\/|$)/.test(window.location.pathname);

    function notifMeta(type) {
        if (type.includes('PengajuanProforma')) return { label: 'Pengajuan Proforma', short: 'Pengajuan', icon: 'bi-file-earmark-plus' };
        if (type.includes('DoNotification')) return { label: 'Delivery Order', short: 'DO', icon: 'bi-truck' };
        if (type.includes('SoNotification')) return { label: 'Sales Order', short: 'SO', icon: 'bi-cart' };
        if (type.includes('PayableNotification')) return { label: 'Payment', short: 'Keuangan', icon: 'bi-credit-card' };
        if (type.includes('ReceivingNotification')) return { label: 'Receiving', short: 'Receiving', icon: 'bi-box-seam' };
        if (type.includes('InternalRevisionOtp')) return { label: 'OTP Revisi', short: 'Revisi', icon: 'bi-key' };
        return { label: 'Notifikasi', short: 'Info', icon: 'bi-bell' };
    }

    // Prioritas datang dari server; cadangan lokal supaya baris lama tetap
    // punya warna walau payload-nya belum memuat field priority.
    function nzPriority(n) {
        const p = n && n.priority;
        if (p === 'urgent' || p === 'action' || p === 'info') return p;
        const type = (n && n.type) || '';
        if (type.includes('InternalRevisionOtp')) return 'urgent';
        if (type.includes('PengajuanProforma')) return 'action';
        if (type.includes('SoNotification') || type.includes('PayableNotification')) return 'action';
        return 'info';
    }

    function notifReadUrl(id) {
        return '{{ route('superuser.penjualan.notification.read', ['id' => '__NID__']) }}'.replace('__NID__', id);
    }

    function topPriority(list) {
        let top = 'info';
        list.forEach(function (n) {
            const p = nzPriority(n);
            if (priorityRank(p) < priorityRank(top)) top = p;
        });
        return top;
    }

    function notifTitleFor(n) {
        const type = (n && n.type) || '';
        const data = (n && n.data) || {};
        if (type.includes('PengajuanProforma')) return 'Pengajuan Proforma';
        if (type.includes('SoNotification')) {
            return data.kind === 'proforma' ? 'SO Proforma' : 'SO Lanjutan';
        }
        if (type.includes('InternalRevisionOtp')) return 'OTP Revisi Internal';
        return notifMeta(type).label;
    }

    function notifSubtitleFor(n) {
        const data = (n && n.data) || {};
        const parts = [];
        if (data.customer) parts.push(data.customer);
        if (data.customer_kota) parts.push('(' + data.customer_kota + ')');
        if (data.type_transaction) parts.push(data.type_transaction);
        return parts.join(' · ');
    }

    function nzShortTime(value) {
        if (!value) return '';
        const d = new Date(String(value).replace(' ', 'T'));
        if (isNaN(d.getTime())) return '';
        return ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
    }

    function nzDateKey(date) {
        return date.getFullYear() + '-'
            + ('0' + (date.getMonth() + 1)).slice(-2) + '-'
            + ('0' + date.getDate()).slice(-2);
    }

    function nzDayLabel(dateKey) {
        if (!dateKey) return '';
        const now = new Date();
        if (dateKey === nzDateKey(now)) return 'Hari ini';
        if (dateKey === nzDateKey(new Date(now.getTime() - 86400000))) return 'Kemarin';
        const parts = String(dateKey).split('-');
        if (parts.length !== 3) return '';
        return parseInt(parts[2], 10) + ' ' + (NZ_MONTHS[parseInt(parts[1], 10) - 1] || '') + ' ' + parts[0];
    }

    function isTypingTarget(el) {
        if (!el || !el.tagName) return false;
        const tag = el.tagName.toLowerCase();
        return tag === 'input' || tag === 'textarea' || tag === 'select' || el.isContentEditable;
    }

    function buildNotificationItem(notification) {

        notification = notification || {};

        const data = notification.data || {};
        const type = notification.type || '';
        const meta = notifMeta(type);
        const unread = !!notification.is_unread;
        const priority = nzPriority(notification);

        const readUrl = notifReadUrl(notification.id);

        const quick = unread
            ? `<button type="button" class="notif-quick" title="Tandai sudah dibaca" data-quick="${notification.id}"><i class="bi bi-check2"></i></button>`
            : '';

        const dot = unread ? '<span class="notif-dot"></span>' : '';
        const flag = priority === 'info'
            ? ''
            : `<span class="nz-flag ${priority}">${NZ_LABELS[priority]}</span>`;

        if (type.includes('InternalRevisionOtp')) {
            return `
            <div class="notification-item ${unread ? 'unread' : 'is-read'}" data-priority="${priority}" data-url="${readUrl}">
                <div class="notif-icon"><i class="bi bi-key"></i></div>
                <div class="notif-main">
                    <div class="notif-title">OTP Revisi Internal${flag}</div>
                    <span class="notif-otp">${esc(data.otp || '-')}</span>
                    <div class="notif-sub" style="white-space:normal">${esc(data.message || '')}</div>
                    <div class="notif-meta"><span>${esc(notification.human || '')}</span></div>
                </div>
                ${dot}
                ${quick}
            </div>`;
        }

        const code = data.code ? `<span class="notif-code">${esc(data.code)}</span>` : '';
        const subtitle = notifSubtitleFor(notification);

        return `
            <div class="notification-item ${unread ? 'unread' : 'is-read'}" data-priority="${priority}" data-url="${readUrl}">
                <div class="notif-icon"><i class="bi ${meta.icon}"></i></div>
                <div class="notif-main">
                    <div class="notif-title">${esc(notifTitleFor(notification))}${flag}${code}</div>
                    ${subtitle ? `<div class="notif-sub">${esc(subtitle)}</div>` : ''}
                    <div class="notif-meta">
                        <span>${meta.short}</span>
                        <span>&middot;</span>
                        <span>${esc(notification.human || '')}</span>
                    </div>
                </div>
                ${dot}
                ${quick}
            </div>`;
    }

    function buildReminderItem(notification) {

        const data = notification.data || {};
        const type = notification.type || '';
        const priority = nzPriority(notification);
        const unread = !!notification.is_unread;

        const flag = priority === 'info'
            ? ''
            : `<span class="nz-flag ${priority}">${NZ_LABELS[priority]}</span>`;

        let code = '';
        if (type.includes('InternalRevisionOtp') && data.otp) {
            code = `<span class="notif-otp">${esc(data.otp)}</span>`;
        } else if (data.code) {
            code = `<span class="notif-code">${esc(data.code)}</span>`;
        }

        const subtitle = notifSubtitleFor(notification);

        return `
            <div class="nzrem-item ${unread ? 'unread' : 'is-read'}" data-priority="${priority}" data-url="${notifReadUrl(notification.id)}">
                <span class="nzrem-bullet"></span>
                <div class="nzrem-item-main">
                    <div class="nzrem-item-title">${esc(notifTitleFor(notification))}${flag}${code}</div>
                    ${subtitle ? `<div class="nzrem-item-sub">${esc(subtitle)}</div>` : ''}
                </div>
                <div class="nzrem-item-when">${esc(nzShortTime(notification.created_at))}</div>
            </div>`;
    }


    /* ================================
       SUARA
    ================================= */

    function ensureAudio() {
        if (!notifAudioCtx) {
            const AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return null;
            try { notifAudioCtx = new AC(); } catch (e) { return null; }
        }
        return notifAudioCtx;
    }

    // Master dengan kompresor supaya nada panjang & bertumpuk tetap jelas
    // tanpa pecah di speaker kecil.
    function audioMaster(ctx) {
        if (!ctx.__nzMaster) {
            let node = ctx.destination;
            if (typeof ctx.createDynamicsCompressor === 'function') {
                const comp = ctx.createDynamicsCompressor();
                comp.threshold.value = -14;
                comp.knee.value = 12;
                comp.ratio.value = 6;
                comp.attack.value = 0.003;
                comp.release.value = 0.25;
                comp.connect(ctx.destination);
                node = comp;
            }
            const master = ctx.createGain();
            master.gain.value = 1;
            master.connect(node);
            ctx.__nzMaster = master;
        }
        return ctx.__nzMaster;
    }

    // Satu nada bel: osilator utama + partial satu oktaf di atasnya agar
    // terdengar nyaring di speaker laptop/HP tanpa volume berlebihan.
    function nzNote(ctx, dest, freq, at, duration, peak, type, harmonic) {
        const osc = ctx.createOscillator();
        const osc2 = ctx.createOscillator();
        const gain = ctx.createGain();
        const partial = ctx.createGain();

        osc.type = type || 'sine';
        osc.frequency.setValueAtTime(freq, at);

        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(freq * 2, at);
        partial.gain.setValueAtTime(harmonic == null ? 0.18 : harmonic, at);

        gain.gain.setValueAtTime(0.0001, at);
        gain.gain.exponentialRampToValueAtTime(peak, at + 0.012);
        gain.gain.exponentialRampToValueAtTime(peak * 0.55, at + Math.min(0.18, duration * 0.45));
        gain.gain.exponentialRampToValueAtTime(0.0001, at + duration);

        osc.connect(gain);
        osc2.connect(partial);
        partial.connect(gain);
        gain.connect(dest);

        osc.start(at);
        osc2.start(at);
        osc.stop(at + duration + 0.03);
        osc2.stop(at + duration + 0.03);
    }

    // Mendesak = tiga nada naik, Perlu tindakan = dua nada, Informasi = satu
    // nada lembut. Semua prioritas berbunyi supaya tidak ada notifikasi yang
    // masuk tanpa aba-aba.
    function scheduleChime(ctx, priority) {
        const out = audioMaster(ctx);
        const now = ctx.currentTime + 0.03;

        if (priority === 'urgent') {
            nzNote(ctx, out, 880, now, 0.48, 0.55, 'triangle', 0.22);
            nzNote(ctx, out, 1174, now + 0.26, 0.48, 0.55, 'triangle', 0.22);
            nzNote(ctx, out, 1568, now + 0.52, 0.66, 0.55, 'triangle', 0.18);
        } else if (priority === 'action') {
            nzNote(ctx, out, 733, now, 0.46, 0.50, 'sine', 0.20);
            nzNote(ctx, out, 988, now + 0.24, 0.60, 0.50, 'sine', 0.20);
        } else {
            nzNote(ctx, out, 784, now, 0.44, 0.38, 'sine', 0.16);
        }
    }

    function playNotifSound(priority, force) {

        priority = priority || 'info';

        const ctx = ensureAudio();
        if (!ctx) return false;

        if (ctx.state !== 'running') {
            // Simpan yang paling mendesak; jangan tandai sudah berbunyi.
            if (!notifPendingSound || priorityRank(priority) < priorityRank(notifPendingSound)) {
                notifPendingSound = priority;
            }
            document.body.classList.add('nz-audio-locked');
            refreshAudioChip();
            if (force) unlockAudio();
            return false;
        }

        document.body.classList.remove('nz-audio-locked');

        try {
            scheduleChime(ctx, priority);
            refreshAudioChip();
            return true;
        } catch (e) {
            // Suara opsional: jangan ganggu alur notifikasi.
            return false;
        }
    }

    function flushPendingSound() {

        if (!notifPendingSound) {
            refreshAudioChip();
            return;
        }

        const pending = notifPendingSound;
        notifPendingSound = '';

        // Kalau audio masih tertahan, playNotifSound mengisi ulang antrean.
        const played = playNotifSound(pending);

        if (played) {
            // Baru sekarang penanda "sudah berbunyi" ditulis, supaya bunyi
            // yang tertahan tidak hilang diam-diam.
            if (notifPendingSeenAt) {
                rememberSound(notifPendingSeenAt);
                notifPendingSeenAt = '';
            }
            refreshAudioChip();
        }
    }

    function unlockAudio() {

        const ctx = ensureAudio();
        if (!ctx) return;

        if (ctx.state === 'running') {
            document.body.classList.remove('nz-audio-locked');
            flushPendingSound();
            refreshAudioChip();
            return;
        }
        if (notifAudioUnlocking) return;

        notifAudioUnlocking = true;
        let guard = null;
        const done = function () {
            if (guard) { clearTimeout(guard); guard = null; }
            notifAudioUnlocking = false;
            refreshAudioChip();
        };

        // resume() bisa menggantung tanpa gestur; jangan sampai percobaan
        // berikutnya ikut tertahan.
        guard = setTimeout(done, 1200);

        try {
            const resume = ctx.resume();
            if (resume && typeof resume.then === 'function') {
                resume.then(function () {
                    done();
                    document.body.classList.remove('nz-audio-locked');
                    flushPendingSound();
                }, done);
            } else {
                done();
            }
        } catch (e) {
            done();
        }
    }

    function refreshAudioChip() {

        const chip = document.getElementById('notifAudioChip');
        if (!chip) return;

        const locked = document.body.classList.contains('nz-audio-locked') || !!notifPendingSound;
        const unread = parseInt($('#notifCount').text(), 10) || 0;

        chip.hidden = !(locked && unread > 0);
    }


    /* ================================
       NOTIFIKASI OS (OPSIONAL)
    ================================= */

    function refreshOsButton() {
        const btn = document.getElementById('notifEnableOs');
        if (!btn) return;

        const supported = 'Notification' in window;
        if (!supported) {
            btn.hidden = true;
            return;
        }

        const permission = Notification.permission;
        btn.hidden = permission !== 'default';
        btn.classList.toggle('on', permission === 'granted');
    }

    function requestOsPermission() {
        if (!('Notification' in window)) return;

        try {
            Notification.requestPermission().then(function () {
                refreshOsButton();
            });
        } catch (e) {}
    }

    function showOsNotification(notification) {

        if (!('Notification' in window) || Notification.permission !== 'granted') return;

        const priority = nzPriority(notification);

        // Tab sedang aktif dan bukan mendesak -> lonceng + pengingat cukup.
        if (!document.hidden && priority !== 'urgent') return;

        const data = notification.data || {};
        const type = notification.type || '';

        let title = notifMeta(type).label;
        if (type.includes('SoNotification')) {
            title = data.kind === 'proforma' ? 'SO Proforma' : 'SO Lanjutan';
        } else if (type.includes('InternalRevisionOtp')) {
            title = 'OTP Revisi Internal';
        }

        const parts = [];
        if (data.code) parts.push(data.code);
        if (data.customer) parts.push(data.customer);
        if (data.customer_kota) parts.push('(' + data.customer_kota + ')');

        try {
            const os = new Notification(title, {
                body: parts.join(' · ') || 'Notifikasi baru masuk',
                icon: NZ_ICON,
                tag: notification.id
            });

            os.onclick = function () {
                window.focus();
                window.location.href = notifReadUrl(notification.id);
            };
        } catch (e) {}
    }


    /* ================================
       BADGE, BANNER, PANEL
    ================================= */

    function pulseBell() {
        const badge = document.getElementById('notifCount');
        const ring = document.getElementById('notifBellRing');

        [badge, ring].forEach(function (el) {
            if (!el) return;
            el.classList.remove('pulse');
            void el.offsetWidth;
            el.classList.add('pulse');
            setTimeout(function () { el.classList.remove('pulse'); }, 1800);
        });
    }

    function updateBadge(count, counts) {

        const $badge = $('#notifCount');

        $badge
            .text(count > 99 ? '99+' : count)
            .toggleClass('show', count > 0)
            .toggleClass('blink', count > 0);

        // Lonceng berkedip terus selama ada yang belum dibaca.
        $('#notifBellRing').toggleClass('blink', count > 0);

        if ($badge.length) {
            let color = NZ_COLORS.info;
            if (counts && counts.urgent > 0) color = NZ_COLORS.urgent;
            else if (counts && counts.action > 0) color = NZ_COLORS.action;
            $badge[0].style.background = color;
        }

        $('#markAllAsReadBtn').toggleClass('d-none', count <= 0);

        refreshAudioChip();
    }

    function isPanelOpen() {
        const panel = document.getElementById('notifPanel');
        return !!panel && !panel.hidden;
    }

    function openNotifPanel() {

        const panel = document.getElementById('notifPanel');
        if (!panel || !panel.hidden) return;

        hideReminder(true);

        panel.hidden = false;

        const bell = document.getElementById('notifBell');
        if (bell) bell.setAttribute('aria-expanded', 'true');

        unlockAudio();
        reloadNotifications();
    }

    function closeNotifPanel() {

        const panel = document.getElementById('notifPanel');
        if (!panel || panel.hidden) return;

        panel.hidden = true;

        const bell = document.getElementById('notifBell');
        if (bell) bell.setAttribute('aria-expanded', 'false');
    }


    /* ================================
       MODAL PENGINGAT (HARI INI & KEMARIN)
    ================================= */

    function isReminderOpen() {
        const modal = document.getElementById('notifReminder');
        return !!modal && !modal.hidden;
    }

    function renderReminder(response) {

        const modal = document.getElementById('notifReminder');
        if (!modal) return;

        const recent = response.recent || [];
        const recentUnread = response.recentUnread || 0;
        const recentTotal = response.recentTotal || recent.length;
        const olderTotal = response.olderTotal || 0;
        const olderUnread = response.olderUnread || 0;

        const priority = topPriority(recent.filter(function (n) { return n.is_unread; }));
        const card = modal.querySelector('.nzrem');
        if (card) card.dataset.priority = priority;

        const title = document.getElementById('nzremTitle');
        const sub = document.getElementById('nzremSub');
        const badge = document.getElementById('nzremBadge');
        const list = document.getElementById('notifReminderList');
        const more = document.getElementById('notifReminderMore');

        if (title) title.textContent = NZ_REMINDER_TITLE[priority] || NZ_REMINDER_TITLE.info;

        if (sub) {
            sub.textContent = 'Hari ini & kemarin · ' + (recentUnread > 0
                ? recentUnread + ' belum dibaca'
                : 'semua sudah dibaca');
        }

        if (badge) {
            badge.hidden = recentUnread <= 0;
            badge.textContent = recentUnread > 99 ? '99+' : recentUnread;
        }

        if (list) {
            list.innerHTML = recent.length
                ? recent.map(buildReminderItem).join('')
                : '<div class="nzrem-empty">Belum ada notifikasi hari ini.</div>';
        }

        if (more) {
            const hiddenToday = Math.max(0, recentTotal - recent.length);
            let note = '';

            if (olderTotal > 0) {
                note = 'Masih ada <b>' + olderTotal + '</b> notifikasi lain yang lebih lama'
                    + (olderUnread > 0 ? ' &middot; <b>' + olderUnread + '</b> belum dibaca.' : '.');
            } else if (hiddenToday > 0) {
                note = 'Masih ada <b>' + hiddenToday + '</b> notifikasi lain hari ini.';
            }

            if (note) {
                more.innerHTML = '<i class="bi bi-clock-history"></i><div>' + note
                    + '<div class="nzrem-more-links">'
                    + '<button type="button" id="notifReminderOpenBell">Cek ikon lonceng</button>'
                    + '<a href="{{ route('superuser.penjualan.notification.index') }}">Arsip notifikasi</a>'
                    + '</div></div>';
                more.hidden = false;
            } else {
                more.innerHTML = '';
                more.hidden = true;
            }
        }
    }

    function openReminder(response) {

        const modal = document.getElementById('notifReminder');
        if (!modal) return;

        renderReminder(response);

        if (!modal.hidden) return;

        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function hideReminder(remember) {

        const modal = document.getElementById('notifReminder');
        if (!modal || modal.hidden) return;

        modal.hidden = true;
        document.body.style.overflow = '';

        if (remember) {
            rememberReminderClosed();
        }
    }


    /* ================================
       DATA + RENDER
    ================================= */

    const notifState = { type: '', page: 1, hasMore: false };

    function notifListUrl(page) {
        let url = '{{ route('superuser.penjualan.notification.getNotifData') }}?page=' + page;
        if (notifState.type) {
            url += '&type=' + encodeURIComponent(NOTIF_TYPES[notifState.type] || '');
        }
        return url;
    }

    function notifEmptyHtml(message) {
        return `<div class="notif-empty"><i class="bi bi-bell-slash"></i>${message}</div>`;
    }

    function renderNotifList(rows, append) {

        const $list = $('#notifList');

        if (!append) {
            $list.empty();
        }

        if (!rows.length) {
            if (!append) {
                $list.html(notifEmptyHtml(notifState.type
                    ? 'Tidak ada notifikasi di kategori ini.'
                    : 'Belum ada notifikasi.'));
            }
            $('#notifLoadMore').prop('hidden', true);
            return;
        }

        // Pemisah hari hanya untuk tab "Semua".
        let lastDay = '';
        if (append) {
            const $lastDay = $list.find('.np-day').last();
            lastDay = $lastDay.length ? $lastDay.attr('data-day') : '';
        }

        let html = '';
        rows.forEach(function (row) {
            if (!notifState.type) {
                const label = nzDayLabel(row.date_key);
                if (label && label !== lastDay) {
                    html += `<div class="np-day" data-day="${esc(label)}">${esc(label)}</div>`;
                    lastDay = label;
                }
            }
            html += buildNotificationItem(row);
        });

        $list.append(html);
        $('#notifLoadMore').prop('hidden', !notifState.hasMore);
    }

    function applyResponseMeta(response) {

        const counts = response.priorityCounts || {};
        const unread = response.notifCount || 0;

        updateBadge(unread, counts);

        $('#notifTotalInfo').text((response.total || 0) + ' notifikasi');

        let sub = 'Semua sudah dibaca';
        if (unread > 0) {
            sub = unread + ' belum dibaca';
            if (counts.urgent > 0) sub += ' · ' + counts.urgent + ' mendesak';
            else if (counts.action > 0) sub += ' · ' + counts.action + ' perlu tindakan';
        }
        $('#notifMSub').text(sub);
    }

    // Notifikasi yang baru masuk sejak halaman dibuka: notifikasi OS dan
    // lonceng. Suara ditangani maybeShowReminder agar tidak berbunyi dua kali.
    function detectIncoming(notifications) {

        const incoming = notifications.filter(function (n) {
            return n.is_unread && !seenNotifIds.has(n.id);
        });
        notifications.forEach(function (n) { seenNotifIds.add(n.id); });

        if (!notifFirstLoad && incoming.length) {
            incoming.forEach(showOsNotification);
            pulseBell();
        }

        notifFirstLoad = false;
    }

    function maybeShowReminder(response) {

        const recent = response.recent || [];
        if (!recent.length) return;

        const recentUnread = response.recentUnread || 0;
        const latestAt = recent[0].created_at || '';

        if (latestAt && latestAt > lastUnreadMaxAt) lastUnreadMaxAt = latestAt;

        if (recentUnread <= 0) return;

        const unreadRecent = recent.filter(function (n) { return n.is_unread; });

        // Bunyi sekali per notifikasi terbaru. Penanda "sudah berbunyi" hanya
        // ditulis kalau audio benar-benar diputar; kalau browser masih
        // menahan audio, bunyi menunggu sentuhan pertama lalu ditandai.
        if (latestAt > soundSeenAt()) {
            const soundPriority = unreadRecent.length ? nzPriority(unreadRecent[0]) : 'info';
            const played = playNotifSound(soundPriority);
            if (played) {
                rememberSound(latestAt);
            } else if (notifPendingSound) {
                notifPendingSeenAt = latestAt;
            } else {
                rememberSound(latestAt);
            }
        }

        if (isReminderOpen()) {
            renderReminder(response);
            return;
        }

        if (latestAt <= reminderSeenAt()) return;
        if (isPanelOpen() || NZ_ARCHIVE_PAGE) return;
        if (isTypingTarget(document.activeElement)) return;

        openReminder(response);
    }

    function reloadNotifications(options) {

        options = options || {};
        const append = !!options.append;
        const page = append ? notifState.page + 1 : 1;

        if (currentRequest) currentRequest.abort();

        if (append) $('#notifLoadMore').prop('disabled', true).text('Memuat...');

        currentRequest = $.ajax({
            url: notifListUrl(page),
            type: 'GET',
            dataType: 'json'
        });

        currentRequest.done(function (response) {

            applyResponseMeta(response);

            const notifications = response.notifications || [];

            notifState.page = response.page || page;
            notifState.hasMore = !!response.hasMore;

            detectIncoming(notifications);
            renderNotifList(notifications, append);
            maybeShowReminder(response);

        }).fail(function (xhr, status) {

            if (status !== 'abort') {
                console.error('Notification error:', xhr.responseText);
            }

        }).always(function () {
            currentRequest = null;
            $('#notifLoadMore').prop('disabled', false).text('Muat lebih banyak');
        });
    }

    // Polling: badge, lonceng, banner, dan pengingat selalu jalan; daftar
    // hanya dirender ulang saat panel terbuka di halaman pertama.
    function pollNotifications() {

        if (currentRequest) return;

        $.ajax({
            url: notifListUrl(1),
            type: 'GET',
            dataType: 'json'
        }).done(function (response) {

            applyResponseMeta(response);

            const notifications = response.notifications || [];
            detectIncoming(notifications);

            if (isPanelOpen() && notifState.page === 1) {
                notifState.hasMore = !!response.hasMore;
                renderNotifList(notifications, false);
            }

            maybeShowReminder(response);
        });
    }


    /* ================================
       SMART POLLING
    ================================= */

    function startPolling() {
        if (!notifInterval) {
            notifInterval = setInterval(pollNotifications, 10000);
        }
    }

    function stopPolling() {
        if (notifInterval) {
            clearInterval(notifInterval);
            notifInterval = null;
        }
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopPolling();
        } else {
            pollNotifications();
            startPolling();
        }
    });


    /* ================================
       EVENT BINDING
    ================================= */

    $(document).on('click', '.notification-item', function () {
        const url = $(this).data('url');
        if (url) window.location.href = url;
    });

    $(document).on('click', '#notifBell', function (e) {
        e.preventDefault();
        if (isPanelOpen()) {
            closeNotifPanel();
        } else {
            openNotifPanel();
        }
    });

    $(document).on('click', '#notifClose', function () {
        closeNotifPanel();
    });

    // Klik di luar panel menutup dropdown.
    $(document).on('click', function (e) {
        if (!isPanelOpen()) return;
        if (e.target.closest('#notifPanel') || e.target.closest('#notifBell')) return;
        closeNotifPanel();
    });

    $(document).on('click', '#notifReminderClose, #notifReminderLater', function () {
        hideReminder(true);
    });

    $(document).on('click', '#notifReminderOpenAll, #notifReminderOpenBell', function () {
        hideReminder(true);
        openNotifPanel();
    });

    $(document).on('click', '.nzrem-item', function () {
        const url = $(this).data('url');
        if (url) window.location.href = url;
    });

    $(document).on('click', '#notifReminder', function (e) {
        if (e.target === this) hideReminder(true);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (isReminderOpen()) {
            hideReminder(true);
            return;
        }
        closeNotifPanel();
    });

    $(document).on('click', '#markAllAsReadBtn', function (e) {

        e.preventDefault();

        $.post(
            '{{ route('superuser.penjualan.notification.unread_all_notif') }}',
            { _token: '{{ csrf_token() }}' }
        )
        .done(function () {
            hideReminder(false);
            reloadNotifications();
        })
        .fail(function (xhr) {
            console.error('Mark all error:', xhr.responseText);
        });
    });

    $(document).on('click', '.np-tab', function () {

        const type = $(this).data('type') || '';
        if (notifState.type === type) return;

        notifState.type = type;
        $('#notifFilters .np-tab').removeClass('active');
        $(this).addClass('active');

        $('#notifList').html('<div class="notif-empty">Memuat...</div>');
        reloadNotifications();
    });

    $(document).on('click', '#notifLoadMore', function () {
        reloadNotifications({ append: true });
    });

    $(document).on('click', '.notif-quick', function (e) {

        e.stopPropagation();

        const url = '{{ route('superuser.penjualan.notification.mark_as_read_only', ['id' => '__NID__']) }}'
            .replace('__NID__', $(this).data('quick'));

        $.post(url, { _token: '{{ csrf_token() }}' })
            .done(function () { reloadNotifications(); })
            .fail(function (xhr) { console.error('Mark read error:', xhr.responseText); });
    });

    $(document).on('click', '#notifTestSound', function () {

        // Tes suara menggantikan bunyi yang tertunda supaya tidak dobel.
        notifPendingSound = '';
        playNotifSound('urgent', true);

        const btn = this;
        btn.classList.add('on');
        setTimeout(function () { btn.classList.remove('on'); }, 700);
    });

    $(document).on('click', '#notifAudioChip', function () {

        const hadPending = !!notifPendingSound;
        unlockAudio();

        // Kalau tidak ada bunyi tertunda, beri contoh singkat sebagai bukti
        // suara sudah aktif.
        if (hadPending) return;

        setTimeout(function () {
            if (notifAudioCtx && notifAudioCtx.state === 'running' && !notifPendingSound) {
                playNotifSound('action');
            }
        }, 200);
    });

    $(document).on('click', '#notifEnableOs', function () {
        requestOsPermission();
    });

    // Browser memblokir bunyi sebelum ada interaksi: buka kunci pada
    // interaksi pertama, lalu mainkan bunyi yang sempat tertunda.
    document.addEventListener('pointerdown', unlockAudio, { once: true });
    document.addEventListener('touchstart', unlockAudio, { once: true });
    document.addEventListener('mousedown', unlockAudio, { once: true });
    document.addEventListener('keydown', unlockAudio, { once: true });

    // Init
    refreshOsButton();
    reloadNotifications();
    startPolling();

})();

</script>
@endpush