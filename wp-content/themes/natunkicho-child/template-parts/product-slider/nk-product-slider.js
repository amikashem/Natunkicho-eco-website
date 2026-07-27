// NK Product Slider JS - Enhanced with better calculation and error handling
document.addEventListener('DOMContentLoaded', function() {
  const sliders = document.querySelectorAll('.nk-product-slider');
  
  if (!sliders.length) return;

  sliders.forEach(slider => {
    const wrapper = slider.closest('.nk-product-slider-wrapper');
    const btnNext = wrapper?.querySelector('.nk-next');
    const btnPrev = wrapper?.querySelector('.nk-prev');
    
    if (!btnNext || !btnPrev) return;

    // Function to calculate scroll amount
    const getScrollAmount = () => {
      const firstCard = slider.querySelector('.nk-product-card');
      if (!firstCard) return 300; // fallback
      
      const cardStyle = window.getComputedStyle(firstCard);
      const cardWidth = firstCard.offsetWidth;
      const gap = parseInt(cardStyle.marginRight || 0) || 25; // Use CSS gap or fallback
      
      return cardWidth + gap;
    };

    // Initialize scroll amount
    let scrollAmount = getScrollAmount();

    // Recalculate on window resize
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        scrollAmount = getScrollAmount();
      }, 250);
    });

    // Next button click
    btnNext.addEventListener('click', () => {
      slider.scrollBy({ 
        left: scrollAmount, 
        behavior: 'smooth' 
      });
    });

    // Previous button click
    btnPrev.addEventListener('click', () => {
      slider.scrollBy({ 
        left: -scrollAmount, 
        behavior: 'smooth' 
      });
    });

    // Update arrow visibility based on scroll position
    const updateArrowVisibility = () => {
      const scrollLeft = slider.scrollLeft;
      const scrollWidth = slider.scrollWidth;
      const clientWidth = slider.clientWidth;
      
      btnPrev.style.opacity = scrollLeft <= 10 ? '0.5' : '1';
      btnPrev.style.cursor = scrollLeft <= 10 ? 'not-allowed' : 'pointer';
      
      btnNext.style.opacity = scrollLeft + clientWidth >= scrollWidth - 10 ? '0.5' : '1';
      btnNext.style.cursor = scrollLeft + clientWidth >= scrollWidth - 10 ? 'not-allowed' : 'pointer';
    };

    // Initial update
    updateArrowVisibility();
    
    // Update on scroll
    slider.addEventListener('scroll', updateArrowVisibility);
  });
});