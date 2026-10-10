GAMI SURGICAL - BLOG ARTICLES
=============================

12 articles. Each folder contains:

  article.html   The article text only (no title, no <html>, <head> or <body>).
  <slug>.jpg     Featured image, 1600 x 900 px.
  seo.txt        Post title, slug, category, tags, excerpt, Yoast SEO fields
                 (focus keyphrase, SEO title, meta description) and the
                 image alt text, title and caption.

SEO-overview.csv lists the SEO details of all 12 articles in one table
(opens in Excel or Google Sheets).


HOW TO PUBLISH ONE ARTICLE (WordPress block editor)
---------------------------------------------------
1. Posts > Add New Post.
2. Title: copy "Post title" from seo.txt.
3. Text: open the editor's menu (three dots, top right) > Code editor,
   paste everything from article.html, then click "Exit code editor".
4. Right-hand panel, Post tab:
   - Categories: tick the category from seo.txt (add it if it is new).
   - Tags: paste the tags.
   - Excerpt: paste the excerpt.
   - Featured image: upload the .jpg from the folder. In the media window
     paste the Alt text and Title from seo.txt (Caption is optional).
5. Yoast SEO box (below the editor or in the Yoast sidebar):
   - Focus keyphrase, SEO title, Slug and Meta description from seo.txt.
   - Keyphrase synonyms (needs Yoast Premium; skip if you do not have it).
6. Publish.

USING ELEMENTOR INSTEAD
-----------------------
Create the post as above (title, category, tags, excerpt, featured image,
Yoast fields), then click "Edit with Elementor". Add a "Text Editor" widget,
switch it to the "Text" (HTML) tab, and paste article.html.
The theme styles the boxes, tables and lists automatically.

NOTES
-----
- Upload the image with its file name unchanged; the name is part of the SEO.
- Articles 01 and 02 replace the two articles published earlier. If those are
  already on your site, just update their text, featured image and SEO fields.
- Article 10 uses the slug "types-of-tweezers". If you already published it as
  "tweezers-guide", you can keep the old slug; then use "types of tweezers" as
  the focus keyphrase anyway.
- Featured images show best with theme version 1.16.2 or later.
