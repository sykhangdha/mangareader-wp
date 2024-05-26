# MangaStarter Rewritten

# Last WP Version Tested: 6.5.3

Install Guide: [Available Here](https://skyha.rf.gd/mangastarter-install-guide/)

Download: [MangaStarter-Revival V1(5.22.24)](https://github.com/sykhangdha/mangareader-wp/releases/download/Revival1/MangaStarter-RevivalV1.zip)

[DEMO HERE
](http://skymanga.42web.io/)

# Quick Install Method
- Download the theme and activate it in wordpress
- Install and activate the required plugins(will show after activating theme)
- Import this xml file [HERE](https://raw.githubusercontent.com/sykhangdha/mangareader-wp/main/skymanga.WordPress.2024-05-26.xml) (CTRL + S  to save the file)
  	- Tools -> Import -> Wordpress(install if not already installed) -> Upload xml file
- This will import some mangas/chapters for you already and give you the same look from the skymanga website

# Upcoming Additions/Hotfixes for next release
- Quick Chapter extension 🚨 not yet available
- Code cleanup/fixes by checking for any error logs detected with WP_Debug function
  	- Null check
  	- CSS duplicates
  	- Unused php funcitons
  	- Issues with chapter listing when using quick chapter extension

# What's New?

 - Style.CSS changes
	 - The new rewrite will make it so you no longer have to copy and paste the style.css for the site to work properly.
- New homepage design
	 - Shows up to 15 mangas and up to 3 of there latest chatpers(see images below)
		 - View more chapters button added to redirect to the recent chapters list template *NEW
 - New reader functions
	 - Added new javascript functions to the reader
		 - Page by Page | List view choice (remembers session)
		 - Next/Prev chapter button added + going to next chapter using arrow keys or clicking on the last image
		 - Preload fixes
 - New Manga Archive page
	 - The new archive page now shows the 3 latest chapters added and the date
         - Filter by search + A-Z index added.
         - Recent chapters page
 - Manga Info page changes
	 - Better chapter listing view
	 - Changed so that it wil replace [Manga Name] 1 with Chapter 1 instead (see content-manga.php for changes)
		 - Remove the preg replace function to remove this option

## Download

- [Release Page](https://github.com/sykhangdha/mangareader-wp/releases)


## Homepage (With Choso theme)
Style 1:
![enter image description here](http://i.epvpimg.com/Z1oFbab.png)
Style 2:
![enter image description here](http://i.epvpimg.com/1RL9cab.png)

## Manga archive Page

![enter image description here](http://i.epvpimg.com/blp3bab.png)

## Manga Info Page

![enter image description here](http://i.epvpimg.com/WTvfdab.png)

## Recent Chapters List

![enter image description here](http://epvpimg.com/g5lzcab.png)

Alternative Style:

![enter image description here](http://i.epvpimg.com/gQJebab.png)

## NEW: Theme/Style settings page

![themepage](http://i.epvpimg.com/ltKVcab.png)

