
// Pre-Req //
- # IMPORTANT # Please note that MangaStarter IS REQUIRED! I am only updating the theme with new changes! The theme can be purchased here https://www.codester.com/items/6001/mangastarter-build-a-manga-reader-with-wordpress. I may upload theme with full changes on a later date on my website, but strongly recommend purchasing the theme to support the developer.
- Make sure to set thumbnail size to at least 220 x 320 or there may be some issues with thumbnails being too blurry!
- A-Z Listing plugin is used for Advanced Search Page
- wp-post-nav plugin


# mangareader-wp
A simple manga reader theme for wordpress using "MangaStarter"
http://hasky.epizy.com/mangareader/

# New MangaStarter theme(white)
![alt text](http://i.epvpimg.com/KFV4fab.png)

# New MangaStarter theme(dark) w/ new manga directory page
![alt text](http://i.epvpimg.com/qrbHcab.png)

# New manga info page with better chapter listing
![alt text](http://i.epvpimg.com/0G9Bcab.png)

# Based on MangaStarter/UiKIT
The following theme uses MangaStarter as the base and adds additional features to the theme.
- Updated functions.php for "Manga Directory" page
- CSS Updates
  - Current custom theme available: "MangaReader"
  - Thumbnail size fix, please change thumbnail size to 225x320 for best results with the manga reader theme
- Fixed UI issues for mobile
- Additional plugin recommendations added in install documentation
- Recently viewed chapters
- "Advanced Search" page updated(W.I.P)

# Upcoming Featuers
- Next/Prev chapter button
- UI Fixes
- Dark Mode
- More theme options(Currently only blue)
- "Text View" for Manga Directory
- Search by Genre
- Fix issue with title getting cut off on homepage

# Installation Guide


// To Install the "MangaReader" theme use style.css! //
- Go to Appearance->Customize and in "Additional CSS" add the code from "style.css" to apply the theme. More themes will
be available
-YellowPencil is recommended but not required, I will include both the regular style.css + the export code for yellowpencil to import
    -Simply copy and paste code to import!

// To Update Manga Directory Page //
- Replace functions.php in main theme folder
- Replace "components/loop/content-archive.php" with one from this github

// Previous and Next Chapter
- Recommended plugin: wp-post-nav(note: info on how to display wp post nav will be in the documentation for the plugin)
      - Used CSS to fix previous and next post being displayed strangely on mobile
      - Currently working on editing the plugin to work better with the theme, but for now this is just a quick fix

