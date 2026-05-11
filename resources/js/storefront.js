import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

const prefersReducedMotion = () =>
  window.matchMedia('(prefers-reduced-motion: reduce)').matches

const initScrollReveal = () => {
  const main = document.querySelector('main#main-content')
  if (!main) return

  const shell = main.querySelector(':scope > .page-shell')
  const sectionEls = shell
    ? [...shell.querySelectorAll(':scope > section')]
    : [...main.querySelectorAll(':scope > section:not(.hero)')]
  const blocks = gsap.utils.toArray([
    ...sectionEls,
    ...main.querySelectorAll(':scope > footer'),
  ])

  blocks.forEach((el) => {
    if (el.querySelector(':scope .grid-4, :scope .cat-grid')) {
      return
    }
    gsap.from(el, {
      opacity: 0,
      y: 28,
      duration: 0.7,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: el,
        start: 'top 90%',
        once: true,
      },
    })
  })

  const grids = main.querySelectorAll('.grid-4, .cat-grid')
  grids.forEach((grid) => {
    const items = grid.querySelectorAll(
      ':scope > a.product, :scope > .product-card-wrap, :scope > .wishlist-cell, :scope > a.cat',
    )
    if (!items.length) return
    gsap.from(items, {
      opacity: 0,
      y: 20,
      duration: 0.45,
      stagger: 0.055,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: grid,
        start: 'top 92%',
        once: true,
      },
    })
  })
}

const initHero = () => {
  const bits = document.querySelectorAll('.hero .hero-content > *')
  if (!bits.length) return
  gsap.from(bits, {
    opacity: 0,
    y: 26,
    duration: 0.78,
    stagger: 0.1,
    ease: 'power3.out',
    delay: 0.05,
  })
}

const initNav = () => {
  const nav = document.querySelector('.site-header .navbar')
  if (!nav) return
  gsap.from(nav, {
    opacity: 0,
    y: -12,
    duration: 0.48,
    ease: 'power2.out',
  })
}

const initMegaBrandFill = () => {
  if (prefersReducedMotion()) return

  const root = document.querySelector('[data-mega-brand]')
  if (!root) return
  const fills = root.querySelectorAll('.mega-brand__fill')
  if (!fills.length) return

  gsap.registerPlugin(ScrollTrigger)

  const fromColor = '#d8dad2'
  const fromAccent = '#cfd2c9'

  fills.forEach((el) => {
    const isAccent = el.classList.contains('mega-brand__fill--accent')
    gsap.set(el, { color: isAccent ? fromAccent : fromColor })
  })

  const tl = gsap.timeline({
    scrollTrigger: {
      trigger: root,
      start: 'top 90%',
      once: true,
    },
  })

  fills.forEach((el, i) => {
    const isAccent = el.classList.contains('mega-brand__fill--accent')
    const toColor = isAccent ? '#3d5248' : '#2c4a3a'
    tl.fromTo(
      el,
      { color: isAccent ? fromAccent : fromColor },
      {
        color: toColor,
        duration: 1.05,
        ease: 'power2.out',
        clearProps: 'willChange',
      },
      i * 0.14,
    )
  })
}

const initReviewsAutoSlider = () => {
  document.querySelectorAll('[data-reviews-slider]').forEach((wrap) => {
    const track = wrap.querySelector('.reviews-track')
    if (!track) return
    const cards = track.querySelectorAll(':scope > .review')
    if (cards.length < 2) return
    if (track.scrollWidth <= track.clientWidth + 12) return

    const section = wrap.closest('.reviews-section') ?? wrap
    let timerId = null
    let idx = 0
    let inView = false
    let paused = false

    const behavior = () => (prefersReducedMotion() ? 'auto' : 'smooth')

    const goToIndex = (i) => {
      const card = cards[i]
      if (!card) return
      card.scrollIntoView({
        block: 'nearest',
        inline: 'start',
        behavior: behavior(),
      })
    }

    const step = () => {
      idx = (idx + 1) % cards.length
      goToIndex(idx)
    }

    const start = () => {
      if (timerId || paused || !inView) return
      timerId = window.setInterval(step, 5200)
    }

    const stop = () => {
      if (!timerId) return
      window.clearInterval(timerId)
      timerId = null
    }

    const syncIndexFromScroll = () => {
      const sl = track.scrollLeft
      let best = 0
      let bestDist = Infinity
      cards.forEach((c, i) => {
        const d = Math.abs(c.offsetLeft - sl)
        if (d < bestDist) {
          bestDist = d
          best = i
        }
      })
      idx = best
    }

    track.addEventListener(
      'scroll',
      () => {
        window.requestAnimationFrame(syncIndexFromScroll)
      },
      { passive: true },
    )

    const io = new IntersectionObserver(
      ([entry]) => {
        inView = Boolean(entry?.isIntersecting)
        if (inView && !paused) start()
        else stop()
      },
      { threshold: 0.12 },
    )
    io.observe(section)

    const handlePointerEnter = () => {
      paused = true
      stop()
    }
    const handlePointerLeave = () => {
      paused = false
      if (inView) start()
    }
    wrap.addEventListener('pointerenter', handlePointerEnter)
    wrap.addEventListener('pointerleave', handlePointerLeave)

    wrap.addEventListener('focusin', handlePointerEnter)
    wrap.addEventListener('focusout', (e) => {
      if (!wrap.contains(e.relatedTarget)) {
        handlePointerLeave()
      }
    })

    const handleVisibility = () => {
      if (document.visibilityState === 'hidden') {
        stop()
        return
      }
      if (inView && !paused) start()
    }
    document.addEventListener('visibilitychange', handleVisibility)
  })
}

const initGalleryNav = () => {
  const behavior = prefersReducedMotion() ? 'auto' : 'smooth'
  document.querySelectorAll('.gallery').forEach((root) => {
    const track = root.querySelector('[data-gallery-track]')
    const prev = root.querySelector('[data-gallery-prev]')
    const next = root.querySelector('[data-gallery-next]')
    if (!track || !prev || !next) return

    const scrollAmount = () =>
      Math.min(280, Math.max(160, Math.round(track.clientWidth * 0.45)))

    prev.addEventListener('click', () => {
      track.scrollBy({ left: -scrollAmount(), behavior })
    })
    next.addEventListener('click', () => {
      track.scrollBy({ left: scrollAmount(), behavior })
    })
  })
}

const init = () => {
  if (prefersReducedMotion()) return

  gsap.registerPlugin(ScrollTrigger)

  initNav()
  initHero()
  initScrollReveal()

  document.fonts?.ready?.then(() => {
    ScrollTrigger.refresh()
  })
}

const boot = () => {
  initGalleryNav()
  initReviewsAutoSlider()
  initMegaBrandFill()
  init()
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot, { once: true })
} else {
  boot()
}
