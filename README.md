<div align="center">

# 📚 Manga Reader Theme for WordPress

A simple manga reading wordpress theme with a manga library, a fullscreen reader and four themes, including a Windows Phone style **MetroUI** theme.

[**Live Demo**](https://skymanga.42web.io/) · [**Download (Beta)**](https://github.com/sykhangdha/mangareader-wp/releases) · [**Screenshots**](#screenshots)

</div>

> [!IMPORTANT]
> **Required setup:** you must create a page called **Manga List** (slug `manga-list`) and set its template to **Manga Archive**. Without it, the "Browse the full library" button and other library links can show a **404 error**.
> **[Jump to the step-by-step guide →](#manga-list-page)**

---

## Contents

- [Installation](#installation)
- [Required: create the Manga List page](#manga-list-page)
- [Features](#features)
- [Switching themes](#switching-themes)
- [How to use Import from Folder](#import-from-folder)
- [Changelog](#changelog)
- [Updating from Alpha](#updating-from-alpha)
- [Screenshots](#screenshots)

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

**Requirements:** WordPress 5.5+ (6.2+ recommended for title-only search), PHP 7.4+, MySQL 5.6+, and a modern browser with JavaScript enabled.

---

<a id="manga-list-page"></a>
## ⚠️ Required: create the Manga List page

> [!IMPORTANT]
> **You MUST create a page called "Manga" with the slug `manga` and set its page template to "Manga Archive".**
> The theme looks for a published page with the slug `manga-list`. If it doesn't exist, library links fall back to `/manga/`, which can give a **404 error**.

1. In your dashboard, go to **Pages → Add New**.
2. **Title:** type `Manga`.
3. **Slug:** open the **Settings** panel (gear icon, top right), click the **URL** field under **Page**, and make sure it is exactly `manga-list`.
4. **Template:** in the same panel, choose **Manga Archive** under **Template**.
   *(Classic Editor: use **Page Attributes → Template**.)*
5. Leave the page content empty. The theme fills it in.
6. Click **Publish**.
7. Go to **Settings → Permalinks** and click **Save Changes**. You don't need to change anything. This prevents 404 errors.
8. *(Optional)* Add it to your menu: **Appearance → Menus**, tick **Manga**, click **Add to Menu**, then **Save Menu**.

**Check that it works**
- Open `yoursite.com/manga/`. You should see the library with search, sort and filters.
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

### 🎨 Themes and design
- **Four themes:** Light, Dark, Sepia and **MetroUI**, switchable from the header and remembered between visits
- **MetroUI:** flat Windows Phone look with square tiles, large lowercase headings and 12 accent colours
- **Fluent design:** clean Windows 11 style with smooth animations and blurred, translucent backgrounds
- **Dark mode on first visit** if your device prefers it
- **Responsive:** works on desktop, tablet and mobile
- **Sticky header** so the menu is always within reach

### 📚 Manga library (MangaDex-style)
- **Genres:** click once to include, twice to exclude
- **Match mode:** match all included genres, or any of them
- **Status:** Ongoing, Completed, Hiatus, Cancelled (pick several)
- **Starts with:** A-Z and #
- **Live title search** as you type
- **Sorting:** A → Z, Z → A, Recently updated, Newly added, Most chapters
- **No page reloads**, and **shareable links** (e.g. `?g=action&x=horror&st=ongoing`)

### 📖 Manga management
- **Manga and Chapter post types**
- **Genres and Status** to organise your library
- **Details:** author, artist, year and cover image
- **Chapters link to their manga**, and titles are generated automatically (e.g. "Series - Vol. 2 Ch. 5")
- **Media Library picker** for chapter pages, with a live preview and natural sorting

### 📱 Fullscreen reader
- **Two reading modes:** Paged and Long Strip
- **Image fit:** Fit width, Fit height or Original, plus zoom (50% - 250%)
- **Backgrounds:** Dark (default), Light, Sepia
- **Navigation:**
  - Click (left third goes back, the rest goes forward)
  - Swipe on touch devices
  - Buttons only
  - Keyboard: ← → arrows, **F** for fullscreen, **S** for settings
  - The end of a chapter opens the next one
- **Chapter selector, progress bar and page indicator**
- **Settings are remembered** in your browser

### 📁 Folder import
- Import many chapters at once from `wp-content/manga-import/`
- Creates manga entries from folder names
- Skips chapters you've already imported
- **Your original files are copied, never moved or deleted**
- Pages import in natural order (`2.jpg` before `10.jpg`)
- **[How to use it →](#import-from-folder)**

> **Note:** on free shared hosting, importing can use a lot of CPU and may slow your site or hit rate limits.

---

<a id="switching-themes"></a>
## 🎨 Switching themes

Click the **theme button** in the header (next to the search icon) and choose **Light, Dark, Sepia or MetroUI**. With MetroUI selected, an **Accent colour** picker appears. Your choice is saved in your browser.

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
- **Import stops or times out?** Import fewer chapters at a time. On free shared hosting, start with 1-3.
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

#### Beta Update 1

**Added**
- Library filters like MangaDex: include/exclude genres, status, A-Z letters, title search and sorting
- Sort by A → Z, Z → A, Recently updated, Newly added or Most chapters
- Filters and pages update without reloading, and filtered links can be shared
- Theme switcher: **Light, Dark, Sepia and the new MetroUI theme** (12 accent colours)
- Genre and status links now open the filtered library
- Reader: tap zones, swipe navigation, and automatic jump to the next chapter
- Chapter editor: Media Library picker with page preview
- Admin chapter list now shows manga, chapter and page count

**Fixed**
- Chapter order when volumes and loose chapters are mixed, and decimal chapters (e.g. 10.5)
- "Chapter 1" style titles showing as "Ch. ?"
- Wrong "Latest" chapter after uploading older chapters
- Manga cards now look the same everywhere, including after filtering
- Mobile menu not opening, and submenus closing the whole menu
- Reader: page counter resetting, double-click skipping pages, zoom clipping the page, blocked shortcuts (Ctrl+F, Ctrl+S) and a broken mobile layout
- Importer deleting your original files, and pages importing out of order
- Security, speed and accessibility improvements

**Changed**
- Import folder is now `wp-content/manga-import/`
- Reader image fit options are now Fit width, Fit height and Original
- Genre and status pages redirect to the filtered library

#### Alpha Update 2
- Fixed next/prev chapter being reversed
- Updated code to fix manga list page
- Added code to fix some functions not working properly on the reader page

---

<a id="updating-from-alpha"></a>
## 🛠️ Updating from Alpha

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

**Homepage**
<img width="1193" height="905" alt="Homepage" src="https://github.com/user-attachments/assets/5605547c-bc57-4801-9f20-1fbe833012ec" />

**Manga list**
<img width="1233" height="869" alt="Manga list" src="https://github.com/user-attachments/assets/1d3aaa9e-d6e9-4cb9-bd8a-f2ab73afa084" />

**Reader page**
<img width="1906" height="1079" alt="Reader page" src="https://github.com/user-attachments/assets/061bb9e3-6570-46b7-8572-e9356c55b214" />

**Chapter list**
<img width="980" height="916" alt="Chapter list" src="https://github.com/user-attachments/assets/752199ea-aa9d-4280-a2f8-6b11d38cf8d2" />
