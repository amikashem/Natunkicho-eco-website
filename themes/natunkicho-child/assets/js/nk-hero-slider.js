document.addEventListener('DOMContentLoaded', function() {
    const sliders = document.querySelectorAll('.nk-hero-slider');
    
    sliders.forEach(slider => {
        const track = slider.querySelector('.nk-slider-track');
        const slides = slider.querySelectorAll('.nk-slide');
        const prevBtn = slider.querySelector('.nk-slider-prev');
        const nextBtn = slider.querySelector('.nk-slider-next');
        const dotsContainer = slider.querySelector('.nk-slider-dots');
        
        const interval = parseInt(slider.dataset.interval) || 5000;
        
        let currentSlide = 0;
        let slideInterval;
        let isPaused = false;
        
        // Create dots
        slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.className = 'dot';
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(index));
            dotsContainer.appendChild(dot);
        });
        
        const dots = dotsContainer.querySelectorAll('.dot');
        
        // Update slider position
        function updateSlider() {
            track.style.transform = `translateX(-${currentSlide * 100}%)`;
            
            // Update dots
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
            
            // Add animation to content
            const currentContent = slides[currentSlide].querySelector('.nk-content-wrapper');
            currentContent.style.animation = 'none';
            setTimeout(() => {
                currentContent.style.animation = 'slideUpFade 0.8s ease-out';
            }, 10);
        }
        
        // Next slide
        function nextSlide() {
            if (!isPaused) {
                currentSlide = (currentSlide + 1) % slides.length;
                updateSlider();
            }
        }
        
        // Previous slide
        function prevSlide() {
            if (!isPaused) {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                updateSlider();
            }
        }
        
        // Go to specific slide
        function goToSlide(index) {
            if (!isPaused) {
                currentSlide = index;
                updateSlider();
                resetAutoplay();
            }
        }
        
        // Autoplay
        function startAutoplay() {
            slideInterval = setInterval(nextSlide, interval);
        }
        
        function resetAutoplay() {
            clearInterval(slideInterval);
            startAutoplay();
        }
        
        // Event listeners
        nextBtn.addEventListener('click', () => {
            nextSlide();
            resetAutoplay();
        });
        
        prevBtn.addEventListener('click', () => {
            prevSlide();
            resetAutoplay();
        });
        
        // Pause on hover and mouse enter
        slider.addEventListener('mouseenter', () => {
            isPaused = true;
            clearInterval(slideInterval);
        });
        
        slider.addEventListener('mouseleave', () => {
            isPaused = false;
            startAutoplay();
        });
        
        // Touch support
        let startX = 0;
        let endX = 0;
        
        slider.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isPaused = true;
            clearInterval(slideInterval);
        });
        
        slider.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            handleSwipe();
            setTimeout(() => {
                isPaused = false;
                startAutoplay();
            }, 3000);
        });
        
        function handleSwipe() {
            const diff = startX - endX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
            }
        }
        
        // Initialize
        updateSlider();
        startAutoplay();
    });
});