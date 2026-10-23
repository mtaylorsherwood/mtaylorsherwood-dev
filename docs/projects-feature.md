# Projects feature — design + progress

Status: **done**. All items below are checked off; 125 tests passing, Pint,
PHPStan, `vue-tsc`, ESLint, Prettier all clean. Public pages verified live in
a browser (index grouping, detail page with body/links/gallery/related
posts); dashboard CRUD verified via automated tests only — login in this
particular browser harness hits a pre-existing session/CSRF quirk unrelated
to this feature (documented earlier in the session).

Kept as a permanent reference doc for this feature's design rationale — see
"Key design decisions" below if extending it later (e.g. the deferred items
at the bottom).

## Requirements (as given)

1. `/projects` is dynamic — backed by a `projects` table, not the hardcoded
   array in `Projects.vue`.
2. Each project may have a website URL and/or a GitHub URL — zero, one, or
   both.
3. A short "blurb" field, shown on the `/projects` index card.
4. A longer markdown "body", rendered (and cached, same pattern as
   `Post::toHtml()`) on the project's own detail page.
5. When creating/editing a **Post**, there's a way to associate it with a
   project (in addition to the existing type/category selects, not replacing
   them). That post still behaves normally on the blog, but also appears
   under the project's description on the project's own page.
6. A project can have up to 5 images (a gallery), reusing the existing image
   upload infrastructure from the images feature.
7. Remove the small placeholder logo images above each project title on the
   current static page.

## Key design decisions

- **No draft/publish workflow for projects.** Unlike `Post`, a `Project` row
  is live as soon as it exists — mirrors `Book`, which also has no
  draft/publish concept. Posts already have scheduling; projects didn't ask
  for it, so it's left out to avoid scope creep. Easy to add later if wanted.
- **Ordering**: projects list by `created_at` desc on the public index — no
  manual drag-reorder UI. Flagged as a possible future ask, not built now.
- **Slug**: auto-generated from title on create (via the same
  `Str::slug()` + numeric-suffix-on-collision pattern as
  `PostController::uniqueSlug()`), immutable after creation — mirrors posts.
- **Images**: extended the *existing* `images` table rather than a new
  pivot table — added nullable `project_id` (FK, `nullOnDelete`) alongside
  the existing nullable `post_id`, plus a nullable `position` (int, 0-4)
  used only for ordering a project's gallery. An image belongs to at most
  one of {post, project} in practice (nothing DB-level enforces that, but
  app code only ever sets one). The existing `images:prune` orphan-cleanup
  command needs no changes — it already targets `post_id IS NULL`, needs to
  also check `project_id IS NULL` (see task list).
  - Reused the existing generic `ImageController@store` upload endpoint
    as-is — no new upload route needed.
  - Project image sync is **add/remove/reorder**, not additive-only like
    `Post::reconcileImages()` (which never detaches images removed from a
    post body, a known/accepted gap noted at the time). Projects have an
    explicit bounded list (max 5) submitted with the form each time, so a
    proper sync (detach anything no longer in the list, set `position` for
    what remains) is both easy and correct here — new method
    `Project::syncImages(array $imageIds): void`.
- **Post ↔ Project link**: added nullable `posts.project_id` (FK,
  `nullOnDelete`). Independent of `PostType` (post/recap/garden_update) — a
  post keeps its normal type and blog behaviour either way. The dashboard
  post form gets an additional "Project (optional)" select alongside the
  existing Type/Category selects. The project's public show page lists its
  associated **published** posts (any type) newest-first, reusing
  `Post::excerpt()`/`published()` — same visibility rule as the blog.
- **Markdown caching**: `Project::toHtml()` duplicates the small
  cache-wrapped-CommonMark pattern from `Post::toHtml()` (see
  `app/Models/Post.php`) rather than extracting a shared trait. `Post` is
  already shipped/tested; a trait touching it risks regressions for a
  two-model reuse case that doesn't clearly earn the abstraction yet. If a
  third markdown-rendering model ever shows up, revisit extracting
  `App\Concerns\RendersCachedMarkdown`. Project's version is simpler than
  Post's anyway — no `rewriteImages()` inline-image rewriting, since the
  gallery covers the "pictures on a project" need and inline images in the
  body weren't asked for.
- **Migrations**: this app is still undeployed (established earlier this
  session), so schema changes go into the existing consolidated
  `database/migrations/2026_08_04_000000_create_site_tables.php` rather than
  a new migration file — keeps the "two migrations total" state from the
  earlier TBR/images restructuring work.

## Addendum: Ongoing/Dormant status (added mid-build)

