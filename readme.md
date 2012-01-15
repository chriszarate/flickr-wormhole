# Flickr Wormhole

### The gist

1. You have private photos on [Flickr](http://www.flickr.com) organized into collections and photosets.
2. You want to let certain people see them, but they don’t have Flickr accounts / don’t know what Flickr is / don’t care what Flickr is / are unclear on the concept / just want you to “put your pictures on the Internet.”

### Features / buzzwords

- Lists all collections and photosets
- Easily adjustable template
- Friendly URLs (created from photoset name)
- Conditional get
- API caching
- HTML 5

### Requirements

- [Flickr API](http://www.flickr.com/services/api/) key, API signature, and access token
- PHP 5
- Apache with mod_rewrite
- Ability to grant PHP write access to one directory

### Installation

**[Download “Flickr Wormhole”](https://github.com/chriszarate/FlickrWormhole)** at Github.

1. Grant PHP write access to the `cache` subdirectory.
2. Review `.htaccess`. It may be necessary to make changes.
3. Edit `flickr_config.php` and provide your Flickr API values.

If it wasn’t clear already, this script allows you to circumvent the privacy settings you set for your own photos. If you have private photosets that you would like to remain private, you should restrict access to this script.

### License

This is free software. It is released to the public domain without warranty.
