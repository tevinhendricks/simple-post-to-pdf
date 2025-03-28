<?php
/**
 * Plugin Name: Simple Post to PDF
 * Description: Converts blog posts into downloadable PDF documents
 * Version: 1.0.0
 * Author: Tevin Jason Hendricks
 * Text Domain: simple-post-to-pdf
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Simple_Post_To_PDF {
    
    /**
     * Constructor to initialize the plugin
     */
    public function __construct() {
        // Add PDF download button after post content
        add_filter('the_content', array($this, 'add_pdf_button'));
        
        // Handle the PDF generation
        add_action('init', array($this, 'generate_pdf'));
        
        // Add the required scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'simple-post-to-pdf-style',
            plugin_dir_url(__FILE__) . 'assets/css/style.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue dashicons if not already included
        wp_enqueue_style('dashicons');
    }
    
    /**
     * Add PDF download button to post content
     */
    public function add_pdf_button($content) {
        // Only add button to single posts
        if (is_single() && get_post_type() === 'post') {
            $post_id = get_the_ID();
            $pdf_url = add_query_arg(
                array(
                    'sptpdf' => 'generate',
                    'post_id' => $post_id,
                    'nonce' => wp_create_nonce('generate_pdf_' . $post_id)
                ),
                home_url()
            );
            
            $button = '<div class="pdf-download-container">';
            $button .= '<a href="' . esc_url($pdf_url) . '" class="pdf-download-button" target="_blank">';
            $button .= '<span class="dashicons dashicons-pdf"></span> Download as PDF';
            $button .= '</a>';
            $button .= '</div>';
            
            // Add the button after the content
            $content .= $button;
        }
        
        return $content;
    }
    
    /**
     * Generate PDF file when requested
     */
    public function generate_pdf() {
        // Check if PDF generation is requested using our custom query parameter
        if (isset($_GET['sptpdf']) && $_GET['sptpdf'] === 'generate' && isset($_GET['post_id'])) {
            $post_id = intval($_GET['post_id']);
            
            // Verify nonce for security
            if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'generate_pdf_' . $post_id)) {
                wp_die('Security check failed');
            }
            
            // Get post data
            $post = get_post($post_id);
            
            if (!$post) {
                wp_die('Post not found');
            }
            
            // Get post content and prepare it for PDF
            $post_title = get_the_title($post_id);
            $post_content = apply_filters('the_content', $post->post_content);
            $post_author = get_the_author_meta('display_name', $post->post_author);
            $post_date = get_the_date('F j, Y', $post_id);
            $site_name = get_bloginfo('name');
            
            // Remove specific elements from the content that might cause issues
            $post_content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $post_content);
            
            // Get the featured image if available
            $featured_image = '';
            if (has_post_thumbnail($post_id)) {
                $image_id = get_post_thumbnail_id($post_id);
                $image_url = wp_get_attachment_image_src($image_id, 'large');
                if ($image_url) {
                    $featured_image = $image_url[0];
                }
            }
        

            require_once plugin_dir_path(__FILE__) . 'includes/tcpdf/tcpdf.php';
            
            // Create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($post_author);
            $pdf->SetTitle($post_title);
            $pdf->SetSubject($post_title);
            $pdf->SetKeywords('WordPress, PDF, post, ' . $post_title);
            
            // Set default header data
            $pdf->SetHeaderData('', 0, $post_title, $site_name . ' | By ' . $post_author . ' | ' . $post_date);
            
            // Set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            
            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            
            // Set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            
            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            
            // Set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            
            // Add a page
            $pdf->AddPage();
            
            // Add featured image if available
            if ($featured_image) {
                // Get image dimensions
                list($width, $height) = getimagesize($featured_image);
                $aspect = $width / $height;
                $img_width = 180; // Max width in mm
                $img_height = $img_width / $aspect;
                
                $pdf->Image($featured_image, 15, 30, $img_width, $img_height, '', '', 'T', false, 300, '', false, false, 0);
                $pdf->Ln($img_height + 10); // Add space after image
            } else {
                $pdf->Ln(20); // Just add some space if no image
            }
            
            // Set font
            $pdf->SetFont('helvetica', '', 12);
            
            // Add content
            $pdf->writeHTML($post_content, true, false, true, false, '');
            
            // Add a footer with the site URL
            $pdf->SetY(-15);
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->Cell(0, 10, 'Downloaded from ' . home_url(), 0, false, 'C');
            
            // Close and output PDF document
            $pdf->Output(sanitize_title($post_title) . '.pdf', 'I');
            exit;
        }
    }

    /**
     * Activation hook callback
     * Called when the plugin is activated
     */
    public static function activate() {
        // Create necessary directories
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-exports';
        
        // Check if the directory exists, if not create it
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }
        
        // You could also:
        // - Create custom database tables if needed
        // - Initialize plugin settings with default values
        // - Check for required PHP extensions (like GD library for image processing)
        // - Flush rewrite rules if the plugin registers custom post types or taxonomies
        
        // Example: Add a capability to administrators
        // $role = get_role('administrator');
        // if ($role) {
        //     $role->add_cap('export_post_to_pdf');
        // }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Deactivation hook callback
     * Called when the plugin is deactivated
     */
    public static function deactivate() {
        // Clean up temporary data if needed
        // Remove scheduled events
        // wp_clear_scheduled_hook('simple_post_to_pdf_scheduled_task');
        
        // You could also:
        // - Remove capabilities added during activation
        // - Save user preferences for when they reactivate
        // - Log deactivation for analytics
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Uninstall hook callback
     * Called when the plugin is deleted
     */
    public static function uninstall() {
        // This is typically handled by a separate uninstall.php file
        // But for simple plugins, you can include it here
        
        // Remove all plugin data:
        // - Delete plugin options
        // delete_option('simple_post_to_pdf_settings');
        
        // - Delete any custom database tables
        // global $wpdb;
        // $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}simple_post_to_pdf_logs");
        
        // - Delete any files or directories created by the plugin
        // $upload_dir = wp_upload_dir();
        // $pdf_dir = $upload_dir['basedir'] . '/pdf-exports';
        // if (file_exists($pdf_dir)) {
        //     require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
        //     $filesystem = new WP_Filesystem_Direct(null);
        //     $filesystem->rmdir($pdf_dir, true);
        // }
    }
}

// Initialize the plugin
$simple_post_to_pdf = new Simple_Post_To_PDF();

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('Simple_Post_To_PDF', 'activate'));
register_deactivation_hook(__FILE__, array('Simple_Post_To_PDF', 'deactivate'));

// Register uninstall hook - Note: register_uninstall_hook must be called from the main plugin file
register_uninstall_hook(__FILE__, array('Simple_Post_To_PDF', 'uninstall'));