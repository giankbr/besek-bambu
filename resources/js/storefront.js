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
    if (
      el.hasAttribute('data-collage-section') ||
      el.hasAttribute('data-gallery-slider') ||
      el.querySelector(':scope .grid-4, :scope .cat-grid')
    ) {
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

const initMobileNav = () => {
  const toggles = [...document.querySelectorAll('[data-nav-mobile-toggle]')]
  const panel = document.querySelector('[data-nav-mobile-panel]')
  if (!toggles.length || !panel) return

  const isOpen = () => toggles[0].getAttribute('aria-expanded') === 'true'

  const setOpen = (open) => {
    toggles.forEach((toggle) => {
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false')
    })
    panel.classList.toggle('is-open', open)
    if (open) {
      panel.removeAttribute('hidden')
      document.body.classList.add('nav-mobile-open')
    } else {
      panel.setAttribute('hidden', '')
      document.body.classList.remove('nav-mobile-open')
    }
  }

  toggles.forEach((toggle) => {
    toggle.addEventListener('click', () => setOpen(!isOpen()))
  })

  panel.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => setOpen(false))
  })

  document.addEventListener('click', (e) => {
    if (!isOpen()) return
    if (toggles.some((toggle) => toggle.contains(e.target)) || panel.contains(e.target)) {
      return
    }
    setOpen(false)
  })

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return
    if (isOpen()) setOpen(false)
    document.querySelectorAll('details[data-nav-lang][open]').forEach((d) => {
      d.removeAttribute('open')
    })
  })

  document.addEventListener('click', (e) => {
    document.querySelectorAll('details[data-nav-lang][open]').forEach((d) => {
      if (!d.contains(e.target)) d.removeAttribute('open')
    })
  })
}

