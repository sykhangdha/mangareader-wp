## Upcoming Changes

- Full theme rework, no ETA of full release, users can try the pre-release [HERE](https://github.com/sykhangdha/wp-mangareader-plugin/releases/tag/AlphaWP)
   - Adds custom taxonomy to create mangas and add chapters all through wordpress(removed FTP function as it was causing too many issues)
   - Improved the way mangas grab chapters and better manga info page. You can now add genres, set status of manga, and a new manga archive section
   - Implements functions from wp-mangareader-plugin
  

# 🎉 MangaStarter Rewritten [Initial Release] 🎉

**1.0 - Initial Release: 5/15/2025(Uses new retro theme)**  
[📥 Release Notes + Download](https://github.com/sykhangdha/mangareader-wp/releases/tag/v1)

[👀 Live Demo](http://skymanga.42web.io/)

Welcome to *MangaStarter Rewritten*, a custom manga viewer theme using Windows 11(fluent) design elements!

*Note: Please clear cache for site if there are issues with website not showing the new updates!

## Added Changes(viewable in the live demo)
- Add chapter list to sidebar for manga viewer
- Switched method for scrolling
- Improve sidebar view and manga page(currently shows chapter list twice when going back to chapter list)
- Admin settings improvement
- Improve image loading for slow servers
- Theming system(Site is currently using 'Manga95' theme by me that follows design elements from Windows95/Chicago95(linux theme)

---

## 🚀 Getting Started

### ✨ What’s This Theme About?
*MangaStarter Rewritten* is designed to bring your manga collection to wordpress.
### 📋 Installation Steps
1. **Upload the Theme**  
   - Drop the `manga-reader-theme` folder into `wp-content/themes/`.  OR upload theme through wordpress
2. **Set Up Your Manga Folder**  
   - Create a `manga/` folder in your WordPress root (`/manga/`).  
   - Add manga folders (e.g., `manga/manga-name-1/`) with a `cover.jpg` and chapter subfolders with images.  
3. **Activate It**  
   - Head to `Appearance > Themes` in your WordPress dashboard and activate "Manga Reader Theme".  
4. **Fix URL Rules**  
   - Go to `Settings > Permalinks` and click "Save Changes".  
5. **Dive In**  
   - Visit `yoursite.com/manga/manga-name` to check out your first manga!  

---

## 🌟 Key Features

### 📖 Manga Page with Cover & Chapter List
- **What You Get**: See a manga’s cover (if you add one) and browse chapters without page reloads.  
- **How It Works**:  
  - Just go to `yoursite.com/manga/manga-name`—the theme handles it automatically with URL magic.  
  - Grabs the cover from `manga/manga-name/cover.jpg`.  
  - Loads chapters via AJAX (`manga_reader_get_chapters`) from files (`manga/manga-name/*`) or the database (`manga_chapter` post type).  


### ⚡ AJAX-Powered Chapter & Image Loading
- **What You Get**: Chapters and images pop up quickly using AJAX.  
- **How It Works**:  
  - `script.js` calls `manga_reader_get_chapters` for the chapter list.  
  - Click a chapter, and `manga_reader_get_images` fetches the pics from folders (`manga/manga-name/chapter-name/*.jpg`) or the database.  
- **What it does**: Keeps your server happy by loading only what’s needed and speeds up navigation.  

### 🗂️ Custom Post Type for Chapters
- **What You Get**: Store chapters in the WordPress database alongside file-based ones.  
- **How It Works**:  
  - Add chapters through the admin panel or code.  
  - Each chapter saves the manga name (`manga_name` meta), images (`chapter_images` meta), and URLs (`chapter_image_urls` meta).  
- **What it does**: Manage chapters without touching files and use WordPress’s built-in tools.  

### 🛠️ Admin Interface for Management
- **What You Get**: A "Manga Reader" section in your WordPress admin to control everything.  
- **Features**:  
  - **➕ Add Manga Folder**: Create new `manga/` folders.  
  - **🎨 Edit Manga Cover**: Upload or remove covers.  
  - **🔍 Scan Manga Folders**: List all manga and covers.  
  - **📝 Add/Edit Chapters**: Add or tweak chapters with images/URLs.  
- **How it Works**: The `manga_reader_settings_page` offers forms, powered by AJAX endpoints like `manga_reader_add_manga_folder`.  
- **What it does**: Central hub for admins, no file editing needed.  

### 🎨 Customizable Color Scheme
- **What You Get**: Tweak colors to match your style via the WordPress Customizer.  
- **Options**: Accent color (links, buttons), background, header, menu, glass panel tint, footer, and body text.  
- **How it Works**: `manga_reader_customize_register` adds a "Color Settings" section, with live CSS updates from `manga_reader_dynamic_styles`.  
- **What it does**: Personalize without coding, see changes instantly.  

### 📢 Site Announcement
- **What You Get**: Share updates or notes with a custom announcement.  
- **How it Works**: Set a title and text in "Site Settings" in the admin panel—it shows up on the frontend.  
- **What it does**: Keep readers informed with ease.  

### 🔄 Update Site Button (Cache Clearing)
- **What You Get**: An optional button to clear CSS/JS cache for users.  
- **How it Works**: Enable it in "Site Settings"—it refreshes the homepage via `manga_reader_clear_cache`.  
- **What it does**: Ensures everyone sees the latest updates.  

### 🔗 Shortcode for Manga Viewer (Optional)
- **What You Get**: Embed a manga viewer with `[display_manga name="manga-name"]`—optional since `yoursite.com/manga/manga-name` works automatically.  
- **How it Works**: `manga_reader_display_manga` builds the viewer; use the shortcode for custom spots like posts.  
- **What it does**: Flexibility to show manga anywhere on your site.  

---

## 📂 Folder Structure

This theme uses a `manga/` folder in your WordPress root and the theme directory to keep things organized.

```
/
├── manga/
│   ├── manga-name-1/
│   │   ├── cover.jpg           # Manga cover pic 🎨
│   │   ├── chapter-1/          # Chapter folder 📁
│   │   │   ├── page1.jpg       # Chapter images 🖼️
│   │   │   ├── page2.jpg
│   │   │   └── ...
│   │   ├── chapter-2/
│   │   └── ...
│   ├── manga-name-2/
│   └── ...
└── wp-content/
    └── themes/
        └── manga-reader-theme/
            ├── admin-script.js   # Admin JavaScript ⚙️
            ├── admin-style.css   # Admin CSS 🎨
            ├── functions.php     # Theme logic & hooks 🛠️
            ├── index.php         # Homepage template 🏠
            ├── manga-page.php    # Manga page template 📖
            ├── script.js         # Frontend JavaScript 🚀
            ├── style.css         # Main styles 🎨
            └── style-manga.css   # Manga-specific styles 📜
```

### 📍 Key Spots
- **`manga/`**: Holds your manga files (covers and chapters) in the root.  
- **`manga-reader-theme/`**: The theme folder with:  
  - **`functions.php`**: Where the setup, AJAX, and admin magic happens.  
  - **`manga-page.php`**: Template for `yoursite.com/manga/manga-name`.  
  - **`script.js`**: Powers frontend AJAX calls.  
  - **`style-manga.css`**: Styles just for manga pages.  

---

## 🎮 Usage

### 🌐 Accessing Manga Pages
- Hit `yoursite.com/manga/manga-name` to view a manga—no shortcode needed!  

### 📝 Embedding Manga (Optional)
- Add `[display_manga name="manga-name"]` to a page or post (e.g., `[display_manga name="cant-believe"]`) for a custom viewer.  

### ➕ Adding Manga
- Use `Manga Reader > Manga Management` in the admin panel to create folders and upload covers.  
- Or, manually add folders to `manga/` with the right setup.  

### 📚 Adding Chapters
- Add chapters via the admin panel with images or URLs.  
- Or, drop them into `manga/manga-name/chapter-name/` on the file system.  

### 🎨 Customizing
- Go to `Appearance > Customize > Color Settings` to tweak colors.  
- Use `Manga Reader > Site Settings` to enable the "Update Site" button or set an announcement.  

---

## 🛠️ Development Notes
- **🔗 URL Rewriting**: Uses `add_rewrite_rule` to map `manga/manga-name` to `manga-page.php`.  
- **⚡ AJAX**: Dynamic loading runs through `functions.php` endpoints.  
- **✂️ Normalization**: Manga names get cleaned (spaces to hyphens, lowercase, no special chars) like "Can't Believe" to `cant-believe`.  
- **🔄 Cache Busting**: `manga_reader_get_asset_version` keeps assets fresh.  

---

## 🤝 Contributing
1. Fork this repo.  
2. Create a branch (`git checkout -b feature/your-feature`).  
3. Make your changes.  
4. Commit (`git commit -m 'Add your feature'`).  
5. Push (`git push origin feature/your-feature`).  
6. Open a pull request!  

---

## 🙌 Acknowledgments
- Built with WordPress and jQuery.  
- Shoutout to [O.M.V](https://rsm23.github.io/O.M.V-V2/) and MangaDex for inspiration.  
- Made because I wanted a simple manga viewer

---

## 🕒 Old Version (No Longer Supported)
**Install Guide**: [Click Here](https://skyha.rf.gd/mangastarter-install-guide/)  
**Download**: [MangaStarter-Revival-HotFix2 (6.03.24)](https://github.com/sykhangdha/mangareader-wp/releases/download/HotFix2/mangastart-Rebuilt-Hotfix2.zip)
