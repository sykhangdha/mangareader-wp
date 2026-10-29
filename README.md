<div align="center">

# 📚 Manga Reader Theme for WordPress

A manga reading WordPress theme with a MangaDex-style library, a fullscreen reader, bookmarks, news updates and three looks: **FluentUI**, a Windows Phone style **MetroUI**, a classic **MangaStreamUI**, and soon to be added **MangaDexUI** once V1.0 releases

[**Live Demo**](https://skymanga.42web.io/) · [**Download (Beta)**](https://github.com/sykhangdha/mangareader-wp/releases) · [**Changelog**](#changelog) · [**Screenshots**](#screenshots)

</div>

> [!IMPORTANT]
> **Required setup:** you must create a page called **Manga List** (slug `manga-list`) and set its template to **Manga Archive**. Without it, the "Browse the full library" button and other library links can show a **404 error**.
> **[Jump to the step-by-step guide →](#manga-list-page)**

---

## 📑 Contents

| Getting started | Using the theme | Reference |
|---|---|---|
| [Installation](#installation) | [Using the features](#using-the-features) | [Changelog](#changelog) |
| [Create the Manga List page](#manga-list-page) | [Import from Folder](#import-from-folder) | [Updating](#updating) |
| [Features](#features) | | [Screenshots](#screenshots) |

---

<a id="installation"></a>
## 🚀 Installation

**Option 1: WordPress admin**
1. Download the theme zip file.
2. Go to **Appearance → Themes → Add New → Upload Theme**.
3. Choose the zip file, click **Install Now**, then **Activate**.

**Option 2: FTP**
1. Unzip the theme folder.
2. Upload it to `/wp-content/themes/`.
3. Go to **Appearance → Themes** and activate it.

**After activating**
1. **[Create the Manga List page](#manga-list-page)** (required).
2. Open any wp-admin page once. This prepares the data used for sorting by "Recently updated" and "Most chapters".
3. *(Optional)* Set up your [menus, footer, slider and music](#customizer-map).

**Requirements:** WordPress 5.5+ (6.2+ recommended for title-only search), PHP 7.4+, MySQL 5.6+, and a modern browser with JavaScript enabled.

---

<a id="manga-list-page"></a>
## ⚠️ Required: create the Manga List page

> [!IMPORTANT]
> **You MUST create a page called "Manga List" with the slug `manga-list` and set its page template to "Manga Archive".**
> The theme looks for a published page with the slug `manga-list`. If it doesn't exist, library links fall back to `/manga/`, which can give a **404 error**.

1. In your dashboard, go to **Pages → Add New**.
2. **Title:** type `Manga List`.
3. **Slug:** open the **Settings** panel (gear icon, top right), click the **URL** field under **Page**, and make sure it is exactly `manga-list`.
4. **Template:** in the same panel, choose **Manga Archive** under **Template**.
   *(Classic Editor: use **Page Attributes → Template**.)*
5. Leave the page content empty. The theme fills it in.
6. Click **Publish**.
7. Go to **Settings → Permalinks** and click **Save Changes**. You don't need to change anything. This prevents 404 errors.
8. *(Optional)* Add it to your menu: **Appearance → Menus**, tick **Manga List**, click **Add to Menu**, then **Save Menu**.

**Check that it works**
- Open `yoursite.com/manga-list/`. You should see the library with search, sort and filters.
- On the homepage, click **Browse the full library**. It should open the same page.
- `yoursite.com/manga/` redirects to the Manga List page.

<details>
<summary><b>Troubleshooting</b></summary>

| Problem | Fix |
|---|---|
| "Browse the full library" shows a 404 | The page is missing, not published, or the slug isn't exactly `manga-list`. Check steps 1-6, then re-save **Settings → Permalinks**. |
| The page loads but has no filters | Set the page template to **Manga Archive** (step 4). |
| Filters don't work | Clear your caching plugin and refresh. |
| "Recently updated" or "Most chapters" shows nothing | Open any wp-admin page once. |

</details>

---

<a id="features"></a>
## ✨ Features

### 🎨 Themes
- **Three themes**, switchable from the header and remembered between visits:
  - **FluentUI:** clean Windows 11 style with blurred backgrounds. It has **Light, Dark and Sepia modes**.
  - **MetroUI:** flat Windows Phone look with square tiles, large lowercase headings and 12 accent colours.
  - **MangaStreamUI:** the classic blue-header MangaStream look with featured tiles, full news posts and a "New & Noteworthy" list.
- **The reader matches your theme:** each UI gets its own reader style and a background that suits it.
- **Dark mode on first visit** if your device prefers it.
- **Responsive:** desktop, tablet and phone layouts.

### 🏠 Homepage
- **Slider:** choose what it shows: **New titles, Most viewed, Popular this week or your own Recommendations**. You can change its heading and the number of slides.
- **News updates:** post announcements from the dashboard. They show as a short headline list in the sidebar, or as full posts in MangaStreamUI.
- **Continue reading and bookmarks rows** for each visitor.
- **Latest updates** grid with Recently updated and A-Z sorting.

### 📚 Manga library (MangaDex-style)
- **Genres:** click once to include, twice to exclude, with match-all or match-any.
- **Status**, **Starts with (A-Z and #)** and **live title search**.
- **Sorting:** A → Z, Z → A, Recently updated, Newly added, Most chapters.
- **No page reloads**, and **shareable links** (e.g. `?g=action&x=horror&st=ongoing`).
- **Random** button that opens a random series.

### 📖 Manga pages
- **Bookmark**, **Continue reading** and **read marks (✓)** for each visitor.
- **Chapter list** with newest/oldest sorting, optional chapter titles and a "Latest" badge.
- **"You might also like"** related series.
- **NEW tag** on cards updated in the last 3 days.

### 📱 Fullscreen reader
- **Two reading modes:** Paged and Long Strip.
- **Reading direction:** left-to-right or **right-to-left (manga)**.
- **Image fit:** Fit width, Fit height or Original, plus zoom (50% - 250%).
- **Backgrounds:** Dark, Light and Sepia, plus a **brightness slider** for night reading.
- **Calm layout:** nothing floats over the pages. The page number and music button sit in a slim strip, and the arrows fade when idle. There is an optional **auto-hide toolbar**.
- **Smooth chapter changes:** the next chapter loads in the background, and moving between chapters doesn't reload the page.
- **Mini music player:** a lofi player with a playlist you control.
- **Remembers your place** and your settings.

### 🛠️ Admin tools
- **Manga and Chapter post types** with genres, status, author, artist and year.
- **Chapter editor:** Media Library picker with live preview and natural sorting. Titles are generated automatically (e.g. "Series - Vol. 2 Ch. 5"), with an optional chapter title.
- **Recommendations box** on each manga.
- **Views column** in the manga list.
- **News Updates** menu.
- **[Import from Folder](#import-from-folder)** for adding many chapters at once.
- **Customizer options** and **footer menus**.

---

<a id="using-the-features"></a>
## 🧭 Using the features

<a id="customizer-map"></a>
### Where to find the settings

| What | Where |
|---|---|
| Homepage slider | **Appearance → Customize → Homepage Slider** |
| News and support box | **Appearance → Customize → MangaStreamUI** |
| Music player playlist | **Appearance → Customize → Reader Music Player** |
| Footer About text | **Appearance → Customize → Footer** |
| Header and footer links | **Appearance → Menus** |
| News posts | **Dashboard → News Updates** |
| Recommendations | **Edit a manga → Recommendation box** (right-hand side) |
| Folder import | **Manga → Import from Folder** |

### 🎨 Switching themes

1. Click the **theme button** in the header (next to the search icon).
2. Pick **FluentUI, MetroUI or MangaStreamUI**.
3. With **FluentUI**, choose **Light, Dark or Sepia** under *Mode*.
4. With **MetroUI**, choose an **accent colour**.

Your choice is saved in your browser. People who used the old Light, Dark or Sepia themes are moved to FluentUI in the same mode automatically.

### 🖼️ Homepage slider and recommendations

1. Go to **Appearance → Customize → Homepage Slider**.
2. **Show:** pick *New titles*, *Most viewed*, *Popular (this week)* or *Recommendations*.
3. **Heading:** type your own, or leave it empty to use the default name.
4. **Number of slides:** 1 to 10.
5. Click **Publish**.

**To use Recommendations**
1. Edit a manga and find the **Recommendation** box on the right.
2. Tick **Recommend this manga**.
3. *(Optional)* write a short reason. It replaces the description on that slide.
4. *(Optional)* set an order number. The lowest number shows first.
5. Set the slider to *Recommendations* as above.

> **Good to know:** views start counting from the day you install this version. Until there are enough views, the "Most viewed" and "Popular" lists are topped up with your newest titles. If you haven't recommended anything yet, the slider shows your newest titles.

### 📰 News updates

1. Go to **News Updates → Add New** in the dashboard.
2. Write a title and your post, then **Publish**.
3. For long posts, add a **Read more** break so only the first part shows on the homepage.

| Theme | How news shows |
|---|---|
| FluentUI and MetroUI | A short **"Latest news"** headline list at the top of the sidebar, with an "All news" link |
| MangaStreamUI | **Full news posts** in the main column |

Set how many appear under **Appearance → Customize → MangaStreamUI**. The page for all posts is at `yoursite.com/news/`. If you don't see a news section, add your first post. You can also set up the optional **"Love [site]? Support us"** box in the same Customizer section.

### 🔖 Bookmarks and continue reading

- **Bookmark:** open a manga page and click **Bookmark**. It then reads **Bookmarked**. Click it again to remove it.
- **Your bookmarks** appear in a row at the top of the homepage.
- **Continue reading:** the manga page shows a **Continue** button, and the homepage shows a **Continue reading** row. A half-read chapter reopens on the page you left.
- **Read marks:** chapters you've read through about 70% get a ✓ in the chapter list.

> **Note:** this data is saved in your own browser only. It doesn't follow you to another device or a private window, and clearing your browser data erases it.

### 📖 Reader settings and shortcuts

Open the settings with the **gear icon** or press **S**.

| Setting | Options |
|---|---|
| Reading mode | Paged · Long Strip |
| Image fit | Fit width · Fit height · Original |
| Zoom | − / + / reset (50% - 250%) |
| Background | Dark · Light · Sepia |
| Brightness | Slider (dim the pages) |
| Reading direction | Left to right · Right to left (manga) |
| Toolbar | Always show · Auto-hide |
| Navigation | Click to advance · Buttons only |

**Shortcuts**

| Key | Action |
|---|---|
| ← → | Previous / next page (swapped in right-to-left mode) |
| ↑ ↓ | Scroll in Long Strip mode |
| F | Fullscreen |
| S | Open or close settings |
| Esc | Close panels |
| Tab | Shows the toolbar when Auto-hide is on |

- **Tap zones (paged mode):** the left third goes back and the rest goes forward. In right-to-left mode the right third goes back.
- **Phones:** swipe to turn pages, and tap the top edge to show the toolbar in Auto-hide. A **home icon** replaces the back arrow.
- **End of a chapter:** in Paged mode, going past the last page opens the next chapter. In Long Strip mode a card at the bottom offers "Next chapter".

### 🎵 Music player

The music button sits at the left of the strip under the pages. Click it to open the player, then play, skip and pick tracks. Music keeps playing when you move between chapters.

**Edit the playlist:** **Appearance → Customize → Reader Music Player**. Add one track per line as `Title | Link`:

```
Lofi Girl - lofi hip hop radio | https://www.youtube.com/watch?v=jfKfPfyJRdk
Late Night Lofi | https://yoursite.com/wp-content/uploads/late-night.mp3
```

- Links can be **YouTube videos or live streams**, or **direct audio files** (mp3, ogg, wav) from your Media Library.
- YouTube tracks show a small video in the panel. For listening in the background, use audio files.
- The default playlist uses two YouTube streams that may stop working. If one doesn't play, replace the link.
- Only add music you are allowed to use.

### 🦶 Footer and menus

- **Footer links:** go to **Appearance → Menus**, create a menu, and tick **Footer Menu: Quick Links** or **Footer Menu: Browse** under *Display location*.
- **About text:** go to **Appearance → Customize → Footer**. Change the heading and text, or hide it. If you leave the text empty, your site tagline is shown.

### ✏️ Chapter titles

When you edit a chapter, you can add an **optional chapter title** (for example "Who Is The Reaper's Enemy?"). It shows in MangaStreamUI's lists and tiles and on the manga page.

---

<a id="import-from-folder"></a>
## 📁 How to use Import from Folder

Use this to add many chapters at once. You upload your image folders to the server, then import them from the dashboard. You need an **Administrator** account.

### Step 1: Prepare your folders

Name the folders like this:

```
Series Name/
├── Chapter 1/
│   ├── 01.jpg
│   ├── 02.jpg
│   └── 03.jpg
├── Chapter 2/
│   └── 01.jpg
└── Vol. 2 Ch. 5/
    └── 01.jpg
```

- The **top folder name becomes the manga title**, so name it exactly how you want it shown.
- The **chapter folder name sets the chapter number**. These all work: `Chapter 5`, `Ch. 5`, `Vol. 2 Ch. 5`, or just `5`. Decimals work too, like `Chapter 10.5`.
- Name pages with numbers (`01.jpg`, `02.jpg`, …) so they stay in order.
- Supported formats: JPG, JPEG, PNG, GIF and WebP.

### Step 2: Upload to your server

1. Connect with FTP or your host's File Manager.
2. Go to `wp-content/manga-import/`. If the folder doesn't exist yet, open the import page once (Step 3) and the theme creates it.
3. Upload your series folder so you end up with:
   `wp-content/manga-import/Series Name/Chapter 1/01.jpg`

### Step 3: Import in WordPress

1. In your dashboard, go to **Manga → Import from Folder**.
2. Click your series in the **Series folders** list on the left. It shows how many new chapters are waiting.
3. Choose what to import:
   - **One chapter:** click **Import** on that row.
   - **Several chapters:** tick the boxes (or the box at the top to select all), then click **Import selected chapters**.
4. Wait for the green confirmation message. Large chapters can take a while, so don't close the page.

The manga entry is created automatically if it doesn't exist yet. To create only the entry, click **Create manga entry only**.

### Step 4: Finish the manga entry

Importing adds the chapters, but not the extras. Go to **Manga → All Manga**, open the series and add:
- **Featured image** (the cover)
- **Genres** and **Status**
- **Author, artist and year**, plus a description

### Good to know

- **Your original files are never moved or deleted.** Images are copied into your Media Library.
- **Already imported chapters are hidden** so you can't import them twice.
- **Need a chapter to show up again?** Click **Clear import history** at the top of the page.
- **Import stops or times out?** Import fewer chapters at a time. On free shared hosting, start with 1-3, because importing can use a lot of CPU.
- **Page order looks wrong?** Rename the images with leading numbers (`01`, `02`, …). You can also fix the order later in the chapter's **Chapter Images** box using **Sort naturally**.

<details>
<summary><b>Troubleshooting</b></summary>

| Problem | Fix |
|---|---|
| "No folders found yet" | Check the path is exactly `wp-content/manga-import/Series Name/Chapter Name/`. |
| A chapter shows 0 images | Images must be directly inside the chapter folder (not in a sub-folder) and be JPG, PNG, GIF or WebP. |
| "Already imported" | Click **Clear import history**, or delete the existing chapter first. |
| Import page missing from the menu | Log in as an Administrator. |

</details>

---

<a id="changelog"></a>
## 📜 Changelog

**Key:** ✨ New · 🔧 Improved · 🐛 Fixed · 🔄 Changed

| Update | Highlights |
|---|---|
| [**Beta Update 4**](#beta-update-4) | FluentUI, bookmarks, continue reading, right-to-left reader, calmer reader layout |
| [**Beta Update 3**](#beta-update-3) | News on every theme, slider options, recommendations, music player, footer menus |
| [**Beta Update 2**](#beta-update-2) | MangaStreamUI theme, News Updates, Manga List page |
| [**Beta Update 1**](#beta-update-1) | MangaDex-style library filters, MetroUI theme, many reader fixes |
| [**Alpha Update 2**](#alpha-update-2) | Chapter order and reader fixes |

<a id="beta-update-4"></a>
<details open>
<summary><b>Beta Update 4</b> · latest</summary>

<br>

| Type | Change |
|---|---|
| ✨ New | **FluentUI.** Light, Dark and Sepia are now one theme with three modes. Saved choices carry over automatically. |
| ✨ New | **Reader themes.** The reader matches the UI you picked and starts with a background that suits it. |
| ✨ New | **Bookmarks.** A Bookmark button on every manga page and a "Your bookmarks" row on the homepage. |
| ✨ New | **Continue reading.** A Continue button on the manga page, a "Continue reading" row on the homepage, and half-read chapters reopen on your page. |
| ✨ New | **Read marks.** Chapters you've read are dimmed with a ✓ in the chapter list. |
| ✨ New | **Right-to-left reading** option, which flips taps, arrow keys and swipes. |
| ✨ New | **Brightness slider** in the reader settings. |
| ✨ New | **Auto-hide toolbar** option in the reader settings. |
| ✨ New | **End-of-chapter card** in Long Strip mode with a "Next chapter" button. |
| ✨ New | The **next chapter loads in the background** while you read. |
| ✨ New | **"You might also like"** related manga on each manga page. |
| ✨ New | **Random** button in the library and footer. |
| ✨ New | **NEW tag** on cards updated in the last 3 days. |
| ✨ New | **Editable footer About section** (Appearance → Customize → Footer). |
| ✨ New | **Home icon** on the phone reader, replacing the back arrow. |
| 🔧 Improved | The music player keeps playing between chapters, and audio files continue where they stopped. |
| 🔧 Improved | **Calmer reader:** the page number and music button sit in a slim strip, zoom moved into settings, and the arrows fade when idle. |
| 🔧 Improved | MangaStreamUI top tiles show the whole cover with the title, chapter and date, and become a swipe row on phones. |
| 🔧 Improved | Slider arrow buttons are now icons that match each theme. |
| 🐛 Fixed | MangaStreamUI chapter badges showing just a number. They now read "Ch. 7". |
| 🐛 Fixed | Bookmarks not showing on the homepage. |
| 🐛 Fixed | Old scripts and styles being served after an update. |
| 🐛 Fixed | Music not resuming between pages. |

</details>

<a id="beta-update-3"></a>
<details>
<summary><b>Beta Update 3</b></summary>

<br>

| Type | Change |
|---|---|
| ✨ New | **News on every theme.** Light, Dark, Sepia and MetroUI show a "Latest news" headline list in the sidebar. MangaStreamUI shows full posts in the main column. |
| ✨ New | **Homepage slider options:** New titles, Most viewed, Popular this week or Recommendations, with your own heading and number of slides. |
| ✨ New | **Recommendations.** Hand-pick manga for the slider, with an optional note and order number. |
| ✨ New | **View counter.** Counts visits to manga and chapter pages (bots and logged-in editors are ignored) and adds a Views column to the manga list. |
| ✨ New | **Mini music player** on the reader page with a lofi playlist (YouTube links, live streams or audio files). |
| ✨ New | **Footer menus.** The Quick Links and Browse columns now use WordPress menus. |
| 🔧 Improved | MangaStreamUI has a cleaner layout, with a tidier sidebar, Bootstrap-style buttons, inputs and pagination, and a striped chapter list. |
| 🔧 Improved | The sidebar no longer sticks while scrolling, so nothing gets cut off on shorter screens. |
| 🐛 Fixed | Bullet points and numbered lists in news posts, pages and descriptions. |
| 🐛 Fixed | "Browse the full library" 404. Library links now point to your Manga List page. |
| 🐛 Fixed | The site title looking different on the homepage than on other pages. |
| 🐛 Fixed | Old CSS being served after an update. |

</details>

<a id="beta-update-2"></a>
<details>
<summary><b>Beta Update 2</b></summary>

<br>

| Type | Change |
|---|---|
| ✨ New | **MangaStreamUI theme,** based on the classic MangaStream site (blue header band, white logo text, grey cards and an overlapping nav bar). |
| ✨ New | **News Updates** menu for posting news to the front page, with "Read more" support and an "Older news" archive at `/news/`. |
| ✨ New | **"New & Noteworthy"** sidebar list of the 12 newest chapters, highlighted when posted in the last 3 days. |
| ✨ New | **Three featured tiles** for the most recently updated manga. |
| ✨ New | **Optional chapter title** field in the chapter editor. |
| ✨ New | **Customizer options for MangaStreamUI:** number of news posts and an optional support box. |
| ✨ New | The library now lives on a page called **Manga List** (slug `manga-list`), and `/manga/` redirects to it. |
| 🐛 Fixed | "Browse the full library" and other library links showing a 404. |
| 🔄 Changed | The theme refreshes its page addresses once after updating so the new News pages work. |

</details>

<a id="beta-update-1"></a>
<details>
<summary><b>Beta Update 1</b></summary>

<br>

| Type | Change |
|---|---|
| ✨ New | **MangaDex-style library filters:** include/exclude genres, status, A-Z letters, title search and sorting. |
| ✨ New | Sort by A → Z, Z → A, Recently updated, Newly added or Most chapters. |
| ✨ New | Filters and pages update without reloading, and filtered links can be shared. |
| ✨ New | **Theme switcher** with Light, Dark, Sepia and the new **MetroUI** theme (12 accent colours). |
| ✨ New | Genre and status links now open the filtered library. |
| ✨ New | Reader: tap zones, swipe navigation, and an automatic jump to the next chapter. |
| ✨ New | Chapter editor: Media Library picker with page preview. |
| ✨ New | The admin chapter list shows manga, chapter and page count. |
| 🐛 Fixed | Chapter order when volumes and loose chapters are mixed, and decimal chapters (e.g. 10.5). |
| 🐛 Fixed | "Chapter 1" style titles showing as "Ch. ?". |
| 🐛 Fixed | Wrong "Latest" chapter after uploading older chapters. |
| 🐛 Fixed | Manga cards looking different after filtering. |
| 🐛 Fixed | Mobile menu not opening, and submenus closing the whole menu. |
| 🐛 Fixed | Reader: page counter resetting, double-click skipping pages, zoom clipping the page, blocked shortcuts (Ctrl+F, Ctrl+S) and a broken mobile layout. |
| 🐛 Fixed | Importer deleting your original files, and pages importing out of order. |
| 🔧 Improved | Security, speed and accessibility across the theme. |
| 🔄 Changed | Import folder is now `wp-content/manga-import/`. |
| 🔄 Changed | Reader image fit options are now Fit width, Fit height and Original. |
| 🔄 Changed | Genre and status pages redirect to the filtered library. |

</details>

<a id="alpha-update-2"></a>
<details>
<summary><b>Alpha Update 2</b></summary>

<br>

| Type | Change |
|---|---|
| 🐛 Fixed | Next/prev chapter being reversed. |
| 🐛 Fixed | The manga list page. |
| 🐛 Fixed | Some functions not working properly on the reader page. |

</details>

---

<a id="updating"></a>
## 🛠️ Updating

### Updating a Beta install

1. Back up your site.
2. Go to **Appearance → Themes → Add New → Upload Theme**, choose the new zip and click **Replace current with uploaded**.
3. **Empty your caches** (caching plugin and host cache), then hard refresh with **Ctrl+F5**. This makes sure your site loads the new scripts and styles.
4. Visitors' saved choices carry over: old Light, Dark or Sepia choices become FluentUI in the same mode.

### Updating from Alpha

- Move your old `ABSPATH/manga/` import folder to `wp-content/manga-import/`.
- Create the **[Manga List page](#manga-list-page)**.
- Genre and status pages (`/genre/...`, `/status/...`) now redirect to the filtered library. To keep the old pages, add this to your `functions.php`:

```php
add_filter( 'manga_redirect_taxonomies', '__return_false' );
```

> [!WARNING]
> **It is highly recommended to start from a fresh WordPress installation if you are coming from Alpha (or are currently using Alpha).**
> Alpha stored chapter data, imported-chapter records and permalinks differently, so upgrading in place can cause wrong chapter numbers, missing chapters in the import list or 404 errors. Back up your site first, then do a clean install of WordPress and the Beta theme.

---

<a id="screenshots"></a>
## 🖼️ Screenshots

> These screenshots show an earlier look of the theme.

**Homepage**
<img width="1193" height="905" alt="Homepage" src="https://github.com/user-attachments/assets/5605547c-bc57-4801-9f20-1fbe833012ec" />

**Manga list**
<img width="1233" height="869" alt="Manga list" src="https://github.com/user-attachments/assets/1d3aaa9e-d6e9-4cb9-bd8a-f2ab73afa084" />

**Reader page**
<img width="1906" height="1079" alt="Reader page" src="https://github.com/user-attachments/assets/061bb9e3-6570-46b7-8572-e9356c55b214" />

**Chapter list**
<img width="980" height="916" alt="Chapter list" src="https://github.com/user-attachments/assets/752199ea-aa9d-4280-a2f8-6b11d38cf8d2" />
