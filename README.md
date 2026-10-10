# Gami Surgical WordPress theme

## One-click updates from GitHub Releases

Every site running this theme checks the latest release of
`zaheerabas/gami-surgical` (every 12 hours) and shows the normal
**Update now** button in Appearance → Themes when the release is newer
than the installed version. The repo must be **public**.

### Shipping a new version

1. Change the theme files.
2. Raise `Version:` in `style.css` (e.g. `1.14.0` → `1.15.0`).
3. Publish a release, either way:
   - **Automatic:** commit, then push a matching tag:
     `git tag v1.15.0 && git push origin main v1.15.0`.
     The *Release theme* action builds `gami-surgical.zip` and publishes the release.
   - **By hand:** GitHub → Releases → *Draft a new release* → tag `v1.15.0`,
     attach your zip (name it `gami-surgical.zip`, with a `gami-surgical/`
     folder inside containing `style.css`), write the changelog, *Publish*.

The tag number must be higher than the installed `Version:`, and must match
the `Version:` inside the zip, otherwise WordPress keeps offering the update.

To see an update right away: Dashboard → Updates → **Check again**.

The repo is set by `GAMI_GITHUB_REPO` in `inc/github-updater.php`.