- `App\Enums\ProjectStatus` — `Ongoing` (default) | `Dormant`.
- `projects.status` column, required at validation, `Project::scopeOngoing()`/`scopeDormant()`.
- Public `/projects` page groups client-side into two sections (same pattern
  as the dashboard books index's To Be Read/Reading/Finished grouping):
  - No ongoing projects → show the section anyway with the fallback line:
    "I don't have an active project underway right now, it's likely life &
    family is taking my focus."
  - No dormant projects → omit that section entirely (no fallback text).
- Dashboard project form gets a Status select (Ongoing/Dormant, default
  Ongoing); dashboard index shows it as a badge per row rather than
  sectioning (sectioning was asked for on the public page specifically).

## Schema

`projects` table (new):
- `id`, `title`, `slug` (unique), `blurb` (text), `body` (longText),
  `website_url` (string, nullable), `github_url` (string, nullable),
  timestamps.

`images` table (extend existing):
- add `project_id` (nullable FK → projects, `nullOnDelete`)
- add `position` (nullable unsignedTinyInteger)

`posts` table (extend existing):
- add `project_id` (nullable FK → projects, `nullOnDelete`)

## File-by-file task list

Backend:
- [x] Migration: `projects` table + `images.project_id`/`position` +
      `posts.project_id` — folded into
      `database/migrations/2026_08_04_000000_create_site_tables.php`.
- [x] `App\Models\Project` — fillable, `toHtml()` (cached), `images()`
      hasMany ordered by `position`, `posts()` hasMany, `syncImages()`.
      Also added `database/factories/ProjectFactory.php`.
- [x] `App\Models\Post` — add `project()` belongsTo.
- [x] `App\Models\Image` — add `project()` belongsTo.
- [x] `App\Concerns\ProjectValidationRules` (title/status/blurb/body/
      website_url/github_url/image_ids rules) — mirrors `BookValidationRules`
      style.
- [x] `App\Http\Requests\Projects\StoreProjectRequest` /
      `UpdateProjectRequest`.
- [x] `App\Http\Controllers\ProjectController` (dashboard CRUD, resource
      minus `show`) — mirrors `BookController`.
- [x] `App\Http\Controllers\Site\ProjectController` (public `index`+`show`).
- [x] `App\Concerns\PostValidationRules` — add `project_id` rule
      (`nullable`, `exists:projects,id`).
- [x] `App\Http\Controllers\PostController` — pass project list to
      create/edit views (`projectSummaries()`).
- [x] `routes/web.php` — dashboard resource + public
      `GET /projects/{slug}` (`projects.show`).
- [x] `App\Console\Commands\PruneOrphanedImages` — also require
      `project_id IS NULL` (was only checking `post_id IS NULL`).

Backend is now feature-complete except for running the migration locally
(next step) and wayfinder regeneration once frontend routes are referenced.

Frontend:
- [x] `resources/js/types/projects.ts` — `Project`, `ProjectSummary`,
      `PublicProjectSummary`, `PublicProjectDetail` types.
- [x] `resources/js/components/projects/ProjectFormFields.vue` — title,
      blurb, body (markdown textarea, no inline-image button), website_url,
      github_url.
- [x] `resources/js/components/projects/ProjectImagesPicker.vue` — up to 5
      image slots, reusing `ImageUploadButton.vue`, hidden
      `image_ids[]` inputs in submission order.
- [x] `resources/js/pages/projects/{Index,Create,Edit}.vue` (dashboard) —
      mirrors `resources/js/pages/books/*`.
- [x] `resources/js/pages/site/Projects.vue` — rewritten to consume real
      `projects` prop; **removed the placeholder logo `<img>` above each
      title** (requirement 7).
- [x] `resources/js/pages/site/projects/Show.vue` (new) — blurb, rendered
      body, image gallery, associated posts list, website/GitHub links,
      wired through `SeoHead`.
- [x] `resources/js/components/posts/PostFormFields.vue` — add "Project
      (optional)" select.
- [x] Wayfinder regenerated after route changes.

Tests:
- [ ] `tests/Feature/Projects/ProjectManagementTest.php` (dashboard CRUD +
      validation).
- [ ] `tests/Feature/Site/ProjectsPageTest.php` (public index + show,
      including "post shown under project" and image gallery).
- [ ] `tests/Feature/Images/PruneOrphanedImagesTest.php` — extend for
      `project_id`.
- [ ] Update `PostManagementTest.php` if `project_id` validation/assignment
      needs coverage.

Verification:
- [ ] Full test suite, Pint, PHPStan, `vue-tsc`, ESLint, Prettier.
- [ ] Browser check: dashboard create/edit a project with images, public
      project page renders gallery + linked posts, `/projects` index has no
      more placeholder logos.

## Not done / explicitly deferred

- No manual reordering of projects on the index page.
- No draft/publish state for projects.
- No inline markdown images within a project's body (only the 5-image
  gallery).
