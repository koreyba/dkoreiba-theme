"""Tiny builder for valid core-block markup (the HTML comments + saved HTML that the
block editor expects), used to write patterns/*.php and page content.

Markup must match what the editor itself would save, otherwise the editor shows
"This block contains unexpected or invalid content". Keep attribute order as here.

    from blocks import *
    html = page(section(wrap(split(h2("Title", "is-style-dk-display"), p("Text")), "dk-split-4-8")))
"""
import json


def _attrs(d):
    d = {k: v for k, v in d.items() if v not in (None, False, "")}
    return (" " + json.dumps(d, ensure_ascii=False, separators=(",", ":"))) if d else ""


def _cls(*names):
    names = [n for n in names if n]
    return (" " + " ".join(names)) if names else ""


def p(html, cls=None):
    return f'<!-- wp:paragraph{_attrs({"className": cls})} -->\n<p{" class=%s" % json.dumps(cls) if cls else ""}>{html}</p>\n<!-- /wp:paragraph -->'


def h(html, level=2, cls=None, anchor=None):
    a = {"level": level if level != 2 else None, "className": cls, "anchor": anchor}
    idattr = f' id="{anchor}"' if anchor else ""
    return f'<!-- wp:heading{_attrs(a)} -->\n<h{level}{idattr} class="wp-block-heading{_cls(cls)}">{html}</h{level}>\n<!-- /wp:heading -->'


def h1(html, cls=None):
    return h(html, 1, cls)


def h2(html, cls=None, anchor=None):
    return h(html, 2, cls, anchor)


def h3(html, cls=None):
    return h(html, 3, cls)


def group(*inner, cls=None, anchor=None, align=None):
    a = {"align": align, "className": cls, "layout": {"type": "default"}, "anchor": anchor}
    # attribute order the editor uses: align, className, layout, anchor
    idattr = f' id="{anchor}"' if anchor else ""
    alignc = f"align{align}" if align else None
    return (f'<!-- wp:group{_attrs(a)} -->\n<div{idattr} class="wp-block-group{_cls(alignc, cls)}">'
            + "\n\n".join(inner) + "</div>\n<!-- /wp:group -->")


def li(html, cls=None):
    return f'<!-- wp:list-item{_attrs({"className": cls})} -->\n<li{" class=%s" % json.dumps(cls) if cls else ""}>{html}</li>\n<!-- /wp:list-item -->'


def lst(items, cls=None, ordered=False, start=None):
    """items: str or (html, li_class) tuples."""
    a = {"ordered": ordered or None, "start": start, "className": cls}
    tag = "ol" if ordered else "ul"
    st = f' start="{start}"' if start else ""
    body = "\n\n".join(li(*i) if isinstance(i, tuple) else li(i) for i in items)
    return f'<!-- wp:list{_attrs(a)} -->\n<{tag}{st} class="wp-block-list{_cls(cls)}">{body}</{tag}>\n<!-- /wp:list -->'


def button(text, href, cls=None):
    return (f'<!-- wp:button{_attrs({"className": cls})} -->\n<div class="wp-block-button{_cls(cls)}">'
            f'<a class="wp-block-button__link wp-element-button" href="{href}">{text}</a></div>\n<!-- /wp:button -->')


def buttons(*b):
    return '<!-- wp:buttons -->\n<div class="wp-block-buttons">' + "\n\n".join(b) + "</div>\n<!-- /wp:buttons -->"


def image(src, img_id=None, alt="", cls=None, lightbox=False, size="full"):
    a = {"lightbox": {"enabled": True} if lightbox else None, "id": img_id, "sizeSlug": size,
         "linkDestination": "none", "className": cls}
    idc = f' class="wp-image-{img_id}"' if img_id else ""
    return (f'<!-- wp:image{_attrs(a)} -->\n<figure class="wp-block-image size-{size}{_cls(cls)}">'
            f'<img src="{src}" alt="{alt}"{idc}/></figure>\n<!-- /wp:image -->')


# ---- DK composites ----
def page(*sections_inner):
    """One full-width .dk-page wrapper; every DK section/pattern starts with it."""
    return group(*sections_inner, cls="dk-page", align="full")


def section(*inner, cls=None, anchor=None):
    return group(*inner, cls="dk-section" + (" " + cls if cls else ""), anchor=anchor)


def wrap(*inner, cls=None):
    return group(*inner, cls="dk-wrap" + (" " + cls if cls else ""))


def split(*inner, variant=None, cls=None):
    return group(*inner, cls=" ".join(x for x in ["dk-split", variant and "dk-split-" + variant, cls] if x))


def stack(*inner, size=None, cls=None):
    return group(*inner, cls=" ".join(x for x in ["dk-stack" + ("-" + size if size else ""), cls] if x))


def grid(*inner, cols=2, cls=None):
    return group(*inner, cls=" ".join(x for x in ["dk-grid", f"dk-grid-{cols}", cls] if x))


def pattern_file(title, slug, body, keywords="", description="", inserter=True):
    head = ["<?php", "/**", f" * Title: {title}", f" * Slug: {slug}", " * Categories: dk"]
    if keywords:
        head.append(f" * Keywords: {keywords}")
    head.append(" * Viewport Width: 1400")
    if not inserter:
        head.append(" * Inserter: no")
    if description:
        head.append(f" * Description: {description}")
    return "\n".join(head + [" */", "?>", body.rstrip() + "\n"])
