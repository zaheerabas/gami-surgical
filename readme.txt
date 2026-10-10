=== Gami Surgical ===
Contributors: zaheerabbas
Requires at least: 6.2
Tested up to: 6.6
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress theme for Gami Surgical: surgical, dental and beauty instruments, made in Sialkot
and shipped worldwide. Built for WooCommerce, Elementor and Yoast SEO.

== Installation ==

1. Appearance > Themes > Add New > Upload Theme, choose gami-surgical.zip, Install, Activate.
2. Install the recommended plugins from the notice that appears: WooCommerce,
   Elementor and Yoast SEO.
3. Settings > Permalinks: choose "Post name" and Save.
4. Products > Categories: add Surgical instruments, Dental instruments, Beauty instruments.
5. Appearance > Customize > Gami Surgical: set the sales email (quote requests go here),
   phone, WhatsApp number, certifications line and homepage text.
6. Appearance > Menus: assign menus to Primary, Footer and Legal links.
7. Settings > Reading: create an empty page called Home and set it as the homepage.
   It shows just the header and footer until you add content.

== WooCommerce: inquiry catalogue ==

Gami Surgical sells by inquiry, so WooCommerce is used as a product catalogue:
* No prices (Customize > Gami Surgical > Inquiries > Show prices to turn on),
  no add to cart, cart, checkout, customer accounts or registration.
* The Cart, Checkout and My account pages redirect to the catalogue and are
  hidden from menus and the Yoast sitemap. Leave these pages in place:
  WooCommerce expects them to exist.
* Every product has "Send inquiry" and "Add to inquiry list". "Send inquiry"
  opens the Inquiry page (created automatically; choose another page in
  Customize > Gami Surgical > Contact details > Inquiry page). Visitors can
  collect several products, choose a size (from the product's Sizes field,
  comma separated) and quantity for each, and send one inquiry. Text written
  on the Inquiry page itself shows above the list. The page is not indexed
  by search engines.
* Each inquiry is emailed to the sales email (Customize > Contact details)
  and saved under Dashboard > Inquiries, with a "Reply by email" button.
  The buyer gets a confirmation copy (can be turned off).
* Shared hosting often drops PHP email: install an SMTP plugin such as
  WP Mail SMTP. Inquiries are saved in the dashboard either way.
* The header button (with no custom link) goes to the Inquiry page and
  shows how many products are on the list. Shortcode [gami_inquiry] puts the
  list and form on any page.
* The Shop page is renamed "Products" on activation; reviews are off unless
  switched on (Customize > Gami Surgical > Inquiries).

Each product:
* SKU (Inventory tab) is the catalogue number, shown above the name in grids.
  Site search matches SKUs.
* Specifications tab in the product editor: sizes, material, finish, usage,
  packing, minimum order. Shown as a Specifications tab on the product page and
  added to WooCommerce's Product schema (material, sizes, finish, usage, brand, MPN).
* A quote form under the product, pre-filled with the product name and SKU.
* Use white or light grey product photos; the theme blends them onto its
  light grey image panels.

== Product page ==

* Sizes: the Sizes field (Specifications tab, comma separated) becomes size
  buttons. Minimum order: a number in that field (e.g. "50 pieces per size")
  sets the starting quantity and the +/- step.
* Key specs: Material, Finish, Usage and Minimum order.
* Feature strip: Customize > Gami Surgical > Inquiries > points (one per line).
* Recently viewed is kept in each visitor's browser only.

== Shop page ==

Customize > Gami Surgical > Shop page: default view (grid or list), products
per row (2, 3 or 4), "Show" choices (default 8, 12, 24, 48) and the default,
whether visitors can change these, and the category cards.

* Category cards and the sidebar list your top-level categories (Surgical
  instruments, Dental instruments, Beauty instruments) with their
  sub-categories. Order: Products > Categories (drag to sort). Images: each
  category's Thumbnail.
* List view shows the product's short description and its Material, Finish
  and Usage from the Specifications tab.
* Extra sidebar widgets: Appearance > Widgets > Shop sidebar.

Logo: the Gami Surgical logo is bundled and used automatically. To change it,
Appearance > Customize > Site Identity > Logo. The header is dark so the white
wordmark shows; upload a light-on-transparent version if you replace it.

== Instrument catalogue (without WooCommerce) ==

Instruments (left admin menu) is a dedicated catalogue:
* Categories: General surgery, Dental, Orthopaedic, Beauty and so on, nested if needed.
* Specifications box on each instrument: catalogue no., sizes, material, finish,
  usage, packing, minimum order. These show as a spec table and feed Yoast schema.
