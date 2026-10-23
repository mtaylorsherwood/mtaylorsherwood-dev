# DONE: Sitemap + RSS feed

Status: shipped. The placeholder copy on Home/About/Projects (and Blog/Books/
Recaps) was replaced first, then this was implemented as scoped below.

- `/sitemap.xml` — [`SitemapController`](../app/Http/Controllers/Site/SitemapController.php),
  cached for an hour, static pages + published posts + published recaps.
- `/feed.xml` — [`FeedController`](../app/Http/Controllers/Site/FeedController.php),
  RSS 2.0 with full-content `<content:encoded>`, latest 20 published items.
- `/robots.txt` — [`RobotsController`](../app/Http/Controllers/Site/RobotsController.php),
  now served dynamically (was a static `public/robots.txt`) so the `Sitemap:`
  line is built from `config('app.url')` rather than a hardcoded domain.
- `Post::url()` added to route a post through `blog.show` or `recaps.show`
  depending on its type — reused by both the sitemap and feed views.
- RSS autodiscovery `<link>` added to `SeoHead.vue`, plus a visible "RSS" link
  in `SiteFooter.vue`.
- Tests: `tests/Feature/Site/{SitemapTest,FeedTest,RobotsTest}.php`.

Projects don't get individual sitemap entries either (only the `/projects`
listing) — that was out of scope for this pass, matching the plan above.

## What's already in place (context for whoever picks this up)

Done in the "mechanical SEO/OG" pass:

- [`resources/js/components/site/SeoHead.vue`](../resources/js/components/site/SeoHead.vue) —
  shared component wired into every public page (Home, About, Projects,
  books/blog/recaps index + show). Handles title, meta description,
  canonical link, OG tags, Twitter Card tags, and (for posts/recaps)
  `article:published_time`/`article:modified_time` + a JSON-LD `BlogPosting`
  block.
- `appUrl` is now a shared Inertia prop (`config('app.url')`, wired in
  [`app/Http/Middleware/HandleInertiaRequests.php`](../app/Http/Middleware/HandleInertiaRequests.php),
  typed in [`resources/js/types/global.d.ts`](../resources/js/types/global.d.ts)).
  `SeoHead` combines this with Inertia's own tracked `page.url` to build
  absolute canonical/OG URLs — reuse this same pattern for absolute URLs in
  the sitemap/feed rather than hardcoding a domain.
- `Post::excerpt()` and `Post::toHtml()` (cached, see `app/Models/Post.php`)
  already give you a plain-text summary and rendered HTML per post/recap —
  reuse both rather than re-deriving.
- `public/robots.txt` (now [`resources/views/robots.blade.php`](../resources/views/robots.blade.php),
  served via `RobotsController` — see above) disallows `/dashboard` and
  `/settings`. **Once the sitemap route exists, add a `Sitemap:` line
  pointing at it** — don't add the line before the route exists.
- Post types: `Post::scopePosts()` (articles + garden updates, excludes
  recaps) and `Post::scopeRecaps()` already split the two out — the sitemap
  and feed both need to route posts through `blog.show` and recaps through
  `recaps.show` (see `Site\PostController::index()` for the existing
  `type === 'recap' ? showRecap(...) : showPost(...)` pattern to mirror).

## Sitemap (`/sitemap.xml`)

No package needed for a site this size — hand-roll it.

- New route (public, no auth) + controller action returning
  `response($xml, 200, ['Content-Type' => 'application/xml'])`.
- Entries needed: `/`, `/about`, `/projects`, `/books`, `/blog`, `/recaps`,
  plus one entry per published post (`route('blog.show', $post->slug)`) and
  per published recap (`route('recaps.show', $recap->slug)`). Books don't
  get individual entries — there's no per-book detail route, only the
  `/books` listing.
- Use `updated_at` (already selected in the post/recap show payloads) for
  `<lastmod>`.
- Cache it — sitemap freshness isn't time-critical, a `Cache::remember('sitemap.xml', now()->addHour(), ...)`
  is enough; no need to wire it into the existing `Post`/`Book` model
  save/delete cache-invalidation hooks used elsewhere.
- Add the `Sitemap: https://<domain>/sitemap.xml` line to `robots.txt` once
  this exists.

## RSS/Atom feed (`/feed.xml` or `/blog/feed.xml`)

Also no package — Laravel doesn't ship a feed generator, and a hand-rolled
RSS 2.0 XML view is small.

- Query: `Post::query()->published()->orderByDesc('published_at')->limit(20)->get()`
  (posts + recaps + garden updates together, matching what `/blog` already
  shows).
- Per item: `title`, `link` (via the same posts-vs-recaps route split as the
  sitemap), `description` (`$post->excerpt()`), optionally full content via
  `$post->toHtml()` in a `<content:encoded>` CDATA block if you want
  full-text feeds rather than summaries, `pubDate` in RFC 822 format
  (`$post->published_at->format('D, d M Y H:i:s O')` — Carbon doesn't have a
  built-in RSS formatter), and `guid` (the post URL is fine, mark
  `isPermaLink="true"`).
- Once it exists: add `<link rel="alternate" type="application/rss+xml" title="..." href="...">`
  to `SeoHead.vue` (or `app.blade.php` if you want it on every page
  including non-`SeoHead` ones) for feed-reader auto-discovery, and probably
  a visible "RSS" link in the site footer/nav.

## Suggested order when resuming

1. Sitemap first (smaller, no new content-formatting concerns).
2. Feed second.
3. `robots.txt` sitemap line + feed auto-discovery link last, once both URLs
   are stable.
