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

The dashboard template is `app/Views/layouts/dashboard.php`. Its only chrome is the top navigation from `app/Views/includes/dashboard-nav.php`. Do not add a sidebar. Page content is rendered into the template's main area.

Cards and the canvas use the shadcn default theme tokens.

## Which block to open

| Kordicraft screen | Reference block |
| --- | --- |
| Overview | [Dashboard 10](https://www.shadcnblocks.com/block/dashboard10) for multi-page navigation, and [Dashboard 1](https://www.shadcnblocks.com/block/dashboard1) for the metric row |
| Events, Enquiries | [Dashboard 18](https://www.shadcnblocks.com/block/dashboard18), the hotel operations board: status strip, quick actions, then a table |
| Vendors, Venues, Contacts | [Dashboard 28](https://www.shadcnblocks.com/block/dashboard28), the customer directory: metrics, filters, then a roster |
| Documents | [Dashboard 30](https://www.shadcnblocks.com/block/dashboard30), the project home with notes and files |

The catalog groups are Orders, Revenue, Customers, Projects, and Payments. Use Orders for enquiries and events, Customers for contacts and vendors, and Projects for documents. Skip Revenue and Payments unless the screen is actually about money.

## Keep the site

Take color, type, radius, and buttons from the shadcn-default-theme skill. Do not restyle the public marketing pages to look like an admin product.

Show an empty state when a list has no records. Do not invent counts, charts, or sample people to fill a reference layout.
