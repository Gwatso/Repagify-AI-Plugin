# WordPress.org directory listing assets

Everything in this folder is published to the plugin's page in the WordPress.org
directory. **None of it ships in the plugin zip** — `.distignore` excludes
`assets/` from the build, which is exactly how WP.org expects this to work.

On WP.org these files live in the `/assets/` directory of the plugin's SVN
repository, as a sibling of `/trunk/` and `/tags/`. If you deploy with
[10up/action-wordpress-plugin-deploy](https://github.com/10up/action-wordpress-plugin-deploy),
this folder is copied there for you.

**Nothing here has been generated.** Every file below still needs to be
produced by a designer and committed.

## Required files

| File | Dimensions | Purpose |
| --- | --- | --- |
| `banner-1544x500.png` | 1544 × 500 | Header on the plugin page, high-DPI |
| `banner-772x250.png` | 772 × 250 | Header on the plugin page, standard |
| `icon-256x256.png` | 256 × 256 | Search results and plugin cards, high-DPI |
| `icon-128x128.png` | 128 × 128 | Search results and plugin cards, standard |
| `screenshot-1.png` | see below | |
| `screenshot-2.png` | see below | |
| `screenshot-3.png` | see below | |
| `screenshot-4.png` | see below | |

`.jpg` is accepted in place of `.png` for banners and screenshots. Icons are
better as PNG for the transparency. An `icon.svg` may be supplied instead of
the two PNG icons, but supplying both is safest.

## What each screenshot must show

The numbering has to match the `== Screenshots ==` list in `readme.txt`
exactly — WP.org pairs them by number, and a mismatch shows the wrong caption
under the wrong image.

1. **The opportunity dashboard.** The hook line ("You have N published
   posts…"), the four stat cards, and the ranked table with score badges
   visible. This is the screenshot that has to earn the install, so use a site
   with a believable archive — twenty or more posts, varied word counts, a
   spread of scores across the green/amber/grey bands. Not a site with three
   "Hello world" posts.

2. **The generate dialog, mid-choice.** Modal open over the dashboard, post
   title visible at the top, format set to something other than the default,
   tone selector open or clearly populated, and the extracted word count line
   showing. Capture it before generating, so the form is the subject.

3. **A finished generation.** The result panel with real generated content in
   the monospace block, the word count, and the Copy to clipboard button.
   Scroll so the content reads as substantial rather than as one line.

4. **The settings screen.** API key field showing the saved mask (never a real
   key — use a mask like `••••••••••••abcd`), and the connection test result
   showing plan tier and remaining generations.

## Rules worth not tripping over

- **No real API keys** in any screenshot. Check the connection-test panel and
  the settings field before exporting.
- **No real customer content** unless you have permission to publish it.
- Screenshots should be taken at a standard browser width with no OS chrome —
  crop to the WordPress admin content area.
- Banners must not contain a WordPress logo or anything implying an official
  endorsement.
- Keep text in banners minimal; they render small and are cropped on narrow
  viewports.
- Every asset must be GPL-compatible, including any font used in a banner.
  Check the licence of any typeface before it goes in.

## Current status

- [ ] `banner-1544x500.png`
- [ ] `banner-772x250.png`
- [ ] `icon-256x256.png`
- [ ] `icon-128x128.png`
- [ ] `screenshot-1.png`
- [ ] `screenshot-2.png`
- [ ] `screenshot-3.png`
- [ ] `screenshot-4.png`

The plugin can be submitted without these — WP.org will show a generic
placeholder icon and no banner. They should be in place before any real
promotion, since a listing with no icon reads as abandoned.
