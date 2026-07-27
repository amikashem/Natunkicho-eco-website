/**
 * nk-footer-slider.js
 * Loads latest posts via REST and duplicates items for infinite scrolling
 * Works with localized vars: nkFooterSliderVars (category, limit, siteUrl)
 */

(async function () {
  // Wait for DOM
  document.addEventListener("DOMContentLoaded", async function () {
    try {
      const wrapper = document.querySelector('.latest-post-slider-wrapper');
      const slider = document.querySelector('.slider-posts');
      if (!wrapper || !slider) return;

      // Use localized vars if available (set by PHP), otherwise fallback
      const siteUrl = (window.nkFooterSliderVars && nkFooterSliderVars.siteUrl) ? nkFooterSliderVars.siteUrl : window.location.origin;
      const limit = (window.nkFooterSliderVars && nkFooterSliderVars.limit) ? parseInt(nkFooterSliderVars.limit, 10) : 6;
      const category = (window.nkFooterSliderVars && nkFooterSliderVars.category) ? nkFooterSliderVars.category : '';

      // Build REST API URL
      let apiUrl = `${siteUrl.replace(/\/$/, '')}/wp-json/wp/v2/posts?per_page=${limit}&_fields=link,title,excerpt,featured_media`;
      if (category) {
        // Try to fetch by category slug -> get its ID first
        try {
          const catRes = await fetch(`${siteUrl.replace(/\/$/, '')}/wp-json/wp/v2/categories?slug=${encodeURIComponent(category)}`);
          if (catRes.ok) {
            const catData = await catRes.json();
            if (Array.isArray(catData) && catData.length) {
              const catId = catData[0].id;
              apiUrl += `&categories=${catId}`;
            }
          }
        } catch (e) {
          // ignore category lookup error; fallback to all posts
          console.warn('Category lookup failed', e);
        }
      }

      const response = await fetch(apiUrl);
      if (!response.ok) {
        console.error('Failed to fetch posts for footer slider', response.status, response.statusText);
        return;
      }
      const posts = await response.json();

      let html = '';

      for (const post of posts) {
        const title = (post.title && post.title.rendered) ? post.title.rendered : 'Untitled';
        const link = post.link || '#';
        const rawExcerpt = (post.excerpt && post.excerpt.rendered) ? post.excerpt.rendered : '';
        const excerpt = rawExcerpt.replace(/<[^>]+>/g, '').split(' ').slice(0, 20).join(' ') + '...';
        let imageUrl = 'https://via.placeholder.com/300x200?text=No+Image';

        if (post.featured_media) {
          try {
            const mediaRes = await fetch(`${siteUrl.replace(/\/$/, '')}/wp-json/wp/v2/media/${post.featured_media}`);
            if (mediaRes.ok) {
              const media = await mediaRes.json();
              if (media && media.source_url) imageUrl = media.source_url;
            }
          } catch (e) {
            console.warn('Failed to fetch media for footer slider', e);
          }
        }

        html += `
          <li>
            <a href="${link}" target="_blank" rel="noopener">
              <img src="${imageUrl}" alt="${escapeHtml(title)}" loading="lazy">
            </a>
            <a href="${link}" target="_blank" rel="noopener">${title}</a>
            <p>${escapeHtml(excerpt)}</p>
          </li>`;
      }

      // Duplicate for smooth infinite scroll
      slider.innerHTML = html + html;

    } catch (error) {
      console.error("Error loading posts:", error);
    }
  });

  // small helper to escape HTML in text inserted into template
  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }
})();
