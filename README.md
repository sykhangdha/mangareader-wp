# MangaStarter Rewritten
With development being basically done on my WP-mangareader-plugin project I am in the process of rewriting parts of the code for the orginal MangaStarter theme to work better. The rewrite will implement the wp-mangareader code into the current reader. This will be a one time update and any additional updates will be added if there are any additions/fixes to the reader. The latest uploade in the 'TEST' folder is pretty much a working release but does not include the new homepage I am working on and some reader functions. 


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
		 - Preload fixes(w.i.p)
 - New Manga Archive page
	 - The new archive page now shows the 3 latest chapters added and the date
         - Filter by search + A-Z index added. 
 - Manga Info page changes
	 - Better chapter listing view
	 - Changed so that it wil replace [Manga Name] 1 with Chapter 1 instead (see content-manga.php for changes)
		 - Remove the preg replace function to remove this option

## Download

- [There are two folders in the github TEST/BETA. Check to see which one has been updated to download. Download the xml file in the folder and import using the wordpress import tool](https://github.com/sykhangdha/mangareader-wp/releases)


## Homepage
![enter image description here](http://i.epvpimg.com/ngWVeab.png)

## Manga archive Page

![enter image description here](http://i.epvpimg.com/blp3bab.png)

## Manga Info Page

![enter image description here](http://i.epvpimg.com/WTvfdab.png)

## Recent Chapters List

![enter image description here](http://epvpimg.com/g5lzcab.png)
