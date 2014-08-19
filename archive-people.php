<?php get_header(); ?>

<?php

$defaultArgs = array('post_type' => 'people', 'posts_per_page' => -1, 'meta_key' => 'person_family_name', 'orderby' => 'meta_value', 'order' => 'asc');

$currentCategories = array(
    'staff' => 'Staff',
    'student-assistant' => 'Student Assistants',
    '2014-2015-praxis-fellow, 2014-2015' => 'Gradute Fellows'
);

?>
<header>
    <h1>People</h1>
</header>

<?php

foreach ($currentCategories as $category => $title) :

    $currentArgs = array(
        'people-category' => $category,
        'meta_query' => array(
            array(
                'key'=>'person_status',
                'value'=> 'current',
                'compare' => '='
            )
        )
    );

    $currentArgs = array_merge($defaultArgs, $currentArgs);

query_posts($currentArgs);
if (have_posts()) : ?>

<?php
    $headingTag = 'h2';

    if ($category == 'student-assistant') {
        $headingTag = 'h3';
    }

    $heading = '<'.$headingTag.'>'
           .$title.'</'.$headingTag.'>';

    echo $heading;
?>
<ul class="people-list current">
<?php while( have_posts() ) : the_post(); ?>
<li>
<a href="<?php the_permalink(); ?>">
    <img src="<?php echo labnotes_custom_background_image_src(); ?>" alt="" />
    <?php the_title(); ?>
</a>
</li>
<?php endwhile; ?>
</ul>
<?php endif; wp_reset_query(); endforeach; ?>

<?php if (have_posts()) : ?>
<h2>Alumni</h2>
<ul class="people-list">
<?php while( have_posts() ) : the_post(); ?>
<?php
     $status = get_post_meta( $post->ID, 'person_status', true);
if(is_null($status) || $status == 'current') continue;
?>
<li>
<a href="<?php the_permalink(); ?>">
    <?php the_title(); ?>
</a>
</li>
<?php endwhile; ?>
</ul>
<?php endif; ?>

<?php get_footer(); ?>
