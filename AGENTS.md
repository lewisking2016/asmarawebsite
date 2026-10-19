# AGENTS.md — Asmara Restaurant Admin

This file is auto-loaded by every agent that runs in this workspace. It documents the design system, conventions, and the skills that should be invoked when working on this project.

## Design system: premium admin v2

The admin UI ships its own design tokens + components in `backend/admin/dashboard.css`. **All admin pages must use this design system** — no raw HTML defaults, no inline styles for layout primitives.

### Tokens (CSS custom properties)

All tokens are prefixed `--ad-` to avoid collisions with existing root tokens:

| Token | Purpose |
|---|---|
| `--ad-rose` (`#ed174b`) | Primary accent (rose) |
| `--ad-rose-tint` (`rgba(237,23,75,0.06)`) | Rose background tint |
| `--ad-cream` (`#fbf8f5`) | Surface background |
| `--ad-line` (`#ece3da`) | Border / divider |
| `--ad-ink` (`#15121a`) | Primary text |
| `--ad-ink-3` (`#554f5e`) | Secondary text |
| `--ad-ink-4` (`#8b8597`) | Muted / placeholder |
| `--ad-radius-m` (`16px`) | Card / panel radius |
| `--ad-radius-pill` (`999px`) | Buttons / chips |
| `--ad-shadow-1` / `-2` / `-3` | Layered shadows |
| `--ad-ease` (`cubic-bezier(0.22, 1, 0.36, 1)`) | Default motion curve |

### Typography

- **Display:** Playfair Display (700). Used on `.ad-page-header h1`, form section titles.
- **UI:** Plus Jakarta Sans (400/500/600/700). Default body font.
- **Numerals:** Space Grotesk (700). Used on `.ad-stat-value`.

### Component classes (opt-in)

Replace legacy `<div class="form-group">…</div>` style markup with these premium classes. Existing legacy classes still work but the premium ones read as designed surfaces:

| New class | Purpose |
|---|---|
| `.ad-page-header` | Top of every admin page. Eyebrow chip + Playfair h1 + description. |
| `.ad-page-eyebrow` | Small uppercase pill above h1. |
| `.ad-card`, `.ad-card-tinted`, `.ad-card-bordered`, `.ad-card-quiet` | Card surfaces. |
| `.ad-card-header` / `.ad-card-body` / `.ad-card-padded-lg` | Card structure. |
| `.ad-btn`, `.ad-btn-primary`, `.ad-btn-ghost`, `.ad-btn-soft`, `.ad-btn-danger`, `.ad-btn-icon`, `.ad-btn-lg` | Buttons. |
| `.ad-field` / `.ad-label` / `.ad-label.ad-required` / `.ad-hint` / `.ad-input` / `.ad-textarea` / `.ad-select` / `.ad-input-group` + `.ad-leading` | Forms. |
| `.ad-grid-2` | 1fr 1fr grid that collapses on mobile. |
| `.ad-form-actions` | Bottom submit/cancel row. |
| `.ad-toolbar` + `.ad-toolbar-section` + `.ad-toolbar-divider` | Filter / action row. |
| `.ad-search` | Pill search input with leading icon. |
| `.ad-segmented` | Card / List view toggle. |
| `.ad-select-wrap` + `.ad-select.ad-trigger` | Pill-trigger styled `<select>`. |
| `.ad-chip`, `.ad-chip-rose`, `.ad-chip-emerald`, `.ad-chip-amber`, `.ad-chip-cobalt`, `.ad-chip-crimson`, `.ad-chip-ink` + `.ad-chip-dot` | Status pills. |
| `.ad-stat-row` + `.ad-stat-tile` (with `-rose`, `-amber`, `-emerald`, `-cobalt` variants) | Dashboard tiles. |
| `.ad-event-card`, `.ad-event-card-media`, `.ad-event-card-body`, `.ad-event-card-head`, `.ad-event-card-cat`, `.ad-event-card-title`, `.ad-event-card-desc`, `.ad-event-card-details`, `.ad-event-card-footer` | Event cards. |
| `.ad-cover-uploader` / `.ad-cover-preview` / `.ad-cover-empty` / `.ad-cover-actions` / `.ad-cover-file` | Cover photo uploader. |
| `.ad-gallery-strip` / `.ad-gallery-thumb` / `.ad-gallery-remove` / `.ad-gallery-drop` | Gallery. |
| `.ad-branch-grid` / `.ad-branch-chip` | Multi-branch selector. |
| `.ad-empty` / `.ad-empty-icon` | Empty states. |
| `.ad-dropdown` + `.ad-dropdown-trigger` + `.ad-dropdown-menu` | Custom dropdown trigger. |