const initCollageSection = () => {
  const wrap = document.querySelector('[data-collage-section]')
  if (!wrap) return

  const left = wrap.querySelector('.c-side:not(.right)')
  const center = wrap.querySelector('.c-main')
  const right = wrap.querySelector('.c-side.right')
  const commitment = wrap.querySelector('[data-collage-commitment]')
  const inlineImgs = wrap.querySelectorAll('.commitment .inline-img')

  const tl = gsap.timeline({
    scrollTrigger: {
      trigger: wrap,
      start: 'top 85%',
      once: true,
    },
  })

  if (left) {
    tl.from(left, {
      x: -56,
      rotation: -10,
      opacity: 0,
      duration: 0.8,
      ease: 'power3.out',
    })
  }

  if (center) {
    tl.from(
      center,
      {
        scale: 0.86,
        y: 36,
        opacity: 0,
        duration: 0.9,
        ease: 'power3.out',
      },
      left ? '-=0.58' : 0,
    )
  }

  if (right) {
    tl.from(
      right,
      {
        x: 56,
        rotation: 10,
        opacity: 0,
        duration: 0.8,
        ease: 'power3.out',
      },
      '-=0.68',
    )
  }

  if (commitment) {
    tl.from(
      commitment,
      {
        y: 28,
        opacity: 0,
        duration: 0.75,
        ease: 'power2.out',
      },
      '-=0.4',
    )
  }

  if (inlineImgs.length) {
    tl.from(
      inlineImgs,
      {
        scale: 0.5,
        opacity: 0,
        duration: 0.45,
        stagger: 0.1,
        ease: 'back.out(1.6)',
      },
      '-=0.5',
    )
  }

  ;[left, center, right].filter(Boolean).forEach((img) => {
    img.addEventListener('mouseenter', () => {
      gsap.to(img, {
        y: -6,
        scale: 1.03,
        duration: 0.35,
        ease: 'power2.out',
        overwrite: 'auto',
      })
    })
    img.addEventListener('mouseleave', () => {
      gsap.to(img, {
        y: 0,
        scale: 1,
        duration: 0.4,
        ease: 'power2.out',
        overwrite: 'auto',
      })
    })
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
      timerId = window.setInterval(step, 3200)
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

  bindScaleHover('.join-btn, .view-more, .cart-link-btn', 1.03)
  bindScaleHover('.hero-cta:not(.cart-summary__cta)', 1.03)
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

const initGallerySlider = () => {
  const canHoverPause = () =>
    window.matchMedia('(hover: hover) and (pointer: fine)').matches

  const mount = (root) => {
    const track = root.querySelector('[data-gallery-track]')
    const prev = root.querySelector('[data-gallery-prev]')
    const next = root.querySelector('[data-gallery-next]')
    if (!track || !prev || !next) return
    if (root.dataset.gallerySliderMounted === '1') return

    const cards = [...track.querySelectorAll(':scope > .gallery-card')]
    if (cards.length === 0) return

    const isScrollable = () => track.scrollWidth > track.clientWidth + 8
    const maxScroll = () => Math.max(0, track.scrollWidth - track.clientWidth)
    const canAutoPlay = cards.length > 1

    const scrollToCard = (card, instant = false) => {
      const trackRect = track.getBoundingClientRect()
      const cardRect = card.getBoundingClientRect()
      const targetLeft = track.scrollLeft + (cardRect.left - trackRect.left)
      track.scrollTo({
        left: Math.min(maxScroll(), Math.max(0, targetLeft)),
        behavior: instant || prefersReducedMotion() ? 'auto' : 'smooth',
      })
    }

    let idx = 0
    let timerId = null
    let paused = false
    let inView = false

    const syncIndexFromScroll = () => {
      const sl = track.scrollLeft
      let best = 0
      let bestDist = Infinity
      cards.forEach((card, i) => {
        const dist = Math.abs(card.offsetLeft - sl)
        if (dist < bestDist) {
          bestDist = dist
          best = i
        }
      })
      idx = best
    }

    const goToIndex = (i, instant = false) => {
      const card = cards[i]
      if (!card) return
      scrollToCard(card, instant)
    }

    const step = () => {
      if (!isScrollable()) return
      idx = (idx + 1) % cards.length
      goToIndex(idx, true)
    }

    const start = () => {
      if (!canAutoPlay || !isScrollable() || timerId || paused || !inView) return
      step()
      timerId = window.setInterval(step, 2800)
    }

    const stop = () => {
      if (!timerId) return
      window.clearInterval(timerId)
      timerId = null
    }

    const restart = () => {
      stop()
      if (canAutoPlay && inView && !paused) start()
    }

    prev.addEventListener('click', () => {
      idx = idx <= 0 ? cards.length - 1 : idx - 1
      goToIndex(idx)
      restart()
    })

    next.addEventListener('click', () => {
      idx = (idx + 1) % cards.length
      goToIndex(idx)
      restart()
    })

    track.addEventListener(
      'scroll',
      () => {
        window.requestAnimationFrame(syncIndexFromScroll)
      },
      { passive: true },
    )

    const setInViewFromRect = () => {
      const r = track.getBoundingClientRect()
      return r.bottom > 0 && r.top < window.innerHeight
    }

    const syncPlayback = () => {
      if (!canAutoPlay) return
      if (!isScrollable()) {
        stop()
        return
      }
      inView = setInViewFromRect()
      if (inView && !paused) start()
      else stop()
    }

    root.__gallerySyncPlayback = syncPlayback

    if (canAutoPlay) {
      const io = new IntersectionObserver(
        ([entry]) => {
          inView = Boolean(entry?.isIntersecting)
          if (inView && !paused) start()
          else stop()
        },
        { threshold: 0.01, rootMargin: '40px 0px 40px 0px' },
      )
      io.observe(track)

      let resizeT = null
      window.addEventListener(
        'resize',
        () => {
          window.clearTimeout(resizeT)
          resizeT = window.setTimeout(syncPlayback, 150)
        },
        { passive: true },
      )

      const pause = () => {
        paused = true
        stop()
      }
      const resume = () => {
        paused = false
        syncPlayback()
      }

      if (canHoverPause()) {
        root.addEventListener('mouseenter', pause)
        root.addEventListener('mouseleave', resume)
      }

      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
          stop()
          return
        }
        if (inView && !paused) start()
      })
    }

    root.dataset.gallerySliderMounted = '1'
    syncPlayback()
    window.setTimeout(syncPlayback, 400)
    window.setTimeout(syncPlayback, 1500)
  }

  const run = () => {
    document.querySelectorAll('[data-gallery-slider]').forEach((root) => {
      if (root.dataset.gallerySliderMounted === '1') {
        root.__gallerySyncPlayback?.()
        return
      }
      mount(root)
    })
  }

  run()
  window.requestAnimationFrame(() => window.requestAnimationFrame(run))
  window.addEventListener('load', run, { once: true })
}

