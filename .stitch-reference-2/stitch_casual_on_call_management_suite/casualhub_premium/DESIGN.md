---
name: CasualHub Premium
colors:
  surface: '#f9f9fb'
  surface-dim: '#d9dadc'
  surface-bright: '#f9f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f3f5'
  surface-container: '#eeeef0'
  surface-container-high: '#e8e8ea'
  surface-container-highest: '#e2e2e4'
  on-surface: '#1a1c1d'
  on-surface-variant: '#5c3f3f'
  inverse-surface: '#2f3132'
  inverse-on-surface: '#f0f0f2'
  outline: '#916f6e'
  outline-variant: '#e6bdbc'
  surface-tint: '#bf0030'
  primary: '#b1002c'
  on-primary: '#ffffff'
  primary-container: '#dc143c'
  on-primary-container: '#fff1f0'
  inverse-primary: '#ffb3b3'
  secondary: '#5f5e5e'
  on-secondary: '#ffffff'
  secondary-container: '#e5e2e1'
  on-secondary-container: '#656464'
  tertiary: '#006262'
  on-tertiary: '#ffffff'
  tertiary-container: '#007d7d'
  on-tertiary-container: '#c9fffe'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdad9'
  primary-fixed-dim: '#ffb3b3'
  on-primary-fixed: '#40000a'
  on-primary-fixed-variant: '#920022'
  secondary-fixed: '#e5e2e1'
  secondary-fixed-dim: '#c8c6c5'
  on-secondary-fixed: '#1c1b1b'
  on-secondary-fixed-variant: '#474646'
  tertiary-fixed: '#95f2f1'
  tertiary-fixed-dim: '#78d6d5'
  on-tertiary-fixed: '#002020'
  on-tertiary-fixed-variant: '#004f4f'
  background: '#f9f9fb'
  on-background: '#1a1c1d'
  surface-variant: '#e2e2e4'
typography:
  display-lg:
    fontFamily: Montserrat
    fontSize: 64px
    fontWeight: '700'
    lineHeight: 72px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Montserrat
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Montserrat
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Montserrat
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.03em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  container-max-width: 1280px
  gutter: 32px
  margin-x: 40px
  section-gap: 120px
  card-padding: 48px
---

## Brand & Style
The design system transitions into a premium, lifestyle-oriented aesthetic that balances the energy of its heritage colors with the sophistication of modern high-end digital products. The personality is confident, clean, and spacious, evoking the editorial feel of a luxury magazine combined with the functional precision of top-tier SaaS platforms.

The style is a blend of **Modern Minimalism** and **Tactile Depth**. It prioritizes high-quality whitespace to let content breathe, utilizes oversized components for easy interaction, and employs subtle lighting effects to create a sense of physical layering without clutter.

## Colors
The palette is anchored by a high-contrast relationship between the primary Crimson and the deep Secondary Black. 

- **Primary (#DC143C):** Used sparingly for call-to-action elements, critical states, and brand signatures to maintain its impact.
- **Secondary (#121212):** Used for primary text and grounding elements like top navigation backgrounds or dark-mode surfaces.
- **Neutral Surface (#F5F5F7):** A soft, off-white grey inspired by premium hardware finishes, used for page backgrounds to reduce eye strain and provide a sophisticated backdrop for white cards.
- **Pure White (#FFFFFF):** Reserved for elevated card surfaces and input backgrounds to create clear visual separation.

## Typography
This design system employs a strict typographic hierarchy. **Montserrat** is used for all headlines and display text to provide a bold, geometric character. **Inter** is used for all body copy, labels, and UI elements to ensure maximum legibility and a systematic, modern feel.

Large display sizes use negative letter spacing to feel more cohesive and "locked-in," while smaller labels use slight tracking to improve readability on digital screens.

## Layout & Spacing
The layout follows a **Fixed-Width Center-Aligned** model for desktop, transitioning to a fluid model for mobile.

- **Top Navigation:** The primary navigation is a persistent top bar (80px height) with a blurred background (20px blur) to maintain context. No sidebars are used; secondary navigation is handled via sub-menus or tabs within the page content.
- **Spacing Rhythm:** An 8px linear scale is used. However, section transitions use generous vertical spacing (up to 120px) to establish a premium, "un-crowded" atmosphere.
- **Grid:** A 12-column grid is used for desktop. Cards should prioritize spanning 4, 6, or 12 columns to maintain a sense of "oversized" scale.

## Elevation & Depth
Depth is communicated through **Ambient Shadows** and **Tonal Layering**. 

Surfaces utilize a "Soft Lift" effect. Shadows are never pure black; they use a highly diluted version of the secondary color with a large blur radius (30px to 60px) and low opacity (5-8%). This creates a natural, soft appearance that mimics physical objects resting on a soft surface. 

Interactive elements like cards should subtly scale (e.g., 1.02x) on hover to reinforce the tactile nature of the UI.

## Shapes
The shape language is defined by large, friendly radii. Buttons and high-level containers use significant rounding to appear approachable and modern. 

- **Standard Elements:** 0.5rem (8px) for inputs and small buttons.
- **Large Components:** 1rem (16px) for cards and primary CTAs.
- **Oversized Cards:** 1.5rem (24px) for hero sections or featured content blocks.

## Components
- **Buttons:** Primary buttons are oversized (minimum height 56px), fully rounded (pill-shaped), using the primary red with white text. Secondary buttons use a white background with a subtle 1px border (#E5E5E7).
- **Cards:** Cards are the primary container. They feature white backgrounds, soft ambient shadows, and 24px corner radii. Internal padding is generous (48px) to keep content from feeling cramped.
- **Inputs:** Text fields use a light grey background (#F2F2F7) with a 0.5rem radius. On focus, the border transitions to the primary color with a soft glow effect.
- **Top Navigation:** The bar is transparent with a `backdrop-filter: blur(20px)` and a thin bottom border (#E5E5E7). Links use Inter Semi-bold and are spaced 32px apart.
- **Chips/Badges:** Small, fully rounded elements with low-opacity fills of the primary or secondary colors for non-intrusive categorization.