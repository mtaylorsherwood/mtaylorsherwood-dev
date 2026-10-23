# Deploy-day checklist

Target: **Laravel Cloud**, with DNS and proxying through **Cloudflare** (orange
cloud enabled).

Work top to bottom. The three items marked **[BLOCKER]** were the ones that
would have broken or silently degraded the first production deploy — the code
side of each is already fixed in the repo (see
[launch-review.md](launch-review.md) B1–B3); what is left here is the part that
lives in the Laravel Cloud dashboard and can only be done there.

## 1. Laravel Cloud — compute cluster settings

Environment → infrastructure canvas → click the **App compute cluster**.

- [ ] **[BLOCKER] Toggle on "Use Inertia SSR".**
      Every meta tag on this site — title, description, canonical, OG, Twitter
      Card, JSON-LD — is emitted by `SeoHead.vue` on the client. The root Blade
      template only ever renders a static `<title>` of the app name. Without
      SSR, crawlers and link unfurlers (Slack, LinkedIn, WhatsApp, iMessage)
      receive an empty body and no metadata, and the failure is completely
      silent: Inertia catches the error and falls back to client-only
      rendering. There is no 500, no error page, nothing in the logs — except
      the listener added in `AppServiceProvider::configureSsrFailureLogging()`,
      which is exactly why it was added.
- [ ] **[BLOCKER] Set the build command to `npm run build:ssr`** (not
      `npm run build`). Laravel Cloud's docs call this out specifically for the
      starter kits. Without it, the SSR bundle is never built and the SSR
      toggle has nothing to serve.
- [ ] Enable the **scheduler** on the App (or a Worker) cluster.
      `routes/console.php` registers `images:prune` daily, and it is the only
      thing that cleans up uploads that were never attached to a post or
      project. Without the scheduler, orphaned images accumulate in R2 forever.
- [ ] Decide on **Scale to Zero**. Fine for this site — new Flex sizes wake in
      under 500 ms, and Laravel Cloud wakes the environment for scheduled tasks
      so `images:prune` still runs. Just be aware the first request after a
      sleep pays the wake cost.

## 2. Environment variables

Set in the Laravel Cloud environment (Settings → Environment variables).

| Variable | Value | Why |
| --- | --- | --- |
| `APP_ENV` | `production` | Gates the strong password policy, `DB::prohibitDestructiveCommands()`, and Inertia DevTools |
| `APP_DEBUG` | `false` | `.env.example` ships `true` |
| `APP_URL` | `https://<domain>` | Every canonical and OG URL is built from this |
| `CLOUDFLARE_IMAGES_DOMAIN` | `images.mtaylorsherwood.dev` | **[BLOCKER]** — see below |
| `IMAGES_DISK` | `r2` | Currently `public` in local dev |
| `SESSION_SECURE_COOKIE` | `true` | Unset everywhere today, so the flag depends on host defaults |
| `SESSION_ENCRYPT` | `true` | Optional; costs nothing on a single-user admin |
| `MAIL_FROM_ADDRESS` | a real address | Still `hello@example.com`; email verification depends on it |
| `MAIL_MAILER` / `MAIL_HOST` / … | real SMTP | Local config points at `127.0.0.1:2525` |
| `PASSKEYS_USER_HANDLE_SECRET` | a fixed random string | Defaults to `APP_KEY`; pinning it means a future key rotation does not invalidate every passkey |
| `APP_MAINTENANCE_DRIVER` | `cache` | Laravel Cloud's recommendation — the `file` driver does not persist across replicas or deploys |
| `APP_MAINTENANCE_STORE` | `database` | Pairs with the above |
| `AWS_*` | R2 credentials | Already understood; confirm they are set for the production bucket |

### [BLOCKER] `CLOUDFLARE_IMAGES_DOMAIN`

`CloudflareImageUrl::make()` deliberately falls back to the plain, untransformed
storage URL when this is empty, so images still render in environments where the
domain has not been wired up. The cost of that fallback is that **every** call —
`url(400)`, `url(800)`, `url(1200)` — returns the identical full-resolution
original, with no width applied and no WebP/AVIF re-encode. The entire
transformation layer, including the inline-image rewriting in
`Post::rewriteImages()`, becomes a no-op. Nothing looks broken; the images are
just several times larger than intended.

It is blank in `.env` and (deliberately) in `.env.example`, so it is easy to
carry that state into production without noticing.

Two things are required, not one:

1. Set `CLOUDFLARE_IMAGES_DOMAIN=images.mtaylorsherwood.dev` in the Laravel
   Cloud environment.
2. In the Cloudflare dashboard for that zone, enable **Image Transformations**
   with "Resize images from any origin" / transform-via-URL. The
   `/cdn-cgi/image/...` path only works on a proxied hostname in a zone where
   the feature is switched on. The R2 custom domain is proxied, so this is a
   toggle, not a migration.

Leave it blank locally — local dev uses `IMAGES_DISK=public`, and pointing at
the Cloudflare domain would break every image in the dev environment.

## 3. Verify after deploying

Three curls confirm the two silent blockers actually landed.

```sh
# SSR is alive — should print a number greater than 0
curl -s https://<domain>/blog | grep -c 'og:title'

# Image transformations are on — should show a /cdn-cgi/image/ path
curl -s https://<domain>/blog | grep -o 'https://[^"]*cdn-cgi/image/[^"]*' | head -1

# 404s are real 404s, not 200s with an error page
curl -s -o /dev/null -w '%{http_code}\n' https://<domain>/blog/no-such-post
```

Then, by eye:

- [ ] Paste a blog post URL into Slack or LinkedIn and confirm the card renders
      with a title, description and image. (The fallback `og:image` is
      [X1](launch-review.md) and is not built yet — until it is, only posts with
      a featured image will show artwork.)
- [ ] Log in and confirm 2FA / passkeys still work against the production
      domain. `config/fortify.php` derives the passkey relying-party ID from
      `APP_URL`, so passkeys registered against a different host will not work.
- [ ] Confirm `/dashboard` and `/settings` redirect to login when logged out.

## 4. Things that are already handled — do not re-do them

- **Trusted proxies.** Laravel's `TrustProxies` middleware auto-trusts when
  `LARAVEL_CLOUD=1` is present, which Laravel Cloud injects
  (`Illuminate\Http\Middleware\TrustProxies::setTrustedProxyIpAddresses()`).
  HTTPS detection and URL generation are fine without any
  `->trustProxies()` call in `bootstrap/app.php`. There is one residual quirk
  from the extra Cloudflare hop — see [launch-review.md](launch-review.md) S2 —
  but it is minor and does not need fixing before launch.
- **Static asset caching.** Vite output is content-hashed and Cloudflare's
  default cache rules pick it up by extension. Uploaded images are stored with
  `Cache-Control: public, max-age=31536000, immutable` at upload time.
- **`php artisan optimize`, `migrate`, `storage:link`.** Laravel Cloud runs the
  configured deploy commands for you; the default deploy command set already
  covers migrations and caching. Confirm `php artisan migrate --force` is in the
  deploy commands, and note that **B1 must be resolved first** or the very first
  migration run fails on a duplicate column.
