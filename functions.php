<?php
function my_cron_schedules($schedules) {
    if (!isset($schedules["5min"])) {
        $schedules["5min"] = array(
            'interval' => 5 * 60,
            'display'  => __('Once every 5 minutes')
        );
    }
    return $schedules;
}
add_filter('cron_schedules', 'my_cron_schedules');

if (!wp_next_scheduled('my_schedule_hook')) {
    wp_schedule_event(time(), '5min', 'my_schedule_hook');
}

add_action('my_schedule_hook', 'my_fetch_and_compare_posts');
function my_fetch_and_compare_posts() {
    $existing_posts = get_posts(array(
        'numberposts' => -1,
        'post_type'   => 'post',
        'post_status' => 'publish'
    ));

    $existing_hashes = array();
    foreach ($existing_posts as $post) {
        $hash = hash('sha256', $post->post_title . $post->post_content);
        $existing_hashes[] = $hash;
    }

    $response = wp_remote_get('https://ma.tt/wp-json/wp/v2/posts');
    if (200 === wp_remote_retrieve_response_code($response)) {
        $posts = json_decode(wp_remote_retrieve_body($response), true);

        $hashed_posts = [];
        foreach ($posts as $post) {
            $hash = hash('sha256', $post['title']['rendered'] . $post['content']['rendered']);
            $hashed_posts[] = [
                'hash'    => $hash,
                'title'   => $post['title']['rendered'],
                'content' => $post['content']['rendered'],
            ];
        }

        compare_the_posts($hashed_posts, $existing_hashes);
    }
}

function compare_the_posts($hashed_posts, $existing_hashes) {
    foreach ($hashed_posts as $post_data) {
        $hashed_post = $post_data['hash'];
        if (!in_array($hashed_post, $existing_hashes)) {
            $new_post = array(
                'post_title'   => wp_strip_all_tags($post_data['title']),
                'post_content' => $post_data['content'],
                'post_status'  => 'publish',
                'post_author'  => 1,  
                'post_category'=> array(1) 
            );
            wp_insert_post($new_post);

            echo 'New post inserted: ' . esc_html($post_data['title']) . '<br>';?>
            <div class="post-container">
                <h2><?php echo esc_html($post_data['title']); ?></h2>
                <p><?php echo wp_kses_post($post_data['content']); ?></p>
            </div>
            <?php
        } else {
            echo 'The post already exists: ' . esc_html($post_data['title']) . '<br>';
        }
    }
}?>