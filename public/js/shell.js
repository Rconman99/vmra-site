/* =========================================================
   VMRA SHARED SHELL · global mobile-menu behavior
   This file is the ONLY home for shared nav JS.
   Do not duplicate this IIFE in per-page <script> blocks.
   ========================================================= */
(function(){
  var toggle = document.getElementById('navToggle');
  var menu = document.getElementById('mobile-menu');
  if (!toggle || !menu) return;

  function closeMenu(){
    toggle.setAttribute('aria-expanded', 'false');
    menu.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }
  function openMenu(){
    toggle.setAttribute('aria-expanded', 'true');
    menu.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  toggle.addEventListener('click', function(){
    toggle.getAttribute('aria-expanded') === 'true' ? closeMenu() : openMenu();
  });
  menu.addEventListener('click', function(e){
    var t = e.target.closest('.mm-link, .mm-secondary');
    if (t) closeMenu();
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeMenu();
  });
})();
