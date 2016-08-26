---
layout: post
title: Svg icons
img: svg-logo-v.svg
img_alt: SVG logo
img_height: 150px
---

Recently we've replaced old iconic font with cool and shiny svg icons. If you want you can always add your own custom icons to svg sprite. Just use some sprite generator like this one [svg-sprite-generator](https://github.com/frexy/svg-sprite-generator). Also you need to remove xml declaration at the top of svg sprite file, cause php will not render your page in that case.
<img src="{{ site.baseurl }}/images/{{ page.img }}" height="{{ page.img_height }}" alt="{{ page.img_alt }}" />