* Featured image is the product photo. White or light grey backgrounds look best.
* Use the Order field (Page Attributes) to control sort order.
* Site search also matches catalogue numbers.

URLs: /instruments/ (catalogue), /instruments/category/dental/ (category),
/instrument/kelly-hemostat/ (single).

== Quote requests ==

* Every instrument page has a quote form pre-filled with that instrument.
* Shortcode for any page: [gami_quote_form], [gami_quote_form title="Get prices"]
  or [gami_quote_form product="123"] to pre-fill a product.
* Elementor widget: Gami Surgical > Quote form.
* Emails go to the Customizer sales email (falls back to the site admin email).
* Install an SMTP plugin (for example WP Mail SMTP) so mail from shared hosting
  actually arrives.
* Developers: hook into do_action( 'gami_quote_received', $data ) to save leads or
  forward to a CRM.

== Elementor ==

* Any page edited with Elementor renders full width with the theme header and footer.
* Page templates: "Full width (no title)" and "Contained (no title)".
* Elementor's own "Elementor Canvas" and "Elementor Full Width" templates also work.
* Widgets under the "Gami Surgical" category: Product grid (Instrument grid without
  WooCommerce; can show featured products only), Quote form, Spec drawing.
* Elementor Pro's own WooCommerce widgets and Theme Builder product templates also work.
* Elementor Pro Theme Builder: header, footer, single and archive locations are
  registered, so Theme Builder templates replace the theme's versions automatically.
* Pages, posts and products are enabled for Elementor on activation.

== Yoast SEO ==

* Breadcrumbs: enable at Yoast SEO > Settings > Advanced > Breadcrumbs. The theme
  shows Yoast's trail when enabled and its own trail otherwise.
* Product schema: each instrument adds a Product node (name, SKU from catalogue no.,
  material, category, sizes, finish, image, brand, manufacturer) to Yoast's graph.
  Set Yoast > Site representation to "Organization" for the manufacturer link.
* Product and instrument pages are typed ItemPage; shop and category pages CollectionPage.
* Yoast SEO's free version handles titles, meta descriptions and sitemaps for products.
  WooCommerce prints its own Product schema, which the theme enriches.
* Instrument photos are included in the XML sitemap.
* Spec fields are included in Yoast's content analysis on the edit screen.
* Without an SEO plugin the theme prints a basic meta description.

== Customizer ==

Appearance > Customize > Gami Surgical:

Brand colour: accent colour (Gami gold by default), certifications line (footer,
hidden while empty; only list certifications you hold).

Top bar: show or hide, text on the left, phone, email and social icons on/off,
background and text colour.

Header: two-row or one-row desktop layout, mobile breakpoint, menu bar
background, phone and hours in the menu bar, contained or full width, sticky on/off, slimmer when scrolled,
Call/WhatsApp/Email buttons in the mobile menu, menu position (next to logo,
centre, right), logo height for desktop and mobile, header height, background,
menu colour, hover colour, bottom line colour and thickness (0 hides it),
button on/off with its own text and link, search icon, account and cart icons.
The logo itself: Customize > Site Identity > Logo (the Gami logo is used until
you upload one).

Footer: contained or full width, 0 to 4 columns, footer logo (or reuse the
header logo), company description, social icons, column headings, background,
text, heading, link and hover colours, bottom bar on/off and its background,
copyright text ({year} and {site} are filled in), back-to-top button,
floating WhatsApp button
on/off, its text and side.

Footer columns show this until you add widgets to them:
  1: logo, description, certifications, social icons
  2: product categories
  3: contact details
  4: the menu assigned to "Footer menu" (Appearance > Menus)
Add any widget to Footer column 1 to 4 (Appearance > Widgets) to replace it.

Social links: Facebook, Instagram, LinkedIn, YouTube, X, TikTok. Only filled-in
networks show.

Contact details: phone, sales email (receives quote requests), WhatsApp,
address, hours, quote page, note under the quote form.

Inquiries: button text, inquiry list on/off, show prices, points under the
product buttons, form title and texts, confirmation email to the buyer.

== Mega menu ==

Automatic (on by default, Customize > Gami Surgical > Mega menu):
* Add a product category (e.g. Surgical instruments) to the menu as a
  top-level item: its sub-categories open as a mega menu. If they have their
  own sub-categories you get columns, otherwise image tiles.
* Add a top-level item (e.g. Products) with product categories under it: it
  becomes a mega menu and each category lists its sub-categories.
* Empty categories show too. Order follows Products > Categories (drag to sort).
  Category images come from each category's Thumbnail.

Manual:
Appearance > Menus > open a top-level item (the Gami Surgical box):
* Show as mega menu: its sub-items become columns. Second-level items are
  column headings, third-level items are the links below them.
* Columns: 2 to 5.
* Fill with product categories automatically: no sub-items needed. Top-level
  product categories become columns (with their category image and product
  count), sub-categories become links, plus a quote panel (text in Customize >
  Gami Surgical > Mega menu). Order follows Products > Categories (drag to sort).
* Badge: a small label such as New on any item.
* Descriptions: Screen Options (top right) > Description; shown under links
  inside mega menus.
Until you create a menu, the default menu already shows Products as an
automatic category mega menu.

== Search ==

Customize > Gami Surgical > Search: bar, icon or off; placeholder; live
results on/off; category dropdown; number of live results; include pages;
popular searches. Live search finds product names, descriptions and catalogue
numbers (SKU, including variation SKUs); an exact catalogue number comes first.
Recent searches are kept only in each visitor's own browser. Press "/" to jump
to the search box.

== Homepage ==

The theme adds no homepage sections. The homepage shows the header, then the
content of the page set at Settings > Reading > Homepage (empty for now), then
the footer. Build it later with Elementor or the block editor and it appears
full width between the header and footer.

The Elementor widgets (Product grid, Quote form, Spec drawing) and the
[gami_quote_form] shortcode are still available for when you build it.

== Widget areas ==

Blog sidebar, Catalogue sidebar, Shop sidebar, Footer columns 1 to 4 (each footer column shows
sensible defaults until you add widgets).

== Developer filters ==

gami_instrument_fields   Add, remove or rename spec fields.
gami_instrument_schema   Change the Product schema array (instruments).
gami_product_schema      Change WooCommerce's Product schema array.
gami_use_instruments     Force the Instruments post type on or off.
gami_process_steps       Change the homepage manufacturing steps.
gami_content_width       Content width for embeds.

== Credits ==

Instrument Sans by Rodrigo Fuenzalida and Jordan Egstad, SIL Open Font License,
loaded from Google Fonts.

== Changelog ==

