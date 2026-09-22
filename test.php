<?php
require 'wp-load.php';
// Get a random product ID
$args = array(
    'post_type' => 'product',
    'posts_per_page' => 1
);
$products = get_posts($args);
if ($products) {
    $product = wc_get_product($products[0]->ID);
    echo $product->get_price_html();
} else {
    echo 'No products found.';
}
