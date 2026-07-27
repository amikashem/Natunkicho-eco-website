// NK Category Bar Show More/Less - Cross Browser Compatible
jQuery(document).ready(function ($) {
  const $items = $(".nk-category-item");
  const $button = $(".nk-show-more");
  const visibleCount = 4; // show 4 initially

  if ($items.length > visibleCount) {
    $items.slice(visibleCount).hide();
    $button.show();
  } else {
    $button.hide();
  }

  $button.on("click", function () {
    const isExpanded = $(this).attr("data-expanded") === "true";

    if (isExpanded) {
      // Show Less
      $items.slice(visibleCount).slideUp(200);
      $(this).text("Show More").attr("data-expanded", "false");
    } else {
      // Show More
      $items.slice(visibleCount).slideDown(200);
      $(this).text("Show Less").attr("data-expanded", "true");
    }
  });
});
