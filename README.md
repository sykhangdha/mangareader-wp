
// Pre-Req //
- Based on MangaStarter, just updating to work with wordpress 6.0 and adding more features
     -Download theme here: https://github.com/sykhangdha/mangareader-wp/releases
- Make sure to set thumbnail size to at least 220 x 320 or there may be some issues with thumbnails being too blurry!
- A-Z Listing plugin is used for Advanced Search Page
- wp-post-nav plugin
-DOCUMENTATION NO YET AVAILABLE! Theme works but documentation for how to install will be released at a later date.


# mangareader-wp
A simple manga reader theme for wordpress using "MangaStarter"
http://hasky.rf.gd/mangareader

# New MangaStarter theme(white) w/ new latest chapters style
![alt text](http://i.epvpimg.com/Yf3hfab.png)

# New MangaStarter theme(dark) w/ new latest chapters style
![alt text](http://i.epvpimg.com/LTKtcab.png)

# New manga info page with better chapter listing
![alt text](http://i.epvpimg.com/f4wbcab.png)

# Based on MangaStarter/UiKIT
The following theme uses MangaStarter as the base and adds additional features to the theme.
- Updated functions.php for "Manga Directory" page
- CSS Updates
  - Current custom theme available: "MangaReader"
  - Thumbnail size fix, please change thumbnail size to 225x320 for best results with the manga reader theme
- Fixed UI issues for mobile
- Additional plugin recommendations added in install documentation
- Recently viewed chapters(use any plugin from wordpress repo)

# Installation Guide

//Documentation has been added!

// To Install the "MangaReader" theme use style.css! //
- Go to Appearance->Customize and in "Additional CSS" add the code from "style.css" to apply the theme. More themes will
be available
-YellowPencil is recommended but not required, I will include both the regular style.css + the export code for yellowpencil to import
    -Simply copy and paste code to import!

// To Update Manga Directory Page //
- Replace functions.php in main theme folder
- Replace "components/loop/content-archive.php" with one from this github

// Previous and Next Chapter
- Recommended plugin: wp-post-nav

