// --- Carousel Logic ---
let currentSlide = 0;
const wrapper = document.getElementById('carouselWrapper');
// We check if wrapper exists to avoid errors on pages without the carousel
if (wrapper) {
    const totalSlides = document.querySelectorAll('.hero-slide').length;

    function moveSlide(direction) {
        if (totalSlides === 0) return;
        currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
        wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
    }

    // Auto-slide every 5 seconds
    setInterval(() => {
        moveSlide(1);
    }, 5000);
}

// --- Load More Logic ---
// We attach this to the window object so the inline onclick="" in HTML can find it
window.loadMoreGames = function() {
    // Find all games that are currently hidden
    const hiddenGames = document.querySelectorAll('.hidden-game');
    
    // Reveal the next 16 games
    for(let i = 0; i < 16 && i < hiddenGames.length; i++) {
        hiddenGames[i].classList.remove('hidden-game');
    }
    
    // If there are no more hidden games, hide the Load More button entirely
    if(document.querySelectorAll('.hidden-game').length === 0) {
        const loadBtn = document.getElementById('loadMoreBtn');
        if (loadBtn) loadBtn.style.display = 'none';
    }
};
