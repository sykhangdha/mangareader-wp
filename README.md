# MangaStarter Rewritten
With development being basically done on my WP-mangareader-plugin project I am in the process of rewriting parts of the code for the orginal MangaStarter theme to work better. The rewrite will implement the wp-mangareader code into the current reader.


# What's New?

 - Style.CSS changes
	 - The new rewrite will make it so you no longer have to copy and paste the style.css for the site to work properly. 
 - New reader functions
	 - Added new javascript functions to the reader
		 - Page by Page | List view choice (remembers session)
		 - Next/Prev chapter button added + going to next chapter using arrow keys or clicking on the last image
		 - Preload fixes(w.i.p)
 - New Manga Archive page
	 - The new archive page now shows the 3 latest chapters added and the date
 - Manga Info page changes
	 - Better chapter listing view
	 - Changed so that it wil replace [Manga Name] 1 with Chapter 1 instead (see content-manga.php for changes)
		 - Remove the preg replace function to remove this option

## Download
Check the test folder


## Homepage
![enter image description here](http://i.epvpimg.com/3Xujeab.png)


## Manga archive Page

![enter image description here](http://i.epvpimg.com/gZ7bgab.png)

## Chapter list page

![enter image description here](http://i.epvpimg.com/WTvfdab.png)