= 1.16.1 =
* Theme updates show up more reliably: the update is offered as soon as a
  new GitHub release exists (no waiting for WordPress's twice-daily check),
  also on servers that cannot reach wordpress.org, and with a fallback when
  GitHub's API limit is reached on shared hosting.
* Appearance > Themes shows the installed version, the latest version on
  GitHub and when it was checked, with a "Check now" link.
* Release notes ("View version details") come from this changelog.

= 1.16.0 =
* Blog page in sections, each with its own layout: banner with article
  search and counts, Latest (lead story + list), Browse by topic (tiles),
  a row per category (alternating cards and list), question banner, and
  All articles (dated list with pagination).
* Article page: sidebar with a pinned "On this page" contents list that
  follows your reading, About box with quote and WhatsApp links, topics
  and latest articles; reading progress bar; author box; copy-link button.
* Article typography: headings with link anchors, gold-underlined links
  (outside links marked), styled bullet and numbered lists, tables with a
  navy header that scroll on phones, quotes, code, captions.
* Customizer > Gami Surgical > Blog and articles: switch each section on or
  off and edit the banner and question texts.

= 1.15.0 =
* Blog: the theme creates the "Blog" page when it is installed or updated
  (an existing page with the slug "blog" is reused, not duplicated).
* New blog design: banner, category links, card grid with a featured first
  post, article page with reading time, share links, quote box and related
  articles. Styles in assets/css/blog.css.
* Posts > Upload Articles: upload article .html files to create or update
  posts, which can then be edited in the block editor or with Elementor.

= 1.14.0 =
* One-click theme updates from GitHub Releases: when a newer release is
  published, Appearance > Themes shows the normal "Update now" button.
* Theme version is read from style.css only (no second copy in functions.php).

= 1.13.1 =
* Contact details: info@gamisurgical.us (also receives inquiries),
  +1 347-727-3384 (USA) and +92 317 8614535 (Pakistan); WhatsApp
  +92 317 8614535. New fields: second phone and a label for each phone,
  shown in the top bar, footer and Inquiry page.

= 1.13.0 =
* Footer redesigned: call-to-action card on the footer's top edge ("Looking
  for a specific instrument?" + Send inquiry and WhatsApp); highlights row;
  brand column with logo, about, contact details with icons, certifications
  and social icons; one column per main product category (sub-categories +
  "View all"); Company column (Footer menu); bottom bar with copyright, Legal
  menu and Back to top. Columns fold into tap-to-open sections on phones.
* New footer settings: call-to-action on/off, title and text; highlights on/off;
  category columns on/off and links per column; Company column on/off.
* Widget areas: Footer column 1 = brand column, 2 = replaces the category
  columns, 3 = replaces the Company column, 4 = an extra column.

= 1.12.2 =
* Site width is now a setting: Customize > Gami Surgical > Brand colour >
  Site width (px). Default 1400px; applies to every page, header and footer.

= 1.12.1 =
* Product photos: vertical thumbnail strip on desktop (below on tablets and
  phones), previous/next arrows and a "1 / 5" counter inside the photo,
  edge-to-edge photo with swipe on phones.
* The photo frame takes each photo's own background colour (read from the
  photo's edges), so there is no visible box around the photo. Fallback
  colour: Customize > Gami Surgical > Shop page > Product photo background.
* Full screen always opens the photo being shown.

= 1.12.0 =
* Site width: content up to 1600px (was 1240px).
* Product page top section: highlights row (from the "points"), description
  fallback (start of the full description with "Read full description" when
  there is no short description), "Request a quote" panel around size,
  quantity and buttons with a 3-step "how it works" line, compact WhatsApp
  row, categories and share on one line, softer gallery background.

= 1.11.2 =
* Product photo: no hover zoom or hover effect (switch to turn hover zoom back
  on: Customize > Gami Surgical > Shop page). Clicking the photo or the
  full-screen button opens it full screen.

= 1.11.1 =
* Fix: on desktop the sticky product gallery slid down over the tabs and
  related products (a sticky element stays inside its parent, which was the
  whole product area). Gallery and summary now have their own wrapper
  (woocommerce/content-single-product.php, same WooCommerce hooks).
* Sizes also come from a WooCommerce "Size" attribute when the Sizes field is
  empty (e.g. variable products): product page, inquiry page, product cards.
* The floating WhatsApp button is hidden on product pages (they have their
  own WhatsApp card), so it no longer covers the buttons.

= 1.11.0 =
* Product page redesigned: large gallery (sticky on desktop) with full-screen
  view; category and catalogue number with copy button; key specs grid; size
  chips and quantity stepper (steps by the minimum order) that go into the
  inquiry; Send inquiry / Add to inquiry list; WhatsApp card; share buttons
  (WhatsApp, LinkedIn, email, copy link).
* Tabs restyled, with a sticky inquiry card beside them showing the chosen
  size and quantity; feature strip from the "points"; related instruments
  (swipe row on phones); recently viewed products.
* Inquiry list: the same product in different sizes is now a separate line.

= 1.10.2 =
* Fix: on phones the page could scroll sideways after scrolling down. The
  condensing header used a CSS transform, which made the hidden slide-in menu
  widen the page. It now slides via the sticky "top" position instead, and the
  page itself can no longer scroll sideways (overflow-x: clip).

= 1.10.1 =
* Phones: product photos are square in the 1-per-row view.
* Phones: scrolling down slides the header up so only the search bar stays
  at the top; scrolling up brings the full header back (Customize > Gami
  Surgical > Header > "Phones: show only the search bar while scrolling down").

= 1.10.0 =
* Phones: Filter button moved into the shop toolbar; layout switch for 1 per
  row, 2 per row or list (remembered).
* Dropdowns restyled site-wide: custom arrow, rounded corners, hover and
  focus states.
* Animations: content fades up as it scrolls into view (product cards
  staggered), hover lift and image zoom on cards, animated menu underline,
  mega menu and mobile menu items slide in, button and icon micro-effects,
  WhatsApp button entrance. Switch: Customize > Gami Surgical > Brand colour >
  Animations. Visitors whose device asks for reduced motion see none.

= 1.9.0 =
* Category pages: sub-categories as one swipeable row of photo cards (image,
  name, product count) with arrow buttons and mouse drag on desktop; the
  current sub-category is highlighted and scrolled into view.
* Mega menu: sub-categories shown with photos (Customize > Gami Surgical >
  Mega menu > Show sub-categories with photos).
* Category photos: a category without its own Thumbnail automatically uses a
  photo of one of its products (switch: Customize > Gami Surgical > Shop
  page). A Thumbnail set in Products > Categories always wins.

= 1.8.0 =
* Mega menu: when sub-items have no deeper levels (e.g. Dental Instruments >
  Elevators, Scalers…), they show as a clean grid of links with category
  thumbnails and product counts, under an "All Dental Instruments · N
  products →" heading row, instead of empty column headings.
* Pagination: Previous / Next with arrows, fewer page numbers, current page
  highlighted, "Page 2 of 39" below; compact on phones.
* Breadcrumbs: chevron separators (also replaces Yoast's "»"), Products
  level added in Yoast breadcrumbs on category and product pages, current
  page in bold; one scrolling line on phones.
* Sorting: "Sort by" label and clearer options: Recommended, Most popular,
  Newest first, Name A–Z / Z–A, Catalogue no.

= 1.7.1 =
* WooCommerce's default category now shows when it has been renamed (for
  example to "Surgical Instruments"); only a category still called
  "Uncategorized" is hidden.
* Shop sidebar: search card removed (search is in the header); product
  category widgets in the Shop sidebar are skipped because the Categories
  card already lists every category. Other widgets still show.

= 1.7.0 =
* Shop page: Grid / List view, 2 / 3 / 4 products per row, "Show" 8 / 12 /
  24 / 48 per page (all remembered, defaults in Customize > Gami Surgical >
  Shop page). List view shows a short description and key specs.
* Category cards for the parent categories (Surgical, Dental, Beauty) at
  the top of the shop page; sub-category quick links on category pages.
* New sidebar: product search, categories as expandable sections with
  counts (current one opens automatically), the visitor's inquiry list, and a
  "Can't find an instrument?" card. Opens as a drawer on phones.

= 1.6.0 =
* Inquiries have their own page ("Send an inquiry", created automatically,
  works like a cart page): product list with size and quantity steppers,
  remove with undo, the form, "What happens next" and contact details.
  Works without JavaScript; the thank-you screen has its own address
  (?sent=GS-123) for conversion tracking. Shortcode [gami_inquiry].
* Header button shows the inquiry count; sticky inquiry bar on product
  pages on phones.
* Fixes: header squeezed to 760px on search results pages; stray menu
  button on WooCommerce pages; card button alignment; empty count badge
  showing "0"; price sorting and "Sale!" badges without prices; reviews
  tab off by default; "In stock" hidden; SKU shown as "Catalogue no.";
  breadcrumbs include the catalogue; helpful "nothing found" pages with an
  inquiry button; floating WhatsApp no longer covers content on product
  and inquiry pages; spam check now uses a signed time token.

= 1.5.0 =
* Inquiry catalogue instead of a shop: "Send inquiry" and "Add to inquiry
  list" replace add to cart; inquiry form with sizes and quantities per
  product; inquiries emailed and saved under Dashboard > Inquiries; optional
  confirmation email to the buyer; spam protection.
* Removed cart, checkout, customer accounts and registration (pages redirect
  to the catalogue and are hidden from menus and the sitemap). Prices hidden
  unless switched on.
* Mega menu: product category menu items show their sub-categories
  automatically (columns or image tiles); empty categories are shown too.

= 1.4.0 =
* Desktop always shows the full menu; the menu button appears only below the
  mobile breakpoint (Customizer, default 992px). Items that don't fit move
  into a "More" dropdown instead of wrapping.
* New two-row desktop header: logo, search bar, account, cart and quote
  button, then the menu bar. One-row layout still available.
* Mega menus: any top-level item, or filled automatically from product
  categories with images, counts, sub-categories and a quote panel.
  Menu item badges (e.g. New) and descriptions.
* Advanced search: live results while typing, category filter, catalogue
  number (SKU) matching with exact matches first, matching categories and
  pages, keyboard navigation, highlighted matches, recent and popular
  searches, "Request a quote" when nothing is found, "/" shortcut.

= 1.3.0 =
* Fully responsive header: the menu switches to a slide-in drawer whenever the
  links don't fit (any menu length, any screen), not at one fixed width.
* Mobile drawer: overlay, close button, expandable sub-menus, quote button,
  Call / WhatsApp / Email buttons, keyboard focus kept inside, Escape closes.
* Desktop dropdowns with chevrons, animation and keyboard support.
* Sticky header gets slimmer with a shadow after scrolling (Customizer option).
* Top bar: icons, one line on phones.
* Footer: icon contact rows; columns become accordions on phones; back-to-top.
* Shop on phones and tablets: Search & filter drawer and swipeable category
  chips, so products show straight away. Tighter product cards on small phones.
* Product page on phones: full-width buttons, side-scrolling tabs.

= 1.2.0 =
* Homepage no longer adds its own sections: header, page content, footer.
* Fully customizable top bar, header and footer (layout, colours, sizes,
  button, icons, columns, social links, copyright, WhatsApp button).
* Header search icon. Four footer widget columns.

= 1.1.0 =
* WooCommerce support: product catalogue, three sales modes, quote buttons and
  forms on products, Specifications tab, header cart and account, shop sidebar,
  styled cart, checkout and account pages, enriched Product schema.
* Rebranded to the Gami Surgical logo: navy header, gold accent, bundled logo.
* Removed placeholder certification claims from the defaults.

= 1.0.0 =
* Initial release.
