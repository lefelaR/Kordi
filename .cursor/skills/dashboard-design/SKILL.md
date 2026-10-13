---
name: dashboard-design
description: >-
  Design and change the Kordicraft dashboard using the Shadcnblocks dashboard
  layouts as the visual reference. Use when creating or editing the Kordicraft
  dashboard, its sidebar, overview, Events, Vendors, Enquiries, Venues,
  Documents, or Contacts screens, or when the user mentions dashboard design
  or shadcnblocks.com/blocks/dashboard.
---

# Kordicraft dashboard design

Use the layouts at https://www.shadcnblocks.com/blocks/dashboard as the reference for the Kordicraft dashboard. Open that catalog, then open the closest block below, and match its regions. Adapt the structure to this PHP app. Do not add React, Tailwind, or shadcn/ui, and do not paste Shadcnblocks source into the repo.

## Shell

Keep the existing dashboard layout:

- Top bar from `app/Views/includes/dashboard-nav.php`
- Left sidebar from `app/Views/includes/dashboard-side-nav.php`, with Events, Vendors, Enquiries, Venues, Documents, and Contacts
- Main column in `app/Views/layouts/dashboard.php`

Match the reference shells: a persistent sidebar, a main column on a muted background (`#fafafa`), and white cards with a 1px `#eee` border, a small radius, and even padding. The active sidebar item uses a neutral fill, not a new accent color.

## Page structure

1. Page header, left aligned: a Cormorant Garamond title and one Montserrat description. Put actions on the right of that row when a screen has them.
2. A first row of summary or action cards.
3. The working area under that: a table, list, or a wide card beside a narrower activity card.
4. On a narrow screen, stack the sidebar above the main column and collapse card grids to one column.

## Which block to open

| Kordicraft screen | Reference block |
| --- | --- |
| Overview | [Dashboard 10](https://www.shadcnblocks.com/block/dashboard10) for multi-page navigation, and [Dashboard 1](https://www.shadcnblocks.com/block/dashboard1) for the metric row |
| Events, Enquiries | [Dashboard 18](https://www.shadcnblocks.com/block/dashboard18), the hotel operations board: status strip, quick actions, then a table |
| Vendors, Venues, Contacts | [Dashboard 28](https://www.shadcnblocks.com/block/dashboard28), the customer directory: metrics, filters, then a roster |
| Documents | [Dashboard 30](https://www.shadcnblocks.com/block/dashboard30), the project home with notes and files |

The catalog groups are Orders, Revenue, Customers, Projects, and Payments. Use Orders for enquiries and events, Customers for contacts and vendors, and Projects for documents. Skip Revenue and Payments unless the screen is actually about money.

## Keep the site

Use the public site's type and color: Montserrat for interface text, Cormorant Garamond for titles, `#111` text, white cards, `#fafafa` canvas, `#eee` borders. Do not restyle the public marketing pages to look like an admin product.

Show an empty state when a list has no records. Do not invent counts, charts, or sample people to fill a reference layout.
