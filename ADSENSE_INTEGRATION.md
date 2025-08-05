# Google AdSense Integration

## Ad Placements

The blog has these ad locations:
- Homepage banner (between posts and categories)
- In-content ads in blog posts
- Footer ads in blog posts
- Categories page banner
- Tags page banner

## Integration Steps

1. Get AdSense account at adsense.google.com
2. Create ad units for each placement
3. Replace placeholder HTML with AdSense code
4. Test on live site

## Example Replacement

Replace placeholder:
```html
<div class="ad-banner" id="homepage-banner-ad">
    <div class="ad-placeholder">
        <!-- placeholder content -->
    </div>
</div>
```

With AdSense code:
```html
<div class="ad-banner" id="homepage-banner-ad">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-YOUR_ID"></script>
    <ins class="adsbygoogle" data-ad-client="ca-pub-YOUR_ID" data-ad-slot="YOUR_SLOT"></ins>
    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>
```

## Best Practices

- Use responsive ads
- Don't place too many ads per page
- Test on mobile devices
- Monitor page performance 