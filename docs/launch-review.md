# Pre-launch review — findings

Reviewed 9 Sep 2026 against `main` @ `4d47de8`, targeting **Laravel Cloud** with
DNS and proxying through **Cloudflare**.

Companion documents:

- [deploy-checklist.md](deploy-checklist.md) — what has to happen on deploy day.
- [post-launch-backlog.md](post-launch-backlog.md) — feature work, issue-ready.
- [todo-seo-sitemap-rss.md](todo-seo-sitemap-rss.md) — sitemap and RSS, already
  scoped.

Findings are numbered so they can be referenced from commits and issues. Items
marked **verified** were confirmed by running the thing, not by reading the
code; the rest are read from source and should be treated as high-confidence but
unproven.

Baseline at time of review: **133 tests, 655 assertions, passing**. No secrets in
git history. `.env` correctly ignored.

---

## Blockers (fixed)

Recorded for history — all three are resolved in the repo, though B2 and B3 each
have a dashboard-side half that is tracked in
[deploy-checklist.md](deploy-checklist.md).

### B1 — Staged migration would have failed `artisan migrate` &nbsp;`[verified]`

`git status` reported `AD` on
`database/migrations/2026_08_30_000000_add_is_reread_to_books_table.php` —
staged as an addition, then deleted from the working tree. Commit `4d47de8`
folded `is_reread` into the base `2026_08_04_000000_create_site_tables.php`
instead, so both migrations added the same column.

Committing as-is would have meant: production runs the base migration (adds
`is_reread`), then runs this one (adds it again) → duplicate column error, with
migrations halted part-way through a first deploy. Local never hits it, because
local was migrated before the fold-in.

**Fixed:** removed from the index with `git rm --cached`.

### B2 — All SEO metadata depended on SSR, and SSR fails silently &nbsp;`[verified]`

Every meta tag on the public site — title, description, canonical, OG, Twitter
Card, JSON-LD — is emitted by `SeoHead.vue`, on the client. `app.blade.php`
contains only a static `<title>` of the app name. So the entire SEO and social
surface exists only if Inertia server-renders the page.

Confirmed working in dev: `/blog` returns fully rendered HTML with `og:title`,
`canonical` and the article markup already in `<head>`, served through the Vite
plugin's SSR dev mode. Confirmed the production bundle builds: `npm run
build:ssr` exits 0 and produces a 218 KB entry.

The risk is the failure mode, not the build. In production,
`Inertia\Ssr\HttpGateway::dispatch()` posts to the SSR server and, on any
failure, catches the exception and returns `null` — Inertia then falls back to
client-only rendering. No exception surfaces, no 500, no log line. The site
looks completely fine in a browser while being invisible to every crawler and
link unfurler.

**Fixed (code side):**

- `AppServiceProvider::configureSsrFailureLogging()` listens for
  `Inertia\Ssr\SsrRenderFailed` and logs it at error level with the component,
  URL, error type and any hint. A dead SSR worker now shows up in the logs
  instead of as a quiet ranking decline.
- `config/inertia.php` now reads `ssr.enabled` from `INERTIA_SSR_ENABLED`
  instead of hardcoding `true`, so SSR can be turned off deliberately per
  environment rather than only failing by accident.
- `phpunit.xml` sets `INERTIA_SSR_ENABLED=false`. Previously the test suite
  inherited `enabled => true`, and because `public/hot` exists during local
  development, every Inertia render in the suite made a real HTTP round trip to
  the Vite dev server. Tests no longer depend on a dev server being up.

**Still required on deploy day:** the "Use Inertia SSR" toggle on the App
compute cluster, and a build command of `npm run build:ssr`. See
[deploy-checklist.md](deploy-checklist.md) §1.

### B3 — `CLOUDFLARE_IMAGES_DOMAIN` empty makes every resize a no-op &nbsp;`[verified]`

`CloudflareImageUrl::make()` falls back to the plain storage URL when the domain
is unset. Every call — `url(400)`, `url(800)`, `url(1200)` — then returns the
identical full-resolution original, with no width applied and no WebP/AVIF
re-encode. The whole transformation layer, including the inline-image rewriting
in `Post::rewriteImages()`, is inert. Nothing looks broken; the images are just
several times larger than intended.

**Fixed (code side):** `.env.example` now documents the variable, its production
value, and the fact that it must stay blank locally (local dev uses
`IMAGES_DISK=public`, and pointing at the Cloudflare domain would break every
image in dev).

**Still required on deploy day:** set the variable in the Laravel Cloud
environment *and* enable Image Transformations on the Cloudflare zone — the
`/cdn-cgi/image/` path only works where the feature is switched on. See
[deploy-checklist.md](deploy-checklist.md) §2.

---

## Security

The strongest area of the codebase. Nothing below is an open door — these are
hardening items.

### Already right — do not regress these

- No public registration and no password reset. `config/fortify.php` enables
  only email verification, two-factor and passkeys; `Features::registration()`
  and `Features::resetPasswords()` are deliberately absent.
- Two-factor with confirmation, passkeys, and `RequirePassword` gating
  `/settings/security`.
- Separate rate limiters for login, two-factor and passkey attempts, plus
  `throttle:6,1` on password updates.
- Both `CommonMarkConverter` instances use `html_input => 'escape'`, so a post
  or project body cannot inject markup through the `v-html` render on the
  public site.
- Uploads restricted by mime, dimensions and size, stored under a SHA-256
  content hash. SVG is excluded — keep it that way.
- `DB::prohibitDestructiveCommands()` and a 12-character `uncompromised()`
  password policy, both correctly production-gated.
- `#[Hidden]` on `User` covers password, 2FA secret, recovery codes and remember
  token.

