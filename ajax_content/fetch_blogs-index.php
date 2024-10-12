<?php
$conn = mysqli_connect('localhost', 'root', '', 'novocamera');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to fetch the main blog (latest)
$main_blog_query = "
    SELECT blog_id, title, image_url, content, created_at, blog_type, author, uploaded_by,
    (SELECT COUNT(*) FROM comments WHERE comments.blog_id = blogs.blog_id) AS comments_count
    FROM blogs
    WHERE status = 'published'
    ORDER BY created_at DESC
    LIMIT 1
";

$main_blog_result = $conn->query($main_blog_query);
$main_blog = $main_blog_result->fetch_assoc();

// Query to fetch the next 4 blogs
$top_blogs_query = "
    SELECT blog_id, title, image_url, content, created_at, blog_type, author, uploaded_by,
    (SELECT COUNT(*) FROM comments WHERE comments.blog_id = blogs.blog_id) AS comments_count
    FROM blogs
    WHERE status = 'published' AND blog_id != '{$main_blog['blog_id']}'
    ORDER BY created_at DESC
    LIMIT 4
";

$top_blogs_result = $conn->query($top_blogs_query);

$blogs = [
    'main_blog' => $main_blog,
    'top_blogs' => $top_blogs_result->fetch_all(MYSQLI_ASSOC),
];

echo json_encode($blogs);

$conn->close();
