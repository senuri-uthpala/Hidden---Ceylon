    </main>
  </div>
</div>
<script>
(function () {
  var body = document.body;
  document.getElementById('menuBtn').addEventListener('click', function () { body.classList.toggle('nav-open'); });
  document.getElementById('scrim').addEventListener('click', function () { body.classList.remove('nav-open'); });

  var menu = document.getElementById('userMenu'), btn = document.getElementById('userBtn');
  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    btn.setAttribute('aria-expanded', menu.classList.toggle('open') ? 'true' : 'false');
  });
  document.addEventListener('click', function (e) {
    if (!menu.contains(e.target)) { menu.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { menu.classList.remove('open'); body.classList.remove('nav-open'); btn.setAttribute('aria-expanded', 'false'); }
  });
})();
</script>
</body>
</html>
