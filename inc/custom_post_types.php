<?php

/**
 * Custom Post Type for People
 */
function labnotes_register_post_types() {

    $defaults = array(
        'public' => true,
        'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt', 'custom-background'),
        'menu_position' => 20,
        'hierarchical' => false,
        'has_archive' => true,
        'show_in_nav_menus' => true
    );

    $post_types = array(
        'people' => array(
            'labels' => array(
                'name' => __( 'People' ),
                'singular_name' => __( 'Person' )
              ),
            'rewrite' => array('slug' => 'people')
        ),
        'research' => array(
            'labels' => array(
                'name' => __( 'Research' ),
                'singular_name' => __( 'Research Project' )
              ),
            'rewrite' => array('slug' => 'research')
        )
    );


    foreach ($post_types as $name => $args) {
        $args = array_merge($args, $defaults);
        register_post_type($name, $args);
    }

}

add_action( 'init', 'labnotes_register_post_types' );


/**
 * Adds meta boxes for custom post types.
 */
function labnotes_add_meta_boxes() {
    add_meta_box("wp-user-information", __('Personal Info'), "labnotes_people_meta_box", "people", "side", "high");
    add_meta_box("wp-user-information", __('Project Info'), "labnotes_research_meta_box", "research", "side", "high");
}

add_action( 'admin_init', 'labnotes_add_meta_boxes');


/**
 * Meta box for personal information.
 */
function labnotes_people_meta_box() {

    global $post;

    $custom = get_post_custom($post->ID);

    $fields = labnotes_meta_fields('people');

    $statusOptions = array('current' => 'Current', 'not_current' => 'Not Current');

?>

<?php foreach ($fields as $field): if ($field == 'person_user_id' || $field == 'person_category' || $field == 'person_status') continue; ?>
    <p><label for="<?php echo $field; ?>"><?php echo ucwords(str_replace('_', ' ', str_replace('person_', ' ', $field))); ?></label></p>
    <p><input type="text" value="<?php echo @$custom[$field][0]; ?>" name="<?php echo $field; ?>" /></p>
<?php endforeach; ?>

    <p><label for="person_user_id">User</label></p>
    <p><?php wp_dropdown_users(array('show_option_none' => 'No User', 'name' => 'person_user_id', 'selected' => @$custom['person_user_id'][0])); ?></p>

    <p><label for="person_status">Status</label></p>
    <p>
      <select name="person_status">
      <option>Choose a Status</option>
      <?php foreach ($statusOptions as $name => $label): ?>
      <option value="<?php echo $name; ?>"<?php if (@$custom['person_status'][0] == $name) echo ' selected="selected"'; ?>><?php echo $label; ?></option>
      <?php endforeach; ?>
      </select>
    </p>

<?php
}

/**
 * Meta box for personal information.
 */
function labnotes_research_meta_box(){
    global $post;
    $custom = get_post_custom($post->ID);

    $fields = labnotes_meta_fields('research');

    $statusOptions = array('current' => 'Current Research', 'archived' => 'Past Research');

?>

    <p><label for="research_url">Project URL</label></p>
    <p><input type="text" value="<?php echo @$custom['research_url'][0]; ?>" name="research_url" /></p>

    <p><label for="research_status">Status</label></p>
    <p>
      <select name="research_status">
      <option>Choose a Status</option>
      <?php foreach ($statusOptions as $name => $label): ?>
      <option value="<?php echo $name; ?>"<?php if (@$custom['research_status'][0] == $name) echo ' selected="selected"'; ?>><?php echo $label; ?></option>
      <?php endforeach; ?>
      </select>
    </p>

<?php
}

/**
* Saves our custom post metadata. Used on the 'save_post' hook.
*/
function labnotes_save_post() {

    global $post;

    $post_type = @$_POST['post_type'];

    if ( $post_type == 'people' || $post_type == 'research' ) {
        $fields = labnotes_meta_fields($post_type);
        foreach ($fields as $field) {
            if ( array_key_exists($field, $_POST)) {
                update_post_meta($post->ID, $field, $_POST[$field]);
            }
        }
    }
}

add_action( 'save_post','labnotes_save_post');


/**
 * Create taxonomies for the People post type.
 */
function create_people_taxonomies() {

    $taxonomies = array(
        'people' => array(
            'name' => _x( 'People Categories', 'taxonomy general name' ),
            'singular_name' => _x( 'People Category', 'taxonomy singular name' ),
            'search_items' =>  __( 'Search People Categories' ),
            'all_items' => __( 'All People Categories' ),
            'parent_item' => __( 'Parent People Category' ),
            'parent_item_colon' => __( 'Parent People Category:' ),
            'edit_item' => __( 'Edit People Category' ),
            'update_item' => __( 'Update People Category' ),
            'add_new_item' => __( 'Add New People Category' ),
            'new_item_name' => __( 'New People Category Name' ),
            'menu_name' => __( 'People Categories' ),
        ),
        'research' => array(
            'name' => _x( 'Research Categories', 'taxonomy general name' ),
            'singular_name' => _x( 'Research Category', 'taxonomy singular name' ),
            'search_items' =>  __( 'Search Research Categories' ),
            'all_items' => __( 'All Research Categories' ),
            'parent_item' => __( 'Parent Research Category' ),
            'parent_item_colon' => __( 'Parent Research Category:' ),
            'edit_item' => __( 'Edit Research Category' ),
            'update_item' => __( 'Update Research Category' ),
            'add_new_item' => __( 'Add New Research Category' ),
            'new_item_name' => __( 'New Research Category Name' ),
            'menu_name' => __( 'Research Categories' ),
        )
    );

    foreach($taxonomies as $name => $labels) {
        register_taxonomy($name.'-category',array($name), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => $name.'-category' ),
        ));
    }
}

add_action( 'init', 'create_people_taxonomies', 0 );

/**
 * Updates the query on people post types.
 *
 * 1. Sets the posts_per_page to -1 to get all posts.
 * 2. Orders alphabetically by family name.
 * 3. Checks query variable for people-category value.
 */
function labnotes_pre_get_posts( $query ) {

    if ( is_admin() || ! $query->is_main_query() )
        return;

    if ( is_post_type_archive( array( 'research', 'people' ) ) ) {
        $query->set( 'posts_per_page', -1 );
    }

    if ( is_post_type_archive( 'people' ) ) {

        $query->set( 'meta_key', 'person_family_name' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'order', 'asc' );

        if ($category = get_query_var('people-category')) {
            $query->set( 'people-category', $category);
        }

        return;

    }

}

add_action( 'pre_get_posts', 'labnotes_pre_get_posts', 1 );
