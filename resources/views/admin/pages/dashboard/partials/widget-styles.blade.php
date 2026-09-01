<style>
    /* ── Stat cards (BEM قديم — يستخدمه أيضاً لوحة الطالب وصفحة تقاريره عبر
       student.partials.dashboard-widget-styles، فلا يُمس) ── */
    .dashboard-stat-card {
        position: relative;
        overflow: hidden;
        border-radius: 14px;
        border: none;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .dashboard-stat-card::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.15);
        inset-inline-start: -30px;
        inset-block-end: -40px;
        pointer-events: none;
    }
    .dashboard-stat-card:hover {
        transform: translateY(-3px);
    }
    .dashboard-stat-card__body {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1.1rem 1.15rem;
        position: relative;
        z-index: 1;
    }
    .dashboard-stat-card__content {
        flex: 1;
        min-width: 0;
    }
    .dashboard-stat-card__label {
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.65rem;
        opacity: 0.92;
    }
    .dashboard-stat-card__value {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 0.2rem;
    }
    .dashboard-stat-card__meta {
        font-size: 0.75rem;
        margin-bottom: 0;
        opacity: 0.85;
    }
    .dashboard-stat-card__icon {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    /* Light mode — vivid gradients */
    .dashboard-stat-card--students {
        background: linear-gradient(135deg, #4a7cff 0%, #2563eb 55%, #1d4ed8 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.28);
    }
    .dashboard-stat-card--classes {
        background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 55%, #7c3aed 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(139, 92, 246, 0.28);
    }
    .dashboard-stat-card--subjects {
        background: linear-gradient(135deg, #34d399 0%, #10b981 55%, #059669 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.28);
    }
    .dashboard-stat-card--quizzes {
        background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 55%, #0284c7 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(14, 165, 233, 0.28);
    }
    .dashboard-stat-card--enrollments {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 55%, #d97706 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(245, 158, 11, 0.28);
    }
    .dashboard-stat-card--students:hover { box-shadow: 0 12px 28px rgba(37, 99, 235, 0.38); }
    .dashboard-stat-card--classes:hover { box-shadow: 0 12px 28px rgba(139, 92, 246, 0.38); }
    .dashboard-stat-card--subjects:hover { box-shadow: 0 12px 28px rgba(16, 185, 129, 0.38); }
    .dashboard-stat-card--quizzes:hover { box-shadow: 0 12px 28px rgba(14, 165, 233, 0.38); }
    .dashboard-stat-card--enrollments:hover { box-shadow: 0 12px 28px rgba(245, 158, 11, 0.38); }

    /* Dark mode — tinted glass */
    [data-theme-mode="dark"] .dashboard-stat-card {
        background: var(--custom-card-bg, var(--default-background));
        color: var(--default-text-color);
        border: 1px solid var(--default-border);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
    }
    [data-theme-mode="dark"] .dashboard-stat-card::after {
        background: rgba(255, 255, 255, 0.04);
        inset-inline-start: auto;
        inset-inline-end: -20px;
        inset-block-end: -30px;
        width: 100px;
        height: 100px;
    }
    [data-theme-mode="dark"] .dashboard-stat-card--students {
        border-color: rgba(74, 124, 255, 0.35);
        background: linear-gradient(145deg, rgba(74, 124, 255, 0.18) 0%, rgba(37, 99, 235, 0.06) 100%);
    }
    [data-theme-mode="dark"] .dashboard-stat-card--classes {
        border-color: rgba(167, 139, 250, 0.35);
        background: linear-gradient(145deg, rgba(167, 139, 250, 0.18) 0%, rgba(139, 92, 246, 0.06) 100%);
    }
    [data-theme-mode="dark"] .dashboard-stat-card--subjects {
        border-color: rgba(52, 211, 153, 0.35);
        background: linear-gradient(145deg, rgba(52, 211, 153, 0.18) 0%, rgba(16, 185, 129, 0.06) 100%);
    }
    [data-theme-mode="dark"] .dashboard-stat-card--quizzes {
        border-color: rgba(56, 189, 248, 0.35);
        background: linear-gradient(145deg, rgba(56, 189, 248, 0.18) 0%, rgba(14, 165, 233, 0.06) 100%);
    }
    [data-theme-mode="dark"] .dashboard-stat-card--enrollments {
        border-color: rgba(251, 191, 36, 0.35);
        background: linear-gradient(145deg, rgba(251, 191, 36, 0.18) 0%, rgba(245, 158, 11, 0.06) 100%);
    }
    [data-theme-mode="dark"] .dashboard-stat-card--students .dashboard-stat-card__value { color: #7ba3ff; }
    [data-theme-mode="dark"] .dashboard-stat-card--classes .dashboard-stat-card__value { color: #c4b5fd; }
    [data-theme-mode="dark"] .dashboard-stat-card--subjects .dashboard-stat-card__value { color: #6ee7b7; }
    [data-theme-mode="dark"] .dashboard-stat-card--quizzes .dashboard-stat-card__value { color: #7dd3fc; }
    [data-theme-mode="dark"] .dashboard-stat-card--enrollments .dashboard-stat-card__value { color: #fcd34d; }
    [data-theme-mode="dark"] .dashboard-stat-card__icon {
        background: rgba(255, 255, 255, 0.08);
    }
    [data-theme-mode="dark"] .dashboard-stat-card--students .dashboard-stat-card__icon { color: #7ba3ff; }
    [data-theme-mode="dark"] .dashboard-stat-card--classes .dashboard-stat-card__icon { color: #c4b5fd; }
    [data-theme-mode="dark"] .dashboard-stat-card--subjects .dashboard-stat-card__icon { color: #6ee7b7; }
    [data-theme-mode="dark"] .dashboard-stat-card--quizzes .dashboard-stat-card__icon { color: #7dd3fc; }
    [data-theme-mode="dark"] .dashboard-stat-card--enrollments .dashboard-stat-card__icon { color: #fcd34d; }
    [data-theme-mode="dark"] .dashboard-stat-card:hover {
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.35);
    }

    /* ── بطاقات لوحة تحكم الأدمن الملوّنة (منقولة بنفس الستايل/الألوان/الحركات
       من مشروع Hr-System) — أسماء أصناف مستقلة تماماً (بادئة dsc-) حتى لا
       تتقاطع مع بطاقات .dashboard-stat-card القديمة أعلاه المستخدمة في لوحة
       الطالب وصفحة تقاريره. ── */
    .dsc-link {
        display: block;
        text-decoration: none !important;
        color: inherit !important;
        animation: dsc-card-enter 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        animation-delay: var(--card-delay, 0s);
    }

    @keyframes dsc-card-enter {
        from { opacity: 0; transform: translateY(28px) scale(0.94); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .dsc-card {
        position: relative;
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        min-height: 128px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.4s ease;
        cursor: pointer;
        background-size: 200% 200%;
        animation: dsc-gradient-flow 6s ease infinite;
        isolation: isolate;
    }

    .dsc-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0.05) 50%, rgba(255, 255, 255, 0.25));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
        z-index: 3;
    }

    @keyframes dsc-gradient-flow {
        0%, 100% { background-position: 0% 50%; }
        50%      { background-position: 100% 50%; }
    }

    .dsc-card:hover {
        transform: translateY(-10px) scale(1.02);
        animation-duration: 3s;
    }

    .dsc-card:active {
        transform: translateY(-4px) scale(0.99);
        transition-duration: 0.1s;
    }

    /* لمعة قطرية عند المرور */
    .dsc-shine {
        position: absolute;
        inset: 0;
        background: linear-gradient(105deg,
            transparent 38%,
            rgba(255, 255, 255, 0.18) 48%,
            rgba(255, 255, 255, 0.28) 50%,
            rgba(255, 255, 255, 0.18) 52%,
            transparent 62%);
        transform: translateX(-120%);
        z-index: 2;
        pointer-events: none;
    }
    .dsc-card:hover .dsc-shine { animation: dsc-shine-sweep 0.85s ease; }
    @keyframes dsc-shine-sweep { to { transform: translateX(120%); } }

    /* نسيج خلفي ناعم */
    .dsc-mesh {
        position: absolute;
        inset: 0;
        opacity: 0.35;
        background-image:
            radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.25) 0%, transparent 45%),
            radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 40%);
        pointer-events: none;
        z-index: 0;
    }

    /* فقاعات عائمة زخرفية */
    .dsc-bubble { position: absolute; border-radius: 50%; background: rgba(255, 255, 255, 0.1); pointer-events: none; z-index: 0; }
    .dsc-bubble-1 { width: 90px; height: 90px; inset-inline-start: -25px; top: -30px; animation: dsc-float-bubble 5s ease-in-out infinite; }
    .dsc-bubble-2 { width: 60px; height: 60px; inset-inline-end: 30%; bottom: -20px; animation: dsc-float-bubble 4s ease-in-out infinite reverse; animation-delay: -1.5s; }
    .dsc-bubble-3 { width: 40px; height: 40px; inset-inline-end: -10px; top: 40%; animation: dsc-float-bubble 3.5s ease-in-out infinite; animation-delay: -0.8s; }
    @keyframes dsc-float-bubble { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(6px, -10px) scale(1.08); } }

    /* توهج نابض أسفل البطاقة */
    .dsc-glow {
        position: absolute;
        width: 160px; height: 160px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        inset-inline-start: -50px; bottom: -60px;
        pointer-events: none; z-index: 0;
        animation: dsc-glow-pulse 4s ease-in-out infinite;
    }
    @keyframes dsc-glow-pulse { 0%, 100% { opacity: 0.6; transform: scale(1); } 50% { opacity: 1; transform: scale(1.1); } }

    .dsc-body {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.4rem 1.55rem;
        gap: 1rem;
        min-height: 128px;
    }

    .dsc-content { flex: 1; min-width: 0; text-align: start; color: #fff; }

    .dsc-content .dsc-label {
        display: block;
        font-size: 0.9rem;
        font-weight: 700;
        opacity: 0.95;
        margin-bottom: 0.45rem;
        text-shadow: 0 1px 8px rgba(0, 0, 0, 0.15);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    .dsc-content .dsc-value {
        display: block;
        font-size: 2.25rem;
        font-weight: 800;
        line-height: 1.05;
        letter-spacing: -1px;
        margin-bottom: 0.4rem;
        text-shadow: 0 2px 16px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease;
    }
    .dsc-content .dsc-value.dsc-value-done { animation: dsc-stat-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
    @keyframes dsc-stat-pop { 0% { transform: scale(1); } 40% { transform: scale(1.12); } 100% { transform: scale(1); } }

    .dsc-card:hover .dsc-value { transform: scale(1.06); transform-origin: right center; }
    [dir="ltr"] .dsc-card:hover .dsc-value { transform-origin: left center; }

    .dsc-content .dsc-subtext {
        display: inline-block;
        font-size: 0.76rem;
        font-weight: 600 !important;
        opacity: 0.9;
        background: rgba(255, 255, 255, 0.18);
        padding: 0.2rem 0.55rem;
        border-radius: 20px;
        backdrop-filter: blur(4px);
    }

    .dsc-icon-wrap { position: relative; flex-shrink: 0; width: 72px; height: 72px; display: flex; align-items: center; justify-content: center; }

    .dsc-icon-ring {
        position: absolute; inset: 0; border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.35);
        animation: dsc-ring-pulse 2.5s ease-out infinite;
    }
    @keyframes dsc-ring-pulse { 0% { transform: scale(0.85); opacity: 0.8; } 70%, 100% { transform: scale(1.35); opacity: 0; } }

    .dsc-icon-circle {
        position: relative;
        width: 68px; height: 68px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.25);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.9rem; color: #fff;
        transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.3s ease, box-shadow 0.3s ease;
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.35);
        animation: dsc-icon-float 3s ease-in-out infinite;
    }
    @keyframes dsc-icon-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }

    .dsc-card:hover .dsc-icon-circle {
        animation: none;
        transform: scale(1.15) rotate(8deg);
        background: rgba(255, 255, 255, 0.38);
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }
    .dsc-card:hover .dsc-icon-ring { animation-duration: 1.2s; }

    /* ===== ألوان البطاقات (نفس تدرجات Hr-System بالضبط + تدرج ذهبي إضافي بنفس الصيغة) ===== */
    .dsc-card--blue {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 30%, #3b82f6 60%, #60a5fa 100%);
        box-shadow: 0 10px 36px rgba(37, 99, 235, 0.45);
    }
    .dsc-card--blue:hover { box-shadow: 0 20px 50px rgba(37, 99, 235, 0.55); }

    .dsc-card--green {
        background: linear-gradient(135deg, #047857 0%, #059669 30%, #10b981 60%, #34d399 100%);
        box-shadow: 0 10px 36px rgba(5, 150, 105, 0.45);
    }
    .dsc-card--green:hover { box-shadow: 0 20px 50px rgba(5, 150, 105, 0.55); }

    .dsc-card--orange {
        background: linear-gradient(135deg, #c2410c 0%, #ea580c 30%, #f97316 60%, #fb923c 100%);
        box-shadow: 0 10px 36px rgba(234, 88, 12, 0.45);
    }
    .dsc-card--orange:hover { box-shadow: 0 20px 50px rgba(234, 88, 12, 0.55); }

    .dsc-card--purple {
        background: linear-gradient(135deg, #6d28d9 0%, #8b5cf6 30%, #a855f7 60%, #c084fc 100%);
        box-shadow: 0 10px 36px rgba(109, 40, 217, 0.45);
    }
    .dsc-card--purple:hover { box-shadow: 0 20px 50px rgba(109, 40, 217, 0.55); }

    .dsc-card--gold {
        background: linear-gradient(135deg, #92400e 0%, #b45309 30%, #d97706 60%, #f59e0b 100%);
        box-shadow: 0 10px 36px rgba(180, 83, 9, 0.45);
    }
    .dsc-card--gold:hover { box-shadow: 0 20px 50px rgba(180, 83, 9, 0.55); }

    @media (max-width: 575.98px) {
        .dsc-content .dsc-value { font-size: 1.75rem; }
        .dsc-icon-wrap { width: 58px; height: 58px; }
        .dsc-icon-circle { width: 54px; height: 54px; font-size: 1.5rem; }
    }

    /* ── اختصارات سريعة (منقولة بنفس الستايل/الألوان/الحركات من مشروع Hr-System) ──
       أصناف مستقلة تماماً (shortcut-*) عن مكوّن x-dashboard-shortcut القديم
       المُستخدَم في قسم "إدارة صفوفي وموادي" أدناه وفي لوحة الطالب. القسم
       بقي داخل بطاقة .card بخلفيتها المعتادة (card-header/card-body) كسائر
       أقسام لوحة التحكم — فقط شبكة البطاقات الصغيرة داخلها استُبدلت. ── */
    .shortcuts-grid .col-xl-2,
    .shortcuts-grid .col-lg-3,
    .shortcuts-grid .col-md-4,
    .shortcuts-grid .col-sm-6,
    .shortcuts-grid .col-6 {
        animation: shortcut-enter 0.55s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        animation-delay: var(--shortcut-delay, 0s);
    }

    @keyframes shortcut-enter {
        from { opacity: 0; transform: translateY(20px) scale(0.92); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .shortcut-card {
        position: relative;
        border: 1px solid var(--default-border, rgba(0, 0, 0, 0.06));
        border-radius: 16px;
        padding: 1.35rem 0.85rem 1.15rem;
        text-align: center;
        text-decoration: none !important;
        color: inherit !important;
        background: var(--custom-white, #fff);
        height: 100%;
        min-height: 148px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
                    box-shadow 0.4s ease,
                    border-color 0.3s ease,
                    background 0.3s ease;
        isolation: isolate;
    }

    .shortcut-shine {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            105deg,
            transparent 40%,
            rgba(255, 255, 255, 0.7) 48%,
            rgba(255, 255, 255, 0.9) 50%,
            rgba(255, 255, 255, 0.7) 52%,
            transparent 60%
        );
        transform: translateX(-130%);
        z-index: 2;
        pointer-events: none;
        opacity: 0;
    }

    .shortcut-card:hover .shortcut-shine {
        opacity: 1;
        animation: shortcut-shine 0.7s ease;
    }

    @keyframes shortcut-shine {
        to { transform: translateX(130%); }
    }

    .shortcut-accent {
        position: absolute;
        top: 0;
        inset-inline: 0;
        height: 3px;
        background: var(--shortcut-accent, #4a7dff);
        transform: scaleX(0);
        transform-origin: center;
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        border-radius: 16px 16px 0 0;
    }

    .shortcut-card:hover .shortcut-accent { transform: scaleX(1); }

    .shortcut-card:hover {
        transform: translateY(-8px) scale(1.03);
        border-color: var(--shortcut-border, rgba(74, 125, 255, 0.25));
        box-shadow: 0 16px 40px var(--shortcut-shadow, rgba(74, 125, 255, 0.18));
    }

    .shortcut-card:active {
        transform: translateY(-3px) scale(0.98);
        transition-duration: 0.1s;
    }

    .shortcut-ripple {
        position: absolute;
        border-radius: 50%;
        background: var(--shortcut-icon-color, #4a7dff);
        opacity: 0.25;
        transform: scale(0);
        animation: shortcut-ripple 0.55s ease-out forwards;
        pointer-events: none;
        z-index: 3;
    }

    @keyframes shortcut-ripple {
        to { transform: scale(4); opacity: 0; }
    }

    .shortcut-icon-wrap {
        position: relative;
        width: 56px;
        height: 56px;
        margin-bottom: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .shortcut-icon-ring {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 2px solid var(--shortcut-ring, rgba(74, 125, 255, 0.3));
        opacity: 0;
        transform: scale(0.8);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .shortcut-card:hover .shortcut-icon-ring {
        opacity: 1;
        animation: shortcut-ring 1.2s ease-out infinite;
    }

    @keyframes shortcut-ring {
        0%   { transform: scale(0.85); opacity: 0.7; }
        100% { transform: scale(1.45); opacity: 0; }
    }

    .shortcut-icon {
        position: relative;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        background: var(--shortcut-icon-bg, rgba(74, 125, 255, 0.12));
        color: var(--shortcut-icon-color, #4a7dff);
        transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1),
                    background 0.3s ease,
                    box-shadow 0.3s ease;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
        animation: shortcut-icon-float 3s ease-in-out infinite;
        animation-delay: var(--shortcut-delay, 0s);
    }

    @keyframes shortcut-icon-float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-3px); }
    }

    .shortcut-card:hover .shortcut-icon {
        animation: none;
        transform: scale(1.18) rotate(-8deg);
        background: var(--shortcut-icon-bg-hover, rgba(74, 125, 255, 0.2));
        box-shadow: 0 6px 20px var(--shortcut-shadow, rgba(74, 125, 255, 0.25));
    }

    .shortcut-title {
        font-size: 0.92rem;
        font-weight: 800;
        margin-bottom: 0.3rem;
        color: var(--default-text-color, #1e293b);
        transition: color 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .shortcut-card:hover .shortcut-title { color: var(--shortcut-icon-color, #4a7dff); }

    .shortcut-desc {
        font-size: 0.74rem;
        color: var(--text-muted, #94a3b8);
        font-weight: 600 !important;
        line-height: 1.35;
        transition: color 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .shortcut-card:hover .shortcut-desc {
        color: var(--default-text-color, #64748b);
        opacity: 0.75;
    }

    .shortcut-arrow {
        position: absolute;
        bottom: 0.65rem;
        inset-inline-start: 0.65rem;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--shortcut-icon-bg, rgba(74, 125, 255, 0.1));
        color: var(--shortcut-icon-color, #4a7dff);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        opacity: 0;
        transform: translateX(8px);
        transition: opacity 0.3s ease, transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .shortcut-card:hover .shortcut-arrow {
        opacity: 1;
        transform: translateX(0);
    }

    /* ألوان الاختصارات (11 نمطاً — نفس قيم Hr-System بالضبط) */
    .shortcut-theme-blue   { --shortcut-accent:#3b82f6; --shortcut-border:rgba(59,130,246,.3); --shortcut-shadow:rgba(59,130,246,.22); --shortcut-ring:rgba(59,130,246,.35); --shortcut-icon-bg:rgba(59,130,246,.12); --shortcut-icon-bg-hover:rgba(59,130,246,.22); --shortcut-icon-color:#3b82f6; }
    .shortcut-theme-green  { --shortcut-accent:#10b981; --shortcut-border:rgba(16,185,129,.3); --shortcut-shadow:rgba(16,185,129,.22); --shortcut-ring:rgba(16,185,129,.35); --shortcut-icon-bg:rgba(16,185,129,.12); --shortcut-icon-bg-hover:rgba(16,185,129,.22); --shortcut-icon-color:#10b981; }
    .shortcut-theme-purple { --shortcut-accent:#8b5cf6; --shortcut-border:rgba(139,92,246,.3); --shortcut-shadow:rgba(139,92,246,.22); --shortcut-ring:rgba(139,92,246,.35); --shortcut-icon-bg:rgba(139,92,246,.12); --shortcut-icon-bg-hover:rgba(139,92,246,.22); --shortcut-icon-color:#8b5cf6; }
    .shortcut-theme-orange { --shortcut-accent:#f97316; --shortcut-border:rgba(249,115,22,.3); --shortcut-shadow:rgba(249,115,22,.22); --shortcut-ring:rgba(249,115,22,.35); --shortcut-icon-bg:rgba(249,115,22,.12); --shortcut-icon-bg-hover:rgba(249,115,22,.22); --shortcut-icon-color:#f97316; }
    .shortcut-theme-pink   { --shortcut-accent:#ec4899; --shortcut-border:rgba(236,72,153,.3); --shortcut-shadow:rgba(236,72,153,.22); --shortcut-ring:rgba(236,72,153,.35); --shortcut-icon-bg:rgba(236,72,153,.12); --shortcut-icon-bg-hover:rgba(236,72,153,.22); --shortcut-icon-color:#ec4899; }
    .shortcut-theme-teal   { --shortcut-accent:#14b8a6; --shortcut-border:rgba(20,184,166,.3); --shortcut-shadow:rgba(20,184,166,.22); --shortcut-ring:rgba(20,184,166,.35); --shortcut-icon-bg:rgba(20,184,166,.12); --shortcut-icon-bg-hover:rgba(20,184,166,.22); --shortcut-icon-color:#14b8a6; }
    .shortcut-theme-cyan   { --shortcut-accent:#06b6d4; --shortcut-border:rgba(6,182,212,.3); --shortcut-shadow:rgba(6,182,212,.22); --shortcut-ring:rgba(6,182,212,.35); --shortcut-icon-bg:rgba(6,182,212,.12); --shortcut-icon-bg-hover:rgba(6,182,212,.22); --shortcut-icon-color:#06b6d4; }
    .shortcut-theme-gold   { --shortcut-accent:#f59e0b; --shortcut-border:rgba(245,158,11,.3); --shortcut-shadow:rgba(245,158,11,.22); --shortcut-ring:rgba(245,158,11,.35); --shortcut-icon-bg:rgba(245,158,11,.12); --shortcut-icon-bg-hover:rgba(245,158,11,.22); --shortcut-icon-color:#f59e0b; }
    .shortcut-theme-red    { --shortcut-accent:#ef4444; --shortcut-border:rgba(239,68,68,.3); --shortcut-shadow:rgba(239,68,68,.22); --shortcut-ring:rgba(239,68,68,.35); --shortcut-icon-bg:rgba(239,68,68,.12); --shortcut-icon-bg-hover:rgba(239,68,68,.22); --shortcut-icon-color:#ef4444; }
    .shortcut-theme-brown  { --shortcut-accent:#b45309; --shortcut-border:rgba(180,83,9,.3); --shortcut-shadow:rgba(180,83,9,.22); --shortcut-ring:rgba(180,83,9,.35); --shortcut-icon-bg:rgba(180,83,9,.12); --shortcut-icon-bg-hover:rgba(180,83,9,.22); --shortcut-icon-color:#b45309; }
    .shortcut-theme-indigo { --shortcut-accent:#6366f1; --shortcut-border:rgba(99,102,241,.3); --shortcut-shadow:rgba(99,102,241,.22); --shortcut-ring:rgba(99,102,241,.35); --shortcut-icon-bg:rgba(99,102,241,.12); --shortcut-icon-bg-hover:rgba(99,102,241,.22); --shortcut-icon-color:#6366f1; }

    @media (max-width: 575.98px) {
        .shortcut-card { min-height: 130px; padding: 1.1rem 0.6rem 1rem; }
        .shortcut-icon-wrap { width: 48px; height: 48px; margin-bottom: 0.65rem; }
        .shortcut-icon { width: 44px; height: 44px; font-size: 1.25rem; }
        .shortcut-title { font-size: 0.82rem; }
        .shortcut-desc { font-size: 0.68rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        .shortcuts-grid .col-xl-2,
        .shortcuts-grid .col-lg-3,
        .shortcuts-grid .col-md-4,
        .shortcuts-grid .col-sm-6,
        .shortcuts-grid .col-6,
        .shortcut-icon { animation: none !important; }

        .shortcut-card:hover { transform: none; }
        .shortcut-card:hover .shortcut-icon { transform: none; }
    }

    [data-theme-mode="dark"] .shortcut-card {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.35),
                    inset 0 1px 0 rgba(255, 255, 255, 0.04);
        backdrop-filter: blur(12px);
    }

    [data-theme-mode="dark"] .shortcut-card:hover {
        background: rgba(255, 255, 255, 0.07);
        border-color: var(--shortcut-border, rgba(255, 255, 255, 0.15));
        box-shadow: 0 16px 48px var(--shortcut-shadow, rgba(0, 0, 0, 0.4)),
                    0 0 0 1px rgba(255, 255, 255, 0.06);
    }

    [data-theme-mode="dark"] .shortcut-shine {
        background: linear-gradient(
            105deg,
            transparent 40%,
            rgba(255, 255, 255, 0.04) 48%,
            rgba(255, 255, 255, 0.1) 50%,
            rgba(255, 255, 255, 0.04) 52%,
            transparent 60%
        );
    }

    [data-theme-mode="dark"] .shortcut-icon {
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
    }

    [data-theme-mode="dark"] .shortcut-ripple { opacity: 0.35; }

    [data-theme-mode="dark"] .shortcut-theme-blue   { --shortcut-icon-bg:rgba(59,130,246,.2); --shortcut-icon-bg-hover:rgba(59,130,246,.32); --shortcut-shadow:rgba(59,130,246,.35); }
    [data-theme-mode="dark"] .shortcut-theme-green  { --shortcut-icon-bg:rgba(16,185,129,.2); --shortcut-icon-bg-hover:rgba(16,185,129,.32); --shortcut-shadow:rgba(16,185,129,.35); }
    [data-theme-mode="dark"] .shortcut-theme-purple { --shortcut-icon-bg:rgba(139,92,246,.2); --shortcut-icon-bg-hover:rgba(139,92,246,.32); --shortcut-shadow:rgba(139,92,246,.35); }
    [data-theme-mode="dark"] .shortcut-theme-orange { --shortcut-icon-bg:rgba(249,115,22,.2); --shortcut-icon-bg-hover:rgba(249,115,22,.32); --shortcut-shadow:rgba(249,115,22,.35); }
    [data-theme-mode="dark"] .shortcut-theme-pink   { --shortcut-icon-bg:rgba(236,72,153,.2); --shortcut-icon-bg-hover:rgba(236,72,153,.32); --shortcut-shadow:rgba(236,72,153,.35); }
    [data-theme-mode="dark"] .shortcut-theme-teal   { --shortcut-icon-bg:rgba(20,184,166,.2); --shortcut-icon-bg-hover:rgba(20,184,166,.32); --shortcut-shadow:rgba(20,184,166,.35); }
    [data-theme-mode="dark"] .shortcut-theme-cyan   { --shortcut-icon-bg:rgba(6,182,212,.2); --shortcut-icon-bg-hover:rgba(6,182,212,.32); --shortcut-shadow:rgba(6,182,212,.35); }
    [data-theme-mode="dark"] .shortcut-theme-gold   { --shortcut-icon-bg:rgba(245,158,11,.2); --shortcut-icon-bg-hover:rgba(245,158,11,.32); --shortcut-shadow:rgba(245,158,11,.35); }
    [data-theme-mode="dark"] .shortcut-theme-red    { --shortcut-icon-bg:rgba(239,68,68,.2); --shortcut-icon-bg-hover:rgba(239,68,68,.32); --shortcut-shadow:rgba(239,68,68,.35); }
    [data-theme-mode="dark"] .shortcut-theme-brown  { --shortcut-icon-bg:rgba(180,83,9,.2); --shortcut-icon-bg-hover:rgba(180,83,9,.32); --shortcut-shadow:rgba(180,83,9,.35); }
    [data-theme-mode="dark"] .shortcut-theme-indigo { --shortcut-icon-bg:rgba(99,102,241,.2); --shortcut-icon-bg-hover:rgba(99,102,241,.32); --shortcut-shadow:rgba(99,102,241,.35); }

    /* ── Shortcut cards (مكوّن x-dashboard-shortcut القديم — يستخدمه أيضاً قسم
       "إدارة صفوفي وموادي" أدناه ولوحة الطالب، فلا يُمس) ── */
    .dashboard-shortcut {
        display: block;
        background: var(--custom-card-bg, var(--default-background));
        border: 1px solid var(--default-border);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .dashboard-shortcut__body {
        text-align: center;
        padding: 1rem 0.75rem;
    }
    .dashboard-shortcut__icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 0.65rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: transform 0.25s ease;
    }
    .dashboard-shortcut__title {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.2rem;
        color: var(--default-text-color);
    }
    .dashboard-shortcut__subtitle {
        display: block;
        font-size: 0.75rem;
        color: var(--text-muted, #6c757d);
    }
    .dashboard-shortcut__extra {
        margin-top: 0.5rem;
    }
    .dashboard-shortcut:hover {
        transform: translateY(-4px);
        text-decoration: none;
    }
    .dashboard-shortcut:focus-visible {
        outline: 2px solid rgb(var(--primary-rgb, 13, 110, 253));
        outline-offset: 2px;
    }
    [data-theme-mode="dark"] .dashboard-shortcut {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
    }
    [data-theme-mode="dark"] .dashboard-shortcut:hover {
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.4);
    }

    .dashboard-shortcut--primary .dashboard-shortcut__icon { background: rgba(var(--primary-rgb, 13, 110, 253), 0.12); color: rgb(var(--primary-rgb, 13, 110, 253)); }
    .dashboard-shortcut--success .dashboard-shortcut__icon { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .dashboard-shortcut--info .dashboard-shortcut__icon { background: rgba(13, 202, 240, 0.12); color: #0dcaf0; }
    .dashboard-shortcut--warning .dashboard-shortcut__icon { background: rgba(255, 193, 7, 0.15); color: #cc9a06; }
    .dashboard-shortcut--danger .dashboard-shortcut__icon { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .dashboard-shortcut--secondary .dashboard-shortcut__icon { background: rgba(108, 117, 125, 0.12); color: #6c757d; }
    .dashboard-shortcut--purple .dashboard-shortcut__icon { background: rgba(111, 66, 193, 0.12); color: #6f42c1; }
    .dashboard-shortcut--teal .dashboard-shortcut__icon { background: rgba(32, 201, 151, 0.12); color: #20c997; }
    .dashboard-shortcut--orange .dashboard-shortcut__icon { background: rgba(253, 126, 20, 0.12); color: #fd7e14; }
    .dashboard-shortcut--indigo .dashboard-shortcut__icon { background: rgba(102, 16, 242, 0.12); color: #6610f2; }
    .dashboard-shortcut--muted .dashboard-shortcut__icon { background: rgba(108, 117, 125, 0.1); color: var(--text-muted, #6c757d); }

    .dashboard-shortcut--primary:hover { border-color: rgba(var(--primary-rgb, 13, 110, 253), 0.45); box-shadow: 0 8px 20px rgba(var(--primary-rgb, 13, 110, 253), 0.15); }
    .dashboard-shortcut--success:hover { border-color: rgba(25, 135, 84, 0.45); box-shadow: 0 8px 20px rgba(25, 135, 84, 0.15); }
    .dashboard-shortcut--info:hover { border-color: rgba(13, 202, 240, 0.45); box-shadow: 0 8px 20px rgba(13, 202, 240, 0.15); }
    .dashboard-shortcut--warning:hover { border-color: rgba(255, 193, 7, 0.5); box-shadow: 0 8px 20px rgba(255, 193, 7, 0.15); }
    .dashboard-shortcut--danger:hover { border-color: rgba(220, 53, 69, 0.45); box-shadow: 0 8px 20px rgba(220, 53, 69, 0.15); }
    .dashboard-shortcut--secondary:hover { border-color: rgba(108, 117, 125, 0.45); box-shadow: 0 8px 20px rgba(108, 117, 125, 0.12); }
    .dashboard-shortcut--purple:hover { border-color: rgba(111, 66, 193, 0.45); box-shadow: 0 8px 20px rgba(111, 66, 193, 0.15); }
    .dashboard-shortcut--teal:hover { border-color: rgba(32, 201, 151, 0.45); box-shadow: 0 8px 20px rgba(32, 201, 151, 0.15); }
    .dashboard-shortcut--orange:hover { border-color: rgba(253, 126, 20, 0.45); box-shadow: 0 8px 20px rgba(253, 126, 20, 0.15); }
    .dashboard-shortcut--indigo:hover { border-color: rgba(102, 16, 242, 0.45); box-shadow: 0 8px 20px rgba(102, 16, 242, 0.15); }
    .dashboard-shortcut--muted:hover { border-color: var(--default-border); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); }

    .dashboard-shortcut:hover .dashboard-shortcut__icon {
        transform: scale(1.08);
    }

    [data-theme-mode="dark"] .dashboard-shortcut__extra .badge.bg-light {
        background: rgba(255, 255, 255, 0.1) !important;
        color: var(--default-text-color) !important;
        border: 1px solid var(--default-border);
    }

    @media (prefers-reduced-motion: reduce) {
        .dsc-card, .dsc-link, .dsc-bubble, .dsc-glow, .dsc-icon-ring, .dsc-icon-circle {
            animation: none !important;
        }
        .dashboard-stat-card,
        .dashboard-stat-card:hover,
        .dsc-card,
        .dsc-card:hover,
        .dsc-card:hover .dsc-icon-circle,
        .dsc-card:hover .dsc-value,
        .dashboard-shortcut,
        .dashboard-shortcut:hover,
        .dashboard-shortcut__icon {
            transition: none;
            transform: none;
        }
    }
</style>
