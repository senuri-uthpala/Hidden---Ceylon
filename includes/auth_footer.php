  </div>
</main>
<script>
  // show / hide password
  document.querySelectorAll('.field .eye').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = btn.parentElement.querySelector('input');
      var show  = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.querySelector('i').className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });
</script>
</body>
</html>
