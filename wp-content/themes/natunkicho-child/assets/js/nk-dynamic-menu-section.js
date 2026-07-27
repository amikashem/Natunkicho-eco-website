/* NK Dynamic Menu - Improved Arrow & Scroll Handling with Mobile Support */
document.addEventListener('DOMContentLoaded', function () {
  const wrapper = document.querySelector('.nk-dynamic-menu-wrapper');
  if (!wrapper) return;

  const container = wrapper.querySelector('.nk-dynamic-menu-container');
  if (!container) return;

  // Check if mobile device
  const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

  // Create or reuse arrows (only for desktop)
  let leftArrow, rightArrow;

  if (!isMobile) {
    leftArrow = wrapper.querySelector('.nk-scroll-arrow.left');
    rightArrow = wrapper.querySelector('.nk-scroll-arrow.right');

    if (!leftArrow) {
      leftArrow = document.createElement('div');
      leftArrow.className = 'nk-scroll-arrow left';
      leftArrow.innerHTML = '&#10094;';
      leftArrow.setAttribute('aria-label', 'Scroll left');
      wrapper.appendChild(leftArrow);
    }

    if (!rightArrow) {
      rightArrow = document.createElement('div');
      rightArrow.className = 'nk-scroll-arrow right';
      rightArrow.innerHTML = '&#10095;';
      rightArrow.setAttribute('aria-label', 'Scroll right');
      wrapper.appendChild(rightArrow);
    }
  }

  // Show or hide arrows depending on overflow (desktop only)
  function updateArrowsVisibility() {
    if (isMobile) return;
    
    const maxScrollLeft = container.scrollWidth - container.clientWidth;
    if (maxScrollLeft > 5) {
      leftArrow.style.display = container.scrollLeft > 10 ? 'block' : 'none';
      rightArrow.style.display = container.scrollLeft < maxScrollLeft - 10 ? 'block' : 'none';
    } else {
      leftArrow.style.display = 'none';
      rightArrow.style.display = 'none';
    }
  }

  // Smooth scroll on click (desktop only)
  if (!isMobile) {
    leftArrow.addEventListener('click', () => {
      container.scrollBy({ left: -container.clientWidth / 2, behavior: 'smooth' });
      setTimeout(updateArrowsVisibility, 300);
    });
    
    rightArrow.addEventListener('click', () => {
      container.scrollBy({ left: container.clientWidth / 2, behavior: 'smooth' });
      setTimeout(updateArrowsVisibility, 300);
    });

    // Auto-scroll while hovering arrows (desktop only)
    let autoScrollTimer = null;
    function startAutoScroll(dir) {
      stopAutoScroll();
      autoScrollTimer = setInterval(() => {
        container.scrollLeft += dir === 'left' ? -8 : 8;
        updateArrowsVisibility();
      }, 20);
    }
    
    function stopAutoScroll() {
      if (autoScrollTimer) {
        clearInterval(autoScrollTimer);
        autoScrollTimer = null;
      }
    }

    leftArrow.addEventListener('mouseenter', () => startAutoScroll('left'));
    leftArrow.addEventListener('mouseleave', stopAutoScroll);
    rightArrow.addEventListener('mouseenter', () => startAutoScroll('right'));
    rightArrow.addEventListener('mouseleave', stopAutoScroll);
  }

  // Mobile touch enhancements
  if (isMobile) {
    let startX;
    let scrollLeft;

    container.addEventListener('touchstart', (e) => {
      startX = e.touches[0].pageX - container.offsetLeft;
      scrollLeft = container.scrollLeft;
    });

    container.addEventListener('touchmove', (e) => {
      if (!startX) return;
      const x = e.touches[0].pageX - container.offsetLeft;
      const walk = (x - startX) * 2; // Scroll sensitivity
      container.scrollLeft = scrollLeft - walk;
    });
  }

  // Update on scroll/resize and after content load
  container.addEventListener('scroll', updateArrowsVisibility);
  window.addEventListener('resize', () => {
    updateArrowsVisibility();
  });

  // In case images/fonts change layout after load:
  window.addEventListener('load', () => {
    updateArrowsVisibility();
  });

  // Initial call
  updateArrowsVisibility();
});