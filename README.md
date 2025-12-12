# WordPress Must-Use Plugin Autoloader

Autoloader for some of my WordPress must-use (mu) plugins for development and production environments.

## What's Included

### Autoloader
- `00-autoloader.php` - Automatically loads mu-plugins organized in subdirectories
- Allows mu-plugins to be structured like regular plugins
- Looks for a PHP file matching the directory name (e.g., `my-plugin/my-plugin.php`)

### Plugins
Check out the subfolders for details on individual plugins.

## Installation

1. Clone this repo into its own location and use the included `symlink.sh` script to symlink individual plugins to your local dev site
2. OR clone/download this repository to your `wp-content/mu-plugins/` directory if you want them all

## Usage Notes

Just a few notes regarding special requirements for certain plugins.

### Cheapo SMTP
To get this to work, add these constants to your `wp-config.php`:

```php
define( 'SMTP_USER',  'your_username' );
define( 'SMTP_TOKEN', 'your_token_or_password' );
define( 'SMTP_HOST',  'your_host' );
define( 'SMTP_PORT',  587 );
define( 'SMTP_FROM',  'your_from_email' );
define( 'SMTP_NAME',  'your_from_name' );
```

## License

GPLv2 or later
