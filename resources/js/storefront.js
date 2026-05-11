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
  init()
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot, { once: true })
} else {
  boot()
}
