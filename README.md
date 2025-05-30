# Auto-Expire Posts

A WordPress plugin that automatically changes posts to draft status when they reach their expiration date.

## Features

- Set expiration date and time for any post
- Posts automatically change to draft status when they expire
- Option to delete posts instead of changing to draft
- Simple and intuitive interface in the post editor

## Installation

1. Upload the `auto-expire-posts` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Edit any post to set an expiration date

## Usage

1. Edit any post
2. Look for the "Post Expiration" meta box in the sidebar
3. Set an expiration date and time
4. Choose whether to set the post to draft or delete it when it expires
5. When the expiration date is reached, the action will be performed automatically

## Requirements

- WordPress 5.0 or higher

## Running Tests

1. Install the development dependencies:

```bash
composer install
```

2. Run the test suite with:

```bash
composer test
```