### Cache-busting requirement

`<link rel="stylesheet" href="/asmaraadmin/dashboard.css?v=<?php echo filemtime(__DIR__ . '/dashboard.css'); ?>">` — keep this on every commit so visitors get fresh CSS after each deploy.

## Page templates

Each admin page should follow this skeleton:

```php
<?php include 'header.php'; ?>

<div class="ad-page-header">
    <span class="ad-page-eyebrow">✦ Module</span>
    <h1>Module Title</h1>
    <p>One-line description for the admin user.</p>
</div>

<div class="page-content">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- page body -->
</div>

<?php include 'footer.php'; ?>
```

For listing pages with filters:

```php
<div class="ad-toolbar">
    <div class="ad-toolbar-section">
        <a href="?action=create" class="ad-btn ad-btn-primary">+ New item</a>
        <label class="ad-search"><svg…></svg><input …></label>
        <div class="ad-select-wrap"><select class="ad-select ad-trigger">…</select></div>
    </div>
    <div class="ad-segmented">…</div>
</div>
```

## Skills to invoke

The following skills are pre-installed both globally (`C:\Users\lewis\.codex\skills`) and project-locally (`.agents/skills`). When working on UI work, **invoke them up front** before writing code:

| Skill | When to use |
|---|---|
| `frontend-design` | When designing a new feature with intentional aesthetic direction (DFII, typography, motion). |
| `design-taste-frontend` | When polishing layouts to avoid generic / template-feeling UI. |
| `baseline-ui` | Before writing any CSS — enforce no-h-screen, no glows, single accent, reduced-motion. |
| `impeccable` | When reviewing a polished surface (Pass / Fail verdict, design-system violations). |
| `ui-review` | UX review with heuristic scoring. |
| `accessibility-compliance-accessibility-audit` | Before declaring any UI complete (WCAG 2.2 AA). |
| `web-design-guidelines` | General interface guidelines check. |
| `tailwind-design-system` | If migrating to Tailwind tokens. |
| `frontend-dev-guidelines` | Senior frontend reviews (architecture, perf, a11y). |
| `frontend-security-coder` | XSS / output sanitisation. |
| `product-design` | For product-level design strategy. |
| `web-design-guidelines` | App interface guideline compliance. |

## Architecture notes

- **Data layer:** PHP + JSON files (events, bookings etc) + MySQL via `database/Repository.php` classes.
- **Auth:** session-based (`security/Auth.php`).
- **Routing:** Apache rewrites `/asmaraadmin/*` → `backend/admin/*` (see `.htaccess`). For local dev, `php -S host:port router.php` is supported and must mirror the rewrite.
- **Image pipeline:** uploads go through `security/ImageOptimizer.php` to WebP/JPEG compression.

## Common gotchas

- **PHP event handlers use `$_POST['action_type']`** — the create / update / delete branches are switched on this string.
- **`asmara_event_image_url()`** normalises stored paths to absolute URLs from `/backend/uploads/events/...`. Always use it instead of `$event['image']` directly.
- **Helper functions** are in `backend/data/event_helpers.php`. Use `asmara_event_gallery_urls()` to get the proper featured-first gallery list.
- **router.php dev mode** gotcha: when starting `php -S host:port router.php`, the dev server sets `SCRIPT_FILENAME` to the router file. Don't use that variable to decide whether to serve static files — always drive decisions from `REQUEST_URI`.

## Deploy

- All admin UI changes ship with the cache-bust query string.
- Run `git push origin main` after committing — `router.php` is automatically picked up.
- For local dev: `php -S 127.0.0.1:8000 router.php` then visit `http://localhost:8000/asmaraadmin/events` (after logging in).