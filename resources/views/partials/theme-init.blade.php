<script>
(function () {
  var KEY = 'besek-theme'
  try {
    var stored = localStorage.getItem(KEY)
    var theme = stored === 'dark' || stored === 'light'
      ? stored
      : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
    document.documentElement.setAttribute('data-theme', theme)
    var isDark = theme === 'dark'
    document.querySelectorAll('meta[name="theme-color"]').forEach(function (meta) {
      var scheme = meta.getAttribute('media') || ''
      if (scheme.indexOf('dark') !== -1) {
        if (isDark) meta.setAttribute('content', '#09090b')
      } else if (scheme.indexOf('light') !== -1) {
        if (!isDark) meta.setAttribute('content', '#ffffff')
      } else {
        meta.setAttribute('content', isDark ? '#09090b' : '#ffffff')
      }
    })
  } catch (e) {
    document.documentElement.setAttribute('data-theme', 'light')
  }
})()
</script>
