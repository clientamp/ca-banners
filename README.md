# CA Banners

A WordPress plugin that displays customizable scrolling banners with advanced scheduling and targeting options.

## Features

- **Customizable Scrolling Banner**: Create eye-catching scrolling text banners
- **Advanced Scheduling**: Set start and end dates for banner display
- **Flexible Display Options**: 
  - Display sitewide or on specific pages
  - Exclude specific pages when displaying sitewide
  - Mobile device control
- **Visual Customization**:
  - Custom background and text colors
  - Font family and size options
  - Border styling (width, style, color)
- **Image Banner Support**: Display images with their own scheduling
- **Easy Administration**: Simple WordPress admin interface

## Installation

1. Download the plugin files
2. Upload the `ca-banners` folder to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to 'CA Banners' in your WordPress admin menu to configure

## Configuration

### Basic Settings

1. **Enable Banner**: Check to activate the banner
2. **Display Sitewide**: Enable to show banner on all pages (overrides page-specific settings)
3. **Banner Message**: Enter the text you want to scroll
4. **Number of Message Repeats**: How many times to repeat the message (1-100)

### Visual Customization

- **Background Color**: Choose banner background color
- **Text Color**: Set text color
- **Font Size**: Adjust text size (10-40px)
- **Font Family**: Select from various font options
- **Border Settings**: Configure border width, style, and color

### Advanced Options

- **Disable on Mobile**: Hide banner on mobile devices
- **Start/End Dates**: Schedule when banner should appear/disappear
- **Display on Pages**: Specify exact URLs where banner should show
- **Exclude on Pages**: URLs to exclude when sitewide is enabled
- **Banner Image**: Upload and schedule image banners

## Usage Examples

### Basic Scrolling Banner
1. Enable the banner
2. Enter your message: "Welcome to our site! Check out our latest offers."
3. Set repeat count to 5
4. Choose your colors and fonts

### Scheduled Promotion
1. Set start date to your promotion start
2. Set end date to your promotion end
3. Enable sitewide display
4. Exclude checkout pages if needed

### Page-Specific Banner
1. Disable sitewide display
2. Add specific URLs in "Display on Pages"
3. Use format: `/about-us/`, `/contact/`, etc.

## Technical Details

- **Version**: 1.1
- **Author**: clientamp
- **Requires**: WordPress 4.0+
- **Tested up to**: WordPress 6.4
- **License**: GPL v2 or later

## Support

For support and feature requests, please contact [clientamp.com](https://clientamp.com/).

## Changelog

### Version 1.1
- Added sitewide display option
- Added page exclusion functionality
- Improved URL matching logic
- Enhanced mobile responsiveness
- Added image banner support with scheduling

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```
