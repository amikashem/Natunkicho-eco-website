<?php
/**
 * Template part: Dynamic Category Grid (4 columns, transparent background)
 */
?>

<section class="category-section">
  <h2 class="category-title">Explore Post Categories</h2>

  <div class="category-grid" id="categoryGrid">
    <?php
    $categories = get_categories(array(
      'orderby' => 'name',
      'order'   => 'ASC'
    ));

    if (!empty($categories)) {
      foreach ($categories as $category) {
        $category_link = get_category_link($category->term_id);
        echo '<div class="category-item"><a href="' . esc_url($category_link) . '">' . esc_html($category->name) . '</a></div>';
      }
    } else {
      echo '<p>No categories found.</p>';
    }
    ?>
  </div>

  <button id="showMoreBtn" class="show-more-btn">Show More Categories</button>
</section>

<style>
/* ===== Transparent Section ===== */
.category-section {
  text-align: center;
  padding: 40px 0;
  background: transparent; /* No background */
}

/* ===== Title ===== */
.category-title {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 40px;
  color: #222;
  text-transform: capitalize;
}

/* ===== Grid (4 Columns Fixed) ===== */
.category-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  max-width: 1100px;
  margin: 0 auto;
}

/* ===== Category Item ===== */
.category-item a {
  display: block;
  background: #fff;
  border-radius: 10px;
  padding: 20px 15px;
  font-size: 18px;
  color: #333;
  font-weight: 500;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  text-decoration: none;
}
.category-item a:hover {
  background: #0073e6;
  color: #fff;
  transform: translateY(-3px);
}

/* ===== Show More Button ===== */
.show-more-btn {
  margin-top: 40px;
  background: #0073e6;
  color: #fff;
  border: none;
  padding: 14px 32px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease;
}
.show-more-btn:hover {
  background: #005bb5;
}

/* ===== Responsive Layout ===== */
@media (max-width: 991px) {
  .category-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 600px) {
  .category-grid {
    grid-template-columns: repeat(1, 1fr);
  }
  .category-item a {
    font-size: 16px;
    padding: 16px 12px;
  }
  .category-title {
    font-size: 24px;
  }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const categoryGrid = document.getElementById("categoryGrid");
  const showMoreBtn = document.getElementById("showMoreBtn");
  const items = categoryGrid.querySelectorAll(".category-item");
  const initialVisible = 4; // show first 4 items

  // Hide all except first row (4 items)
  items.forEach((item, index) => {
    if (index >= initialVisible) item.style.display = "none";
  });

  showMoreBtn.addEventListener("click", () => {
    const hiddenItems = [...items].filter(item => item.style.display === "none");

    if (hiddenItems.length > 0) {
      hiddenItems.forEach(item => (item.style.display = "block"));
      showMoreBtn.textContent = "Show Less";
    } else {
      items.forEach((item, index) => {
        if (index >= initialVisible) item.style.display = "none";
      });
      showMoreBtn.textContent = "Show More Categories";
    }
  });
});
</script>