const init = () => {
  if (prefersReducedMotion()) return

  gsap.registerPlugin(ScrollTrigger)

  initNav()
  initHero()
  initScrollReveal()
  initCollageSection()
  initMicroInteractions()

  document.fonts?.ready?.then(() => {
    ScrollTrigger.refresh()
  })
}

const initConfirmDialog = () => {
  const root = document.getElementById('sf-confirm')
  if (!root) return

  const titleEl = root.querySelector('[data-sf-confirm-title]')
  const messageEl = root.querySelector('[data-sf-confirm-message]')
  const cancelBtn = root.querySelector('[data-sf-confirm-cancel]')
  const okBtn = root.querySelector('[data-sf-confirm-ok]')
  const backdrop = root.querySelector('[data-sf-confirm-backdrop]')

  let pendingForm = null

  const close = () => {
    root.hidden = true
    document.body.classList.remove('sf-confirm-open')
    pendingForm = null
  }

  const open = (form) => {
    pendingForm = form
    titleEl.textContent = form.dataset.confirmTitle || 'Konfirmasi'
    messageEl.textContent = form.dataset.confirm || 'Lanjutkan tindakan ini?'
    okBtn.textContent = form.dataset.confirmOk || 'Ya, lanjutkan'
    root.hidden = false
    document.body.classList.add('sf-confirm-open')
    cancelBtn.focus()
  }

  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (form.dataset.confirmBypass === '1') {
        form.dataset.confirmBypass = ''
        return
      }

      event.preventDefault()
      open(form)
    })
  })

  const handleConfirm = () => {
    if (!pendingForm) return

    const form = pendingForm
    close()
    form.dataset.confirmBypass = '1'
    form.requestSubmit()
  }

  cancelBtn?.addEventListener('click', close)
  backdrop?.addEventListener('click', close)
  okBtn?.addEventListener('click', handleConfirm)

  root.addEventListener('keydown', (event) => {
    if (root.hidden) return

    if (event.key === 'Escape') {
      event.preventDefault()
      close()
    }
  })
}

const initPasswordToggles = () => {
  document.querySelectorAll('.auth-password__toggle').forEach((button) => {
    if (button.dataset.bound === '1') return
    button.dataset.bound = '1'

    const wrap = button.closest('.auth-password')
    const input = wrap?.querySelector('input')
    if (!input) return

    const iconShow = button.querySelector('.auth-password__icon--show')
    const iconHide = button.querySelector('.auth-password__icon--hide')
    const labelShow = button.dataset.labelShow ?? 'Show password'
    const labelHide = button.dataset.labelHide ?? 'Hide password'

    const setVisible = (visible) => {
      input.type = visible ? 'text' : 'password'
      button.setAttribute('aria-pressed', visible ? 'true' : 'false')
      button.setAttribute('aria-label', visible ? labelHide : labelShow)
      iconShow?.toggleAttribute('hidden', visible)
      iconHide?.toggleAttribute('hidden', !visible)
    }

    button.addEventListener('click', () => {
      setVisible(input.type === 'password')
    })
  })
}

const boot = () => {
  initMobileNav()
  initConfirmDialog()
  initGallerySlider()
  initReviewsAutoSlider()
  initMegaBrandFill()
  initPasswordToggles()
  init()
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot, { once: true })
} else {
  boot()
}