### S1 — No security response headers &nbsp;`[fixed]`

Checked against a live response: `X-Content-Type-Options`, `Referrer-Policy`,
`X-Frame-Options`, `Permissions-Policy` and `Content-Security-Policy` are all
missing.

- [x] Add the first four. A short middleware covers it, or a Cloudflare
      Transform Rule if you would rather not touch the app.
      `[done]` — `App\Http\Middleware\AddSecurityHeaders` sets
      `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`,
      `Referrer-Policy: strict-origin-when-cross-origin` and a
      `Permissions-Policy` that empties out `camera`, `microphone`,
      `geolocation` and `payment` (blocked for every origin, this site
      included — it doesn't use any of them). Appended to the `web`
      middleware group in `bootstrap/app.php`, alongside
      `HandleInertiaRequests`. Covered by
      `tests/Feature/Middleware/SecurityHeadersTest.php`.
- [ ] CSP is more work, because of the inline `<style>` in `app.blade.php` and
      Inertia's JSON island. Reasonable to defer; not reasonable to forget.

### S2 — Real visitor IPs behind the Cloudflare + Laravel Cloud proxy chain

**Downgraded after confirming the hosting setup.** Laravel's `TrustProxies`
middleware auto-trusts when `LARAVEL_CLOUD=1` is present, which Laravel Cloud
injects — see
`Illuminate\Http\Middleware\TrustProxies::setTrustedProxyIpAddresses()`. So
HTTPS detection and URL generation are correct without any `->trustProxies()`
call in `bootstrap/app.php`, and nothing needs changing for launch.

The residual quirk: that auto-trust path trusts the *calling* IP only, and with
Cloudflare proxying in front of Laravel Cloud there are two hops. Symfony walks
`X-Forwarded-For` right to left and stops at the first untrusted entry, which
will be a Cloudflare edge IP rather than the visitor. The one place that matters
is the login limiter in `FortifyServiceProvider`, which keys on `email|ip` —
attempts from different visitors share an IP bucket.

For a single-user site behind 2FA and passkeys the practical impact is small
(coarser throttling, not weaker authentication), and the obvious fix — trusting
`0.0.0.0/0` so the leftmost `X-Forwarded-For` entry wins — is only safe if the
origin cannot be reached directly, which on Laravel Cloud it can be via the
`*.laravel.cloud` hostname. Reading `CF-Connecting-IP` explicitly is the correct
fix if this ever matters.

- [ ] Revisit only if login throttling starts misbehaving. Not launch work.

### S3 — `SESSION_SECURE_COOKIE` is unset

Absent from `.env` and `.env.example`, so the production value depends on
whatever the host injects.

- [ ] Set `SESSION_SECURE_COOKIE=true` explicitly.
- [ ] `SESSION_ENCRYPT=true` too — costs nothing on a single-user admin.

Both are in [deploy-checklist.md](deploy-checklist.md) §2.

### S4 — Inertia DevTools is gated on `APP_ENV` alone

`Inertia\DevTools\DevTools::enabled()` defaults to
`app()->environment('local')`, and `config/inertia.php` has no `devtools` key to
override it. Correct today — but it means a mistyped `APP_ENV` exposes
`/_inertia/devtools/entries`, which replays recorded page props.

- [ ] Add `'devtools' => ['enabled' => env('INERTIA_DEVTOOLS', false)]` so it is
      off by construction rather than by environment.

### S5 — The `local` disk registers two unused HTTP routes

`'serve' => true` on the `local` disk registers `GET` and `PUT
/storage/{path}`. The `PUT` is signed-URL gated (`ReceiveFile::hasValidSignature()`)
so it is not exploitable, but nothing in this app serves that disk over HTTP —
images go to R2.

- [ ] Set `'serve' => false` in `config/filesystems.php` to drop both routes.

### S6 — A password change does not invalidate other sessions

`SecurityController::update()` updates the hash and returns.

- [ ] Add `Auth::logoutOtherDevices($request->password)` so "change my password"
      actually ends a stolen session, which is the reason you would change it
      under duress.

---

## Speed and caching

Server-side caching is already well handled — rendered markdown, book lists and
lookups are all cached with proper invalidation hooks on save and delete. The
waste is on the client, and it is concentrated in one place.

### P1 — Every public visitor downloads the whole dashboard UI &nbsp;`[fixed]`

`resources/js/app.ts` statically imports `AppLayout`, `AuthLayout` and
`SettingsLayout`, and calls `initializeFlashToast()`, which pulls in
`vue-sonner`. All of it lands in the entry chunk.

Walking the built manifest: the entry chunk is 173 KB and drags in reka-ui
primitives (`Teleport`, `Presence`, `VisuallyHidden`, `Primitive`,
`useForwardExpose`) plus lucide's icon factory and a `folder-git-2` icon that
only the sidebar uses. Net effect — a blog post page ships roughly **461 KB of
JavaScript, of which the page's own component is 0.8 KB**. None of the layout
code, the toaster or those icons is reachable without logging in.

Two changes, and the second makes the first safe:

- [x] Exclude authenticated routes from SSR. They have no SEO value and each one
      costs a round trip to the SSR worker.
      `[done]` — used the `$withoutSsr` property on `HandleInertiaRequests`
      (the documented "via middleware" mechanism, backed by the same
      `ExcludesSsrPaths`/`HttpGateway::except()` this note originally pointed
      at — `Inertia::ssr()->except()` doesn't exist on the installed version,
      the facade method is `Inertia::withoutSsr()`). Excludes `dashboard`,
      `dashboard/*`, `settings`, `settings/*`, `login`,
      `two-factor-challenge`, `email/verify` and `user/*` — that last group
      covers password confirmation and 2FA management, and together with
      `login`/`two-factor-challenge`/`email/verify` it's every Fortify view
      that isn't already under `dashboard/*` or `settings/*`.
- [x] Make the non-site layouts async so they become their own chunk —
      `defineAsyncComponent(() => import('@/layouts/AppLayout.vue'))` in the
      `layout` resolver, keeping `SiteLayout` eager.
      `[done]` — same for `AuthLayout` and `SettingsLayout`. Entry chunk is
      now 25 KB (from 173 KB); `AppLayout`, `AuthLayout` and `Layout`
      (settings) each build as their own chunk.
- [x] Move `initializeFlashToast()` behind the same boundary. The public site has
      no forms and never flashes a toast.
      `[done]` — dynamically imported on first non-site layout resolution,
      guarded so it only fires once. `vue-sonner` no longer appears in the
      entry chunk.

Verify SSR still renders dashboard pages correctly after the async change, or
confirm they are excluded by the first bullet.
`[done]` — excluded by the first bullet: every route using the async layouts
is also SSR-excluded, so there's no overlap to verify. Confirmed with
`tests/Feature/SsrExclusionTest.php` (dashboard/login/settings skip SSR, a
public page still dispatches to it) and a production + SSR build
(`npm run build`, `npm run build:ssr`).

### P2 — `/books` is 312 KB of HTML and grows linearly &nbsp;`[measured]`

Timed against every public page, local, warm cache:

```
/           380ms   29,321 B   props   1,919 B
/blog       300ms   33,724 B   props   3,412 B
/books      568ms  312,465 B   props  31,067 B   <--
/projects   264ms   17,373 B   props     291 B
/recaps     278ms   22,101 B   props     982 B
/about      267ms   20,018 B   props     271 B
```

111 started books today, out of 193 rows. At 300 books that page is around
850 KB.

The client-side filtering genuinely needs the full list in props, so that part is
fine as designed. The problem is that every book is *also* fully server-rendered
on every request.

- [ ] Render "currently reading" plus the current year up front, and mount
      earlier years on expand. Keeps filtering instant, cuts the rendered markup
      to a fraction.
- [ ] Drop `review` from the index selection if the cards do not need it inline
      — it is a `text` column and likely most of the 31 KB of props.
      Per-book pages ([post-launch-backlog.md](post-launch-backlog.md) item 2)
      would make this straightforward.

### P3 — Featured images are neither lazy nor prioritised

Inline body images are handled correctly — `Post::rewriteImages()` adds
`loading="lazy"` plus real `width`/`height` from the images table. The featured
images do not go through that path:

- [ ] `resources/js/pages/site/blog/Index.vue:46` renders up to 10 cover images
      with no `loading="lazy"` — all ten fetch eagerly on `/blog`.
- [ ] `resources/js/components/site/PostLayout.vue:44` is the hero on every
      article and therefore the LCP element. It wants `fetchpriority="high"` and
      explicit dimensions, and must *not* be lazy.
- [ ] `resources/js/pages/site/projects/Show.vue` gallery has the same gap.

The `aspect-video` classes do reserve space, so there is no layout shift — this
is purely bandwidth and LCP.

### P4 — Syntax highlighting is styled but never happens &nbsp;`[verified]`

`resources/css/prism.css` is imported into `app.css` (line 5) and styles
`.token.*` classes — but there is no Prism, Shiki or highlight.js anywhere in
`composer.json`, `package.json` or the application code. Code blocks render as
plain `<pre><code class="language-php">` with no tokens for those rules to
match, so the stylesheet ships to every visitor and applies to nothing.

It also references stock Tailwind `zinc`, `pink` and `teal` variables rather than
the site's ash-grey / paprika palette, so it would clash even if it did fire.

For an engineering blog this is worth doing properly rather than deleting:

- [ ] Highlight server-side inside `Post::renderHtml()`, so it lands in the
      already-cached HTML and costs the browser nothing. A CommonMark
      highlighting extension fits the existing pipeline.
- [ ] Re-map `prism.css` onto the site palette, or replace it with a theme
      generated from those tokens.

### P5 — No HTTP caching on public pages, and it cannot be added cheaply

Every response carries `Cache-Control: no-cache, private` and a `Set-Cookie`, so
Cloudflare cannot cache any HTML even with the proxy enabled. Static assets are
fine — Vite output is content-hashed and picked up by Cloudflare's default rules,
and uploaded images are stored with `public, max-age=31536000, immutable`.

For expected traffic this is fine, and SSR plus the existing server-side caches
keep it fast.

- [ ] Only if you get linked from somewhere big: the public routes have no forms
      and nothing user-specific, so they could move to a route group without
      session and cookie middleware and emit
      `public, s-maxage=300, stale-while-revalidate=86400`. Real work — do not
      do it speculatively.

### P6 — Leftover template assets &nbsp;`[verified unreferenced]`

`resources/js/images/planetaria-design-system.png` and
`resources/js/images/photos/image-1..5.jpg` are Spotlight-template holdovers.
No references anywhere in source, and absent from `public/build`. Around 200 KB
of repo weight doing nothing.

- [ ] Delete them.

---

## SEO and Open Graph

The mechanical pass documented in
[todo-seo-sitemap-rss.md](todo-seo-sitemap-rss.md) was done well.
`SeoHead.vue` is wired into every public page, canonicals are absolute and
correct, articles carry published and modified times plus JSON-LD, and the 404
returns a genuine 404 with `noindex` (verified). What is left is the discovery
layer.

### X1 — No fallback `og:image`

`SeoHead` only emits `og:image` when an image prop is passed. So Home, About,
Books, Projects and the blog index — plus any post without a featured image —
unfurl with no image at all and drop to `twitter:card=summary`.

Given the site's whole first impression on social is that card, this is the
highest-value small job on the list.

- [ ] Add a static 1200×630 card in `public/` and default to it in `SeoHead`.
- [ ] Add `og:image:width`, `og:image:height` and `og:image:alt` so LinkedIn and
      Slack render immediately instead of re-fetching.
- [ ] Add `og:locale` and `article:author` while you are in there.

### X2 — No sitemap, no RSS feed

Both fully scoped in [todo-seo-sitemap-rss.md](todo-seo-sitemap-rss.md), parked
behind the placeholder copy. That reasoning held while nothing was launching;
worth revisiting now. RSS is still how most people follow a developer's blog,
and the sitemap is the fastest route to getting new URLs indexed. Neither needs
the home page copy to be final — they only surface published posts.

- [x] Sitemap, then feed, then the `robots.txt` `Sitemap:` line and the
      `<link rel="alternate" type="application/rss+xml">` autodiscovery tag.
      `[done]` — `/sitemap.xml`, `/feed.xml`, and a dynamic `/robots.txt`
      (built from `config('app.url')` rather than hardcoded) all shipped, plus
      a visible RSS link in the footer. See
      [todo-seo-sitemap-rss.md](todo-seo-sitemap-rss.md).

### X3 — `/recaps` is orphaned from navigation &nbsp;`[verified]`

`siteNavItems` lists Home, About, Blog, Books, Projects; the footer drops Blog as
well. `/recaps` and every `/recaps/{slug}` are reachable only from the
recent-recaps panel on `/books`. Orphaned pages get crawled late and rank poorly,
and reading recaps are some of the most linkable content on a site like this.

- [ ] Add Recaps to the nav, or fold recaps into `/blog` and keep the routes as
      aliases.

**Pointer:** `resources/js/components/site/navigation.ts:7-20`

### X4 — JSON-LD covers articles but not you

`SeoHead` emits `BlogPosting` for posts and recaps and nothing else.

- [ ] Add a `Person` block on the home page — `name`, `url`, `jobTitle`,
      `sameAs: [github, linkedIn]`. Every value is already in
      `resources/js/lib/site.ts`.

### X5 — Paginated blog pages are indexable and identical

`/blog?page=2` gets a correct self-referencing canonical, but the same title and
description as page 1, and no `rel="prev"` / `rel="next"`.

- [ ] Either number the title ("Blog — Page 2") or `noindex` pages 2+.
      `SeoHead` already takes a `noindex` prop.

### X6 — Two different names for the same person

`og:site_name` comes from `APP_NAME` ("M Taylor-Sherwood") while bylines,
copyright and the JSON-LD author come from `site.author` ("Martyn
Taylor-Sherwood"). Search engines and social cards will show both.

- [ ] Pick one and make the other derive from it.

### X7 — No `theme-color` for a dark-only site

`app.blade.php` commits to dark and paints `#141310` inline to avoid a flash, but
there is no `<meta name="theme-color">`, so mobile Safari and Chrome draw their
browser chrome in default light against the dark page.

- [ ] Add `<meta name="theme-color" content="#141310">`. One line.
