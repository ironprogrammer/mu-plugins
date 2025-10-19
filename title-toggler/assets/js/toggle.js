/**
 * Title Toggler - JavaScript
 * Handles toggle state and title transformation
 */

(function() {
    'use strict';

    const STORAGE_KEY = 'tt_show_slug_titles';

    // Store original titles for toggling back
    const originalTitles = new Map();

    /**
     * Convert a slug to lowercase with spaces
     * Example: "how-i-built-this" -> "how i built this"
     */
    function slugToTitle(slug) {
        if (!slug) return '';

        // Replace hyphens with spaces and keep lowercase
        return slug.replace(/-/g, ' ');
    }

    /**
     * Extract slug from a post URL
     * Handles various WordPress permalink structures
     */
    function extractSlugFromUrl(url) {
        try {
            const urlObj = new URL(url);
            const pathname = urlObj.pathname;

            // Remove trailing slash
            const cleanPath = pathname.replace(/\/$/, '');

            // Get the last segment of the path (the slug)
            const segments = cleanPath.split('/');
            const slug = segments[segments.length - 1];

            return slug;
        } catch (e) {
            console.error('Error extracting slug from URL:', url, e);
            return null;
        }
    }

    /**
     * Get slug for a title element (link or heading)
     */
    function getSlugForElement(element) {
        // If it's a link, use its href
        if (element.tagName === 'A' && element.getAttribute('href')) {
            return extractSlugFromUrl(element.getAttribute('href'));
        }

        // If it's a heading on a single post page, use the current page URL
        if (element.tagName.match(/^H[1-6]$/)) {
            return extractSlugFromUrl(window.location.href);
        }

        return null;
    }

    /**
     * Find all post title elements on the page
     * Targets common WordPress post title patterns
     */
    function getPostTitleElements() {
        const elementsSet = new Set();

        // First, look for links inside post title elements
        const linkSelectors = [
            '.wp-block-post-title a',
            '.entry-title a',
            'h1.post-title a',
            'h2.post-title a',
            'h1 a[rel="bookmark"]',
            'h2 a[rel="bookmark"]',
            'h3 a[rel="bookmark"]'
        ];

        linkSelectors.forEach(selector => {
            const found = document.querySelectorAll(selector);
            found.forEach(el => elementsSet.add(el));
        });

        // Then, look for post title headings without links (single post pages)
        // Only add these if they don't already have a link child
        const headingSelectors = [
            'h1.wp-block-post-title',
            'h2.wp-block-post-title',
            '.entry-title'
        ];

        headingSelectors.forEach(selector => {
            const found = document.querySelectorAll(selector);
            found.forEach(heading => {
                // Only add if it doesn't contain a link and isn't already in our list
                const hasLink = heading.querySelector('a');
                if (!hasLink && !elementsSet.has(heading)) {
                    elementsSet.add(heading);
                }
            });
        });

        return Array.from(elementsSet);
    }

    /**
     * Sequential character swap with glitch effect
     */
    function sequentialSwapText(oldText, newText, progress) {
        const oldChars = oldText.replace(/ /g, '');
        const newChars = newText.replace(/ /g, '');
        const revealCount = Math.floor((progress / 100) * newChars.length);

        let result = '';
        let charIndex = 0;

        for (let i = 0; i < newText.length; i++) {
            if (newText[i] === ' ') {
                result += ' ';
            } else {
                result += charIndex < revealCount ? newText[i] : (oldChars[charIndex] || newText[i]);
                charIndex++;
            }
        }

        return result;
    }

    /**
     * Apply glitch effect with sequential character swap
     */
    function applyGlitchTransition(titleLink, oldText, newText) {
        let frame = 0;
        const totalFrames = 12;
        const frameDelay = 33; // ~30fps, total 400ms

        // Add glitch animation class and do first swap immediately
        titleLink.classList.add('tt-glitching');
        titleLink.textContent = sequentialSwapText(oldText, newText, 0);
        frame++;

        const interval = setInterval(() => {
            const progress = (frame / totalFrames) * 100;
            titleLink.textContent = sequentialSwapText(oldText, newText, progress);

            frame++;

            if (frame > totalFrames) {
                clearInterval(interval);
                titleLink.textContent = newText;
            }
        }, frameDelay);

        // Remove glitch class when animation ends
        setTimeout(() => {
            titleLink.classList.remove('tt-glitching');
        }, 400);
    }

    /**
     * Transform titles between original and slug-based versions
     * @param {boolean} showSlug - Whether to show slug-based titles (true) or original titles (false)
     * @param {boolean} animate - Whether to apply glitch animation effect
     */
    function applyTitleTransform(showSlug, animate = false) {
        const titleElements = getPostTitleElements();

        titleElements.forEach(titleElement => {
            const slug = getSlugForElement(titleElement);
            if (!slug) return;

            // Store original title if we haven't already
            if (!originalTitles.has(titleElement)) {
                originalTitles.set(titleElement, titleElement.textContent);
            }

            // Determine target text
            const targetText = showSlug ? slugToTitle(slug) : originalTitles.get(titleElement);
            if (!targetText) return;

            // Apply transformation with or without animation
            if (animate) {
                const currentText = titleElement.textContent;
                applyGlitchTransition(titleElement, currentText, targetText);
            } else {
                titleElement.textContent = targetText;
            }
        });
    }

    /**
     * Get the current preference from localStorage
     */
    function getPreference() {
        const stored = localStorage.getItem(STORAGE_KEY);
        return stored === 'true';
    }

    /**
     * Save preference to localStorage
     */
    function savePreference(showSlug) {
        localStorage.setItem(STORAGE_KEY, showSlug.toString());
    }

    /**
     * Initialize the toggle functionality
     */
    function init() {
        const toggle = document.getElementById('tt-toggle');
        const showSlug = getPreference();

        if (!toggle) {
            // Toggle not on this page, but we still need to apply preference
            // Only transform if slug titles are enabled (animate for visual effect)
            if (showSlug) {
                applyTitleTransform(true, true);
            }
            return;
        }

        // Set toggle state from localStorage
        toggle.checked = showSlug;

        // On page load: only activate if slug titles are enabled
        // If disabled, let WordPress render naturally without JS intervention
        if (showSlug) {
            applyTitleTransform(true, true);
        }

        // Listen for toggle changes (always animate user interactions)
        toggle.addEventListener('change', function() {
            const newState = this.checked;
            savePreference(newState);
            applyTitleTransform(newState, true);
        });
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
