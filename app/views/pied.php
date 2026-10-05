<script src="../../public/js/common.js"></script>
<script src="../../public/js/views/<?= htmlspecialchars(basename($_SERVER['PHP_SELF'], '.php'), ENT_QUOTES, 'UTF-8') ?>.js"></script>
<script>
      const sidebar = document.querySelector(".sidebar");
      const sidebarBtn = document.querySelector(".sidebarBtn");
      const sidebarCloseBtn = document.querySelector(".sidebar-close");
      const sidebarIcon = sidebarBtn?.querySelector("i");
      const sidebarMobile = window.matchMedia("(max-width: 700px)");
      const updateSidebarIcon = () => {
        const showClose = sidebar && sidebar.classList.contains("active") && sidebarMobile.matches;
        sidebarIcon?.classList.toggle("fa-bars", !showClose);
        sidebarIcon?.classList.toggle("fa-xmark", showClose);
      };
      if (sidebar && sidebarBtn) {
        sidebarBtn.addEventListener("click", () => {
          const expanded = sidebar.classList.toggle("active");
          sidebarBtn.setAttribute("aria-expanded", String(expanded));
          updateSidebarIcon();
        });
        const closeSidebar = () => {
          sidebar.classList.remove("active");
          sidebarBtn.setAttribute("aria-expanded", "false");
          updateSidebarIcon();
        };
        sidebarCloseBtn?.addEventListener("click", closeSidebar);
        document.addEventListener("click", event => {
          if (sidebarMobile.matches && sidebar.classList.contains("active")
              && !sidebar.contains(event.target) && !sidebarBtn.contains(event.target)) {
            closeSidebar();
          }
        });
        sidebarMobile.addEventListener("change", updateSidebarIcon);
        updateSidebarIcon();
      }
    </script>
  </body>
</html>