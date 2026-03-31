jQuery(document).ready(function ($) {

  // ---------------- Toggle sidebar ----------------
  $('#sidebar-toggle').on('click', function () {
    $('#platform-sidebar').toggleClass('collapsed');

    // Store collapse state in localStorage
    localStorage.setItem('sidebar-collapsed', $('#platform-sidebar').hasClass('collapsed'));
  });

  // ---------------- Load saved sidebar state ----------------
  if (localStorage.getItem('sidebar-collapsed') === 'true') {
    $('#platform-sidebar').addClass('collapsed');
  }

  // ---------------- Highlight current app ----------------
  const params = new URLSearchParams(window.location.search);
  const currentApp = params.get('app');
  if (currentApp) {
    $('.app-card').removeClass('active');
    $('#wpac-platform-' + currentApp).addClass('active');
  }
});
