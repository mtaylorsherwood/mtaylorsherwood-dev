# Post-launch backlog

Feature work identified during the pre-launch review (9 Sep 2026). None of it
blocks launch. Each entry below is written to be pasted straight into a GitHub
issue — title, labels, context, acceptance criteria.

Ordered by how much the existing data model already supports them, which is
roughly the same as ordering by effort.

For bugs and hardening items rather than features, see
[launch-review.md](launch-review.md). For the sitemap and RSS feed — already
scoped in detail — see [todo-seo-sitemap-rss.md](todo-seo-sitemap-rss.md).

---

- [ ] **1. Browse posts by type and category**
- [ ] **2. Per-book permalinks and reviews**
- [ ] **3. A `/now` page**
- [ ] **4. Reading time and last-updated on articles**
- [ ] **5. Previous / next links on articles**
- [ ] **6. A contact route**

---

## 1. Browse posts by type and category

**Labels:** `enhancement`, `seo`, `content-discovery`

`PostType` already distinguishes Post, Recap, Garden Update and Project Update,
and `PostCategory` has General, Life and Tech. Both are cast on the model, both
reach the frontend, and both render as badges on the cards. What is missing is
any route that filters by either — so `/blog` is one undifferentiated stream and
`Site\PostController::index()` has no filter parameter at all.

The practical effect: someone who arrives for the garden updates has no way to
find the rest of them, and there is no landing page for "Tech" that could ever
rank on its own. This is the largest content-discovery gap on the site, and the
data is entirely in place — this is routing and a filter clause, not modelling.

**Acceptance criteria**

- `/blog` accepts a type and/or category filter and shows the active filter in
  the UI.
- Each filter combination has a distinct, indexable URL with its own title and
  meta description via `SeoHead`.
- Filter pages are linked from somewhere crawlable (the badges on each card are
  the natural place — make them links).
- The existing `Post::scopePosts()` / `scopeRecaps()` split is respected, so
  recaps keep routing through `recaps.show`.

**Pointers:** `app/Enums/PostType.php`, `app/Enums/PostCategory.php`,
`app/Http/Controllers/Site/PostController.php`,
`resources/js/pages/site/blog/Index.vue`

---

## 2. Per-book permalinks and reviews

**Labels:** `enhancement`, `seo`

`books` has a `review` text column, but there is no per-book route. Reviews
therefore exist only as fragments inside the `/books` listing — they cannot be
linked, shared, or indexed independently. With 193 books in the table, that is
193 potential search entry points currently collapsed into one page.

This also relieves pressure on the `/books` page weight problem (see
[launch-review.md](launch-review.md) P2), since the full review text no longer
has to be in the index payload.

**Acceptance criteria**

- Books gain a unique `slug` (migration + backfill; mirror the `uniqueSlug()`
  helpers already in `PostController` / `ProjectController`).
- `/books/{slug}` renders title, author, series, dates, and the review.
- The page uses `SeoHead` with a JSON-LD `Book` or `Review` block.
- Books without a review still resolve (or 404 deliberately — decide which).
- Book cards on `/books` link through.
- The sitemap picks them up. Note this contradicts the current note in
  [todo-seo-sitemap-rss.md](todo-seo-sitemap-rss.md) that books get no
  individual entries — update that doc if this ships.

**Pointers:** `app/Models/Book.php`,
`app/Http/Controllers/Site/BookController.php`,
`resources/js/components/books/BookCard.vue`

---

## 3. A `/now` page

**Labels:** `enhancement`, `content`

Near-universal on personal sites of this shape, and almost all of it can be
derived from queries that already exist: currently-reading books
(`Book::currentlyReading()`), ongoing projects (`Project::ongoing()`), and the
most recent garden update or post. Mostly assembly rather than new modelling.

**Acceptance criteria**

- `/now` route and page, linked from the header or footer nav.
- Pulls live data rather than hardcoded copy, so it does not go stale.
- Has its own `SeoHead` title and description.

**Pointers:** `app/Models/Book.php` (scopes already exist),
`app/Models/Project.php`, `resources/js/components/site/navigation.ts`

---

## 4. Reading time and last-updated on articles

**Labels:** `enhancement`, `good-first-issue`

`updated_at` already flows through to `article:modified_time` in the OG tags but
is never shown to readers. Reading time is a word count away from
`Post::toHtml()`, which is already cached — so both are effectively free at
render time.

Both signal that a post is maintained rather than abandoned, which matters
disproportionately for engineering writing.

**Acceptance criteria**

- Reading time computed server-side and cached alongside the rendered HTML (do
  not recompute per request, and do not do it in JS).
- "Last updated" shown only when it is meaningfully later than `published_at` —
  otherwise it is noise on every post.

**Pointers:** `app/Models/Post.php` (`toHtml()`, `excerpt()`),
`resources/js/components/site/PostLayout.vue`

---

## 5. Previous / next links on articles

**Labels:** `enhancement`, `good-first-issue`

The back button in `PostLayout` depends on `usePreviousUrl()`, which is
referrer/history state. A visitor arriving cold from Google therefore has no
onward path from an article except the header nav — the button does not even
render. Adjacent-post links keep them reading and give crawlers more internal
links to follow.

**Acceptance criteria**

- Previous and next resolved by `published_at` within the same post type, so a
  blog post links to blog posts and a recap links to recaps.
- Renders correctly at the ends of the range (first post has no previous).
- Two extra queries at most; ideally one.

**Pointers:** `app/Http/Controllers/Site/PostController.php@show`,
`app/Http/Controllers/Site/RecapController.php@show`,
`resources/js/components/site/PostLayout.vue`

---

## 6. A contact route

**Labels:** `enhancement`, `good-first-issue`

The home page links GitHub and LinkedIn only, though `MailIcon` and
`MailOutlineIcon` already exist unused in the icon set. For a site partly aimed
at people evaluating you professionally, an email link is the one call to action
worth having.

A `mailto:` is enough — a contact form means spam handling, validation, queued
mail and a honeypot, for a site with one author.

**Acceptance criteria**

- Email reachable from the home page and/or About page.
- Address lives in `resources/js/lib/site.ts` alongside the existing social
  links, not hardcoded in a component.

**Pointers:** `resources/js/lib/site.ts`,
`resources/js/components/site/icons/MailIcon.vue`,
`resources/js/pages/site/Home.vue`
