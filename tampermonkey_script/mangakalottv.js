// ==UserScript==
// @name         Image Link Grabber
// @namespace    http://tampermonkey.net/
// @version      0.5
// @description  Grab all image links and copy them into clipboard
// @author       You
// @match        https://ww8.mangakakalot.tv/chapter/*/*
// @grant        GM_setClipboard
// ==/UserScript==

(function() {
    'use strict';

    // Function to grab image links
    function grabImageLinks() {
        // Select all images with the data-src or src attribute
        let images = document.querySelectorAll('img[data-src], img[src]');

        // Array to store image URLs
        let imageLinks = [];

        // Loop through images and extract URLs
        images.forEach(function(img) {
            let src = img.getAttribute('data-src') || img.getAttribute('src');
            if (src && src.startsWith("https://")) {
                imageLinks.push(src);
            }
        });

        // Copy image URLs to clipboard
        let linksText = imageLinks.join("\n");
        GM_setClipboard(linksText);

        // Show popup message
        alert("Image links copied!");
    }

    // Function to create a button to manually trigger the image link grabber
    function createButton() {
        let button = document.createElement('button');
        button.innerHTML = 'Grab All Image Links';
        button.style.position = 'fixed';
        button.style.bottom = '10px';
        button.style.right = '10px';
        button.style.zIndex = '1000';
        button.style.backgroundColor = '#4CAF50';
        button.style.color = 'white';
        button.style.border = 'none';
        button.style.padding = '10px 20px';
        button.style.cursor = 'pointer';

        button.addEventListener('click', grabImageLinks);
        document.body.appendChild(button);
    }

    // Check if the current page URL matches the desired pattern
    if (window.location.href.match(/https:\/\/ww8\.mangakakalot\.tv\/chapter\/.+\/.+/)) {
        createButton();
    }
})();
