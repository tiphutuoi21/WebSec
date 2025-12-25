<?php
    require 'connection.php';
    require 'SecurityHelper.php';
    
    header('Content-Type: application/json');
    
    // Get and sanitize search query from GET
    $search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $results = array();
    
    // Define harmful characters and patterns that should be filtered
    $harmful_patterns = array(
        '/<script[^>]*>.*?<\/script>/i',  // Script tags
        '/<iframe[^>]*>.*?<\/iframe>/i', // iFrame tags
        '/<img[^>]*on\w+\s*=/i',          // img with event handlers
        '/<[a-z][a-z0-9]*[^>]*on\w+\s*=/i', // Any tag with event handlers
        '/<svg[^>]*on\w+\s*=/i',          // SVG with event handlers
        '/javascript:/i',                  // javascript: protocol
        '/data:text\/html/i',             // data:text/html protocol
        '/vbscript:/i',                   // vbscript: protocol
        '/<body[^>]*on\w+\s*=/i'          // body with event handlers
    );
    
    if (strlen($search_query) >= 2) {
        // Check for harmful patterns
        $is_harmful = false;
        foreach ($harmful_patterns as $pattern) {
            if (preg_match($pattern, $search_query)) {
                $is_harmful = true;
                break;
            }
        }
        
        if ($is_harmful) {
            // Return empty results if harmful content detected
            echo json_encode($results);
            exit();
        }
        
        // Validate search length (max 255 characters)
        if (strlen($search_query) > 255) {
            $search_query = substr($search_query, 0, 255);
        }
        
        // Remove any remaining special characters that could be harmful
        // Allow only alphanumeric, spaces, and common Vietnamese characters
        $search_query = preg_replace('/[^a-zA-Z0-9\p{L}\s\-_.]/u', '', $search_query);
        $search_query = trim($search_query);
        
        // If search query is empty after filtering, return no results
        if (strlen($search_query) < 2) {
            echo json_encode($results);
            exit();
        }
        
        // Prepare statement to prevent SQL injection
        $query = "SELECT id, name, price, category FROM items WHERE is_active = 1 AND (name LIKE ? OR category LIKE ? OR description LIKE ?) ORDER BY name ASC LIMIT 10";
        $stmt = mysqli_prepare($con, $query);
        
        if ($stmt) {
            // Add wildcards to search term for partial matching
            $search_term = "%" . $search_query . "%";
            mysqli_stmt_bind_param($stmt, "sss", $search_term, $search_term, $search_term);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($product = mysqli_fetch_assoc($result)) {
                $results[] = array(
                    'id' => $product['id'],
                    'name' => htmlspecialchars($product['name']),
                    'price' => number_format($product['price'], 0, ',', '.'),
                    'category' => htmlspecialchars($product['category'])
                );
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    echo json_encode($results);
?>

