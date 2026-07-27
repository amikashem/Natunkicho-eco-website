/**
 * NK Dropdown Menu - Cross-browser Fix (Firefox + Chrome + Edge)
 * Fixes link click behavior inside dropdowns
 */

document.addEventListener('DOMContentLoaded', function () {
  const dropdownWrappers = document.querySelectorAll('.nk-dropdown-menu-wrapper');
  if (dropdownWrappers.length === 0) return;

  dropdownWrappers.forEach(wrapper => {
    const dropdownColumns = wrapper.querySelectorAll('.nk-dropdown-column');
    const triggers = wrapper.querySelectorAll('.nk-dropdown-trigger');

    // Overlay for outside clicks
    const overlay = document.createElement('div');
    overlay.className = 'nk-dropdown-overlay';
    wrapper.appendChild(overlay);

    function closeAllDropdowns() {
      dropdownColumns.forEach(col => {
        col.classList.remove('active');
        const trigger = col.querySelector('.nk-dropdown-trigger');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
      });
      overlay.style.display = 'none';
    }

    function toggleDropdown(column, trigger) {
      const isActive = column.classList.contains('active');
      closeAllDropdowns();
      if (!isActive) {
        column.classList.add('active');
        trigger.setAttribute('aria-expanded', 'true');
        overlay.style.display = 'block';
      }
    }

    // Initialize as closed
    closeAllDropdowns();

    // Click event for triggers
    triggers.forEach(trigger => {
      trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        // ⚠️ Removed e.preventDefault()
        // Only prevent default if trigger has no href (not a real link)
        if (!this.getAttribute('href') || this.getAttribute('href') === '#') {
          e.preventDefault();
        }

        const column = this.closest('.nk-dropdown-column');
        toggleDropdown(column, this);
      });
    });

    // Allow links inside dropdowns to work in Firefox
    wrapper.querySelectorAll('.nk-dropdown-link, .nk-dropdown-column a').forEach(link => {
      link.addEventListener('click', function (e) {
        // Let the link work, then close dropdown
        setTimeout(closeAllDropdowns, 150);
      });
    });

    // Overlay & outside click close
    overlay.addEventListener('click', closeAllDropdowns);
    document.addEventListener('click', e => {
      if (!wrapper.contains(e.target)) closeAllDropdowns();
    });

    // Escape key close
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') closeAllDropdowns();
    });
  });
});
