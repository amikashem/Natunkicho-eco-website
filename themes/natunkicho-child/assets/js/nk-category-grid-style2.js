document.addEventListener("DOMContentLoaded", function () {
  const categoryGrid = document.getElementById("categoryGridStyle2");
  const showMoreBtn = document.getElementById("showMoreBtnStyle2");
  if (!categoryGrid || !showMoreBtn) return;

  const items = Array.from(categoryGrid.querySelectorAll(".category-item"));
  const initialVisible = 4;

  // Function to shuffle array randomly
  function shuffleArray(array) {
    return array
      .map(value => ({ value, sort: Math.random() }))
      .sort((a, b) => a.sort - b.sort)
      .map(({ value }) => value);
  }

  // Initial setup: show only first 4
  items.forEach((item, index) => {
    item.style.display = index < initialVisible ? "block" : "none";
  });

  // Handle Show More / Less button
  showMoreBtn.addEventListener("click", () => {
    const hiddenItems = items.filter(item => item.style.display === "none");

    if (hiddenItems.length > 0) {
      // Shuffle and show all
      const randomized = shuffleArray(hiddenItems);
      randomized.forEach(item => (item.style.display = "block"));

      // Change grid layout to 6 columns
      categoryGrid.classList.add("expanded");

      showMoreBtn.textContent = "Show Less Categories";
    } else {
      // Hide all except 4
      items.forEach((item, index) => {
        item.style.display = index < initialVisible ? "block" : "none";
      });

      // Reset grid layout back to 4 columns
      categoryGrid.classList.remove("expanded");

      showMoreBtn.textContent = "Show More Categories";
    }
  });
});
