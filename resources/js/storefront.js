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
  const mount = (wrap) => {
    const track = wrap.querySelector('.reviews-track')
    if (!track || wrap.dataset.reviewsSliderMounted === '1') return

    let cards = [...track.querySelectorAll(':scope > .review')]
    if (cards.length < 2) return

    const ensureScrollable = () => {
      if (track.scrollWidth > track.clientWidth + 8) return
      if (wrap.dataset.reviewsDuplicated === '1') return
      wrap.dataset.reviewsDuplicated = '1'
      cards.forEach((card) => {
        const clone = card.cloneNode(true)
        clone.setAttribute('aria-hidden', 'true')
        clone.querySelectorAll('a').forEach((a) => a.setAttribute('tabindex', '-1'))
        track.appendChild(clone)
      })
      cards = [...track.querySelectorAll(':scope > .review')]
    }

    ensureScrollable()
    if (track.scrollWidth <= track.clientWidth + 8) return

    wrap.dataset.reviewsSliderMounted = '1'

    const section = wrap.closest('.reviews-section') ?? wrap
    let timerId = null
    let idx = 0
    let inView = false
    let paused = false

    const behavior = () => (prefersReducedMotion() ? 'auto' : 'smooth')

    const scrollToCard = (card) => {
      const t = track.getBoundingClientRect()
      const c = card.getBoundingClientRect()
      const nextLeft = track.scrollLeft + (c.left - t.left) - 4
      const max = Math.max(0, track.scrollWidth - track.clientWidth)
      track.scrollTo({
        left: Math.min(max, Math.max(0, nextLeft)),
        behavior: behavior(),
      })
    }

    const goToIndex = (i) => {
      const card = cards[i]
      if (!card) return
      scrollToCard(card)
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

    const setInViewFromRect = () => {
      const r = section.getBoundingClientRect()
      return r.bottom > 0 && r.top < window.innerHeight
    }

    const syncInView = () => {
      inView = setInViewFromRect()
      if (inView && !paused) start()
      else stop()
    }

    const io = new IntersectionObserver(
      ([entry]) => {
        inView = Boolean(entry?.isIntersecting)
        if (inView && !paused) start()
        else stop()
      },
      { threshold: 0.08, rootMargin: '0px 0px 12% 0px' },
    )
    io.observe(section)

    window.setTimeout(() => {
      if (!timerId && !paused && setInViewFromRect()) {
        inView = true
        start()
      }
    }, 800)

    let resizeT = null
    const onResize = () => {
      window.clearTimeout(resizeT)
      resizeT = window.setTimeout(() => {
        ensureScrollable()
        syncInView()
      }, 150)
    }
    window.addEventListener('resize', onResize, { passive: true })

    const handlePointerEnter = () => {
      paused = true
      stop()
    }
    const handlePointerLeave = () => {
      paused = false
      inView = setInViewFromRect()
      if (inView) start()
    }
    wrap.addEventListener('pointerenter', handlePointerEnter)
    wrap.addEventListener('pointerleave', handlePointerLeave)

    wrap.addEventListener('focusin', handlePointerEnter)
    wrap.addEventListener('focusout', (e) => {
      if (!wrap.contains(e.relatedTarget)) {
        paused = false
        inView = setInViewFromRect()
        if (inView) start()
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
  }

  const run = () => {
    document.querySelectorAll('[data-reviews-slider]').forEach((wrap) => {
      mount(wrap)
    })
  }

  run()
  window.requestAnimationFrame(() => window.requestAnimationFrame(run))
  window.addEventListener('load', run, { once: true })
}

const initMicroInteractions = () => {
  const bindScaleHover = (selector, hoverScale = 1.03) => {
    document.querySelectorAll(selector).forEach((el) => {
      const scaleTo = gsap.quickTo(el, 'scale', { duration: 0.32, ease: 'power2.out' })

      const onEnter = () => scaleTo(hoverScale)
      const onLeave = () => scaleTo(1)
      const onDown = () => scaleTo(0.97)
      const onUp = () => scaleTo(el.matches(':hover') ? hoverScale : 1)

      el.addEventListener('mouseenter', onEnter)
      el.addEventListener('mouseleave', onLeave)
      el.addEventListener('mousedown', onDown)
      el.addEventListener('mouseup', onUp)
    })
  }

  bindScaleHover('.join-btn, .hero-cta, .view-more, .cart-link-btn', 1.03)
  bindScaleHover('.add-btn', 1.06)

  document.querySelectorAll('.nav-links a').forEach((link) => {
    const yTo = gsap.quickTo(link, 'y', { duration: 0.22, ease: 'power2.out' })
    link.addEventListener('mouseenter', () => yTo(-2))
    link.addEventListener('mouseleave', () => yTo(0))
  })

  document.querySelectorAll('.product-card-wrap').forEach((wrap) => {
    const card = wrap.querySelector(':scope > a.product')
    if (!card) return

    const hoverTween = gsap.to(card, {
      y: -6,
      scale: 1.02,
      duration: 0.38,
      ease: 'power2.out',
      paused: true,
    })

    wrap.addEventListener('mouseenter', () => hoverTween.play())
    wrap.addEventListener('mouseleave', () => hoverTween.reverse())
  })

  document.querySelectorAll('.foot-cols a, .socials a').forEach((link) => {
    const xTo = gsap.quickTo(link, 'x', { duration: 0.25, ease: 'power2.out' })
    link.addEventListener('mouseenter', () => xTo(4))
    link.addEventListener('mouseleave', () => xTo(0))
  })

  document.querySelectorAll('.cat').forEach((card) => {
    const hoverTween = gsap.to(card, {
      y: -5,
      scale: 1.015,
      duration: 0.35,
      ease: 'power2.out',
      paused: true,
    })
    card.addEventListener('mouseenter', () => hoverTween.play())
    card.addEventListener('mouseleave', () => hoverTween.reverse())
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
  initMicroInteractions()

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
