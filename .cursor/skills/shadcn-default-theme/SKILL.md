---
name: shadcn-default-theme
description: >-
  Style this project with the Shadcn default theme: zinc neutrals, Inter, and
  a 10px radius. Use when adding or changing UI, CSS, colors, type, buttons,
  cards, or the dashboard, or when the user mentions the default theme or
  shadcnblocks.com/theme/default.
---

# Shadcn default theme

Use the [Shadcn default theme](https://www.shadcnblocks.com/theme/default) for visual styling in this project. It is the classic shadcn/ui palette: neutral zinc tokens, high contrast, and calm surfaces.

This app is PHP and plain CSS. Do not install React, Tailwind, or shadcn/ui, and do not run `npx shadcn add`. Put the tokens below in `assets/css/index.css` and use the variables. Layout structure for the dashboard still comes from the dashboard-design skill.

## Type

Load Inter for UI text. Use `ui-serif` only for a rare editorial line, and `ui-monospace` for code.

```css
--font-sans: Inter, ui-sans-serif, system-ui, sans-serif;
--font-serif: ui-serif, Georgia, serif;
--font-mono: ui-monospace, SFMono-Regular, Menlo, monospace;
```

## Light tokens

The site stays in light mode. Do not add a dark theme unless the user asks. Radius is `0.625rem`. Values are the shadcn zinc preset.

```css
--background: oklch(1 0 0);
--foreground: oklch(0.141 0.005 285.823);
--card: oklch(1 0 0);
--card-foreground: oklch(0.141 0.005 285.823);
--popover: oklch(1 0 0);
--popover-foreground: oklch(0.141 0.005 285.823);
--primary: oklch(0.21 0.006 285.885);
--primary-foreground: oklch(0.985 0 0);
--secondary: oklch(0.967 0.001 286.375);
--secondary-foreground: oklch(0.21 0.006 285.885);
--muted: oklch(0.967 0.001 286.375);
--muted-foreground: oklch(0.552 0.016 285.938);
--accent: oklch(0.967 0.001 286.375);
--accent-foreground: oklch(0.21 0.006 285.885);
--destructive: oklch(0.577 0.245 27.325);
--border: oklch(0.92 0.004 286.32);
--input: oklch(0.92 0.004 286.32);
--ring: oklch(0.705 0.015 286.067);
--chart-1: oklch(0.871 0.006 286.286);
--chart-2: oklch(0.552 0.016 285.938);
--chart-3: oklch(0.442 0.017 285.786);
--chart-4: oklch(0.37 0.013 285.805);
--chart-5: oklch(0.274 0.006 286.033);
--radius: 0.625rem;
--sidebar: oklch(0.985 0 0);
--sidebar-foreground: oklch(0.141 0.005 285.823);
--sidebar-primary: oklch(0.21 0.006 285.885);
--sidebar-primary-foreground: oklch(0.985 0 0);
--sidebar-accent: oklch(0.967 0.001 286.375);
--sidebar-accent-foreground: oklch(0.21 0.006 285.885);
--sidebar-border: oklch(0.92 0.004 286.32);
--sidebar-ring: oklch(0.705 0.015 286.067);
```

## How to apply them

- Page canvas: `--background`. Body text: `--foreground`. Supporting text: `--muted-foreground`.
- Cards and panels: `--card` on `--background`, border `--border`, radius `--radius`.
- Sidebar: `--sidebar`, border `--sidebar-border`. The current item uses `--sidebar-accent` and `--sidebar-accent-foreground`.
- Primary button: `--primary` background and `--primary-foreground` text. Secondary button: `--secondary` and `--secondary-foreground`. Outline: transparent background and `--border`. Ghost: no border, `--accent` on hover. Destructive: `--destructive` background and `--primary-foreground` text.
- Inputs: background `--background`, border `--input`, focus ring `--ring`.
- Charts, in order: `--chart-1` through `--chart-5`.

When you change a screen, replace one-off hex colors and Montserrat or Cormorant on that screen with these tokens. Leave untouched pages alone.
