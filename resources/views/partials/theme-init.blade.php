<script>
(function () {
  var KEY = 'besek-theme'
  try {
    var stored = localStorage.getItem(KEY)
    var theme =
      stored === 'dark' || stored === 'light' ? stored : 'light'
    document.documentElement.setAttribute('data-theme', theme)
  } catch (e) {
    document.documentElement.setAttribute('data-theme', 'light')
  }
})()
</script>
