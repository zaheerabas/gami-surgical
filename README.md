# Gami Surgical WordPress theme

## One-click updates from GitHub Releases

Every site running this theme checks the latest release of
`zaheerabas/gami-surgical` (every 12 hours) and shows the normal
**Update now** button in Appearance → Themes when the release is newer
than the installed version. The repo must be **public**.

### Shipping a new version

1. Change the theme files.
2. Raise `Version:` in `style.css` (e.g. `1.14.0` → `1.15.0`).
3. Commit and push to `main`. The *Release theme* action sees the new
   version, builds `gami-surgical.zip` and publishes release `v1.15.0`.
   (Pushes that don't change the version publish nothing.)

   Or by hand: GitHub → Releases → *Draft a new release* → tag `v1.15.0`,
   attach your zip (name it `gami-surgical.zip`, with a `gami-surgical/`
   folder inside containing `style.css`), write the changelog, *Publish*.

The release number must be higher than the installed `Version:`, and must match
the `Version:` inside the zip, otherwise WordPress keeps offering the update.

To see an update right away: Dashboard → Updates → **Check again**.

The repo is set by `GAMI_GITHUB_REPO` in `inc/github-updater.php`.

## Blog and articles

- When the theme is installed or updated it sets up the **Blog** page
  (reuses an existing page with the slug `blog`, otherwise creates one).
- Blog page sections and article extras can be switched on and off in
  **Appearance → Customize → Gami Surgical → Blog and articles**.
- Articles live in `articles/` as `.html` files. They are not part of the
  theme zip; each release also carries `gami-surgical-articles.zip`.
- In WordPress: **Posts → Upload Articles**, choose the `.html` files,
  pick *Draft* or *Publish*. Uploading the same file again updates the post.
- After upload, edit the post normally or click **Edit with Elementor**,
  and add a featured image.

### Writing a new article file

```html
<!DOCTYPE html>
<html>
<head>
<title>Article title</title>
<meta name="description" content="One or two sentences; used as the excerpt.">
<meta name="slug" content="article-url-slug">
<meta name="category" content="Instrument Guides">
<meta name="tags" content="tag one, tag two">
</head>
<body>
<article>
<h1>Article title</h1>
<p>Text…</p>
<div class="gs-callout"><p class="gs-callout__title">In short</p><p>Highlighted box.</p></div>
<div class="gs-checklist"><p class="gs-checklist__title">Checklist</p><ul><li>Ticked item</li></ul></div>
</article>
</body>
</html>
```
