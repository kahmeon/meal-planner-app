<?php
include('../community-module/community-function.php');

$search = trim($_GET['search'] ?? '');
$results = [];

if (!empty($search)) {
    $query = "
    SELECT r.id as recipe_id, r.title, t.name AS tag, c.community_id
    FROM recipes r
    LEFT JOIN recipe_tags rt ON r.id = rt.recipe_id
    LEFT JOIN tags t ON rt.tag_id = t.id
    LEFT JOIN community c ON r.id = c.recipe_id
    WHERE r.title LIKE '%$search%' OR t.name LIKE '%$search%'
    ORDER BY r.id
    ";


    $res = mysqliOperation($query);

    // Group tags by recipe
    while ($row = mysqli_fetch_assoc($res)) {
        $id = $row['recipe_id'];
        if (!isset($results[$id])) {
            $results[$id] = [
                'title' => $row['title'],
                'community_id' => $row['community_id'], // 🔥 add this line
                'tags' => [],
            ];
        }        
        if (!empty($row['tag'])) {
            $results[$id]['tags'][] = $row['tag'];
        }
    }

    // Re-index for JSON
    $results = array_values($results);
}

header('Content-Type: application/json');
echo json_encode($results);
?>
