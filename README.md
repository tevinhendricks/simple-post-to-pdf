# Simple Post to PDF

A WordPress plugin that converts blog posts into downloadable PDF documents.

## Description

Simple Post to PDF is a lightweight WordPress plugin that allows readers to download blog posts as PDF files. The plugin adds a "Download as PDF" button at the end of each blog post, generating a well-formatted PDF document that includes the post's title, author information, publication date, featured image, and complete content.

### AI Prompt Template

Here's the prompt you can use with any AI assistant to help create this plugin:

```
Create a WordPress plugin that allows users to download blog posts as PDF files. The plugin should add a "Download as PDF" button at the end of each blog post, which generates a PDF document containing the post's title, author, date, featured image, and content.

The plugin should:

1. Add a "Download as PDF" button at the end of each blog post
2. Generate a PDF file when the button is clicked, containing:
   - Post title
   - Author name
   - Publication date
   - Featured image (if available)
   - Complete post content
3. Use the TCPDF library for PDF generation
4. Include proper security measures like nonce verification
5. Have basic styling for the download button
6. Include activation, deactivation, and uninstall hooks

Please provide the complete plugin code with:
- Main plugin file (simple-post-to-pdf.php)
- CSS styles for the button
- Detailed instructions for where to place the TCPDF library
- Security implementation details
```

### To include the TCPDF library in your plugin, follow these steps:

1. Download TCPDF first:

   - Go to the TCPDF GitHub repository (https://github.com/tecnickcom/TCPDF/releases)
   - Download the latest release ZIP file


2. Extract and organize files:

   - Extract the ZIP file on your computer
   - Create an "includes/tcpdf" folder inside your plugin directory
   - Copy the essential files from the extracted TCPDF folder to your plugin's "includes/tcpdf" folder


Note: The TCPDF library should be downloaded separately and placed in the includes directory. You could also use composer dependancy manager to install tcpdf

## Features

- Adds a styled "Download as PDF" button to the end of blog posts
- Generates PDF documents with proper formatting and styling
- Includes post metadata (title, author, date) in the PDF header
- Adds the featured image to the PDF when available
- Secures PDF generation with WordPress nonces
- No configuration needed - works out of the box

## Installation

1. Download the plugin files and extract them
2. Upload the `simple-post-to-pdf` folder to your `/wp-content/plugins/` directory
3. Ensure the TCPDF library is placed in the `includes/tcpdf/` directory
4. Activate the plugin through the 'Plugins' menu in WordPress
5. That's it! The "Download as PDF" button will appear at the end of your blog posts

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- TCPDF library (included)

## Structure

The plugin has a simple structure:

```
simple-post-to-pdf/
├── includes/
│   └── tcpdf/      (TCPDF library)
├── assets/
│   └── css/
│       └── style.css
├── simple-post-to-pdf.php
└── index.php       (Empty file for security)
```

## Usage

After activation, a "Download as PDF" button will automatically appear at the end of each blog post. When a user clicks this button, the plugin generates a PDF version of the post and prompts the download.

No configuration is required for basic functionality.

## Customization

The plugin's appearance can be customized by modifying the CSS in `assets/css/style.css`.

## Contributing

Contributions are welcome! Feel free to:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

Distributed under the GPL v2 or later. See `LICENSE` for more information.

## Author

Tevin Jason Hendricks

## Acknowledgements

- [TCPDF](https://tcpdf.org/) - PHP library for PDF document generation
- [WordPress Plugin API](https://developer.wordpress.org/plugins/)

## Roadmap

Future enhancements may include:
- Admin settings page for customization options
- Support for custom post types
- PDF styling options
- PDF caching for improved performance

## Workshop Information

This plugin was developed as part of a WordPress development workshop. The initial prompt for creating this plugin was generated using Claude by Anthropic, but workshop participants are encouraged to use any AI assistant they prefer.
