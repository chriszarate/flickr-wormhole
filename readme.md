# Flickr Wormhole

### The gist

1. You have private photos on [Flickr][flickr] organized into collections and
   photosets.
2. You want to let certain people see them, but they don’t have Flickr accounts
   / don’t know what Flickr is / don’t care what Flickr is / are unclear on the
   concept / just want you to “put your pictures on the Internet.”

### Features / buzzwords

- Lists all collections and photosets
- Easily adjustable template
- Friendly URLs (created from photoset name)
- Conditional get
- API caching
- HTML 5

### Requirements

- [Flickr API][flickr-api] key, API signature, and access token
- PHP 5
- Apache with mod_rewrite
- Ability to grant PHP write access to one directory

### Installation

1. [Download][download] or clone this repo.
2. Grant PHP write access to the `cache` subdirectory.
3. Review `.htaccess`. It may be necessary to make changes.
4. Copy `flickr_config_sample.php` to `flickr_config.php` and edit as needed.

If it wasn’t clear already, this script allows you to circumvent the privacy
settings you set for your own photos. If you have private photosets that you
would like to remain private, you should restrict access to this script by some
means (e.g., simple password protection).

### License

This is free software. It is released to the public domain without warranty.

[flickr]: http://www.flickr.com
[flickr-api]: http://www.flickr.com/services/api/
[download]: https://github.com/chriszarate/flickr-wormhole/archive/master.zip
