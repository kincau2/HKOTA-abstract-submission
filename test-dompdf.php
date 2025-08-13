<?php
/**
 * Simple test to verify DomPDF integration
 */

// Include WordPress (adjust path as needed)
// This is just for testing - remove this file after testing
define('ABSPATH', dirname(__FILE__) . '/../../../../');
define('HKOTA_ABSTRACT_PLUGIN_PATH', dirname(__FILE__) . '/');

// Include composer autoload
require_once HKOTA_ABSTRACT_PLUGIN_PATH . 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

echo "Testing DomPDF integration...\n";

try {
    // Configure DomPDF options
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    
    // Initialize DomPDF
    $dompdf = new Dompdf($options);
    
    // Simple test HTML
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Test PDF</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            h1 { color: #0073aa; }
        </style>
    </head>
    <body>
        <h1>HKOTA PDF Test</h1>
        <p>This is a test PDF generated using DomPDF.</p>
        <p>If you can see this, DomPDF is working correctly!</p>
    </body>
    </html>';
    
    // Load HTML content
    $dompdf->loadHtml($html);
    
    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');
    
    // Render PDF
    $dompdf->render();
    
    echo "✓ DomPDF is working correctly!\n";
    echo "✓ PDF can be generated successfully.\n";
    
    // Optionally save to file for testing
    $output = $dompdf->output();
    file_put_contents('test-pdf-output.pdf', $output);
    echo "✓ Test PDF saved as 'test-pdf-output.pdf'\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "✗ Fatal Error: " . $e->getMessage() . "\n";
}
