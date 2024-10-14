<?php
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC'
);
$query = new WP_Query($args);

if ($query->have_posts()) : ?>
    <div class="all-posts">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <div class="post-container">
                <h2><?php the_title(); ?></h2>
                <p><?php the_content(); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
<?php else : ?>
    <p>No posts found.</p>
<?php endif; 
wp_reset_postdata();
?>