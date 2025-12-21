<?php
    require 'connection.php';
    
    header('Content-Type: application/json');
    
    // Get search query from GET
    $search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $results = array();
    
    if (strlen($search_query) >= 2) {
        // Validate search length (max 255 characters)
        if (strlen($search_query) > 255) {
            $search_query = substr($search_query, 0, 255);
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

