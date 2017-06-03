<?php
/*
Plugin Name: MSP Hello World
Description: Create hello world message
Version: 1.0
Author: Author's name
Author URI: http://easy-code.ru/
Plugin URI: http://easy-code.ru/lesson/building-wordpress-plugin-part-one
*/
define('MSP_HELLOWORLD_DIR', plugin_dir_path(__FILE__));
define('MSP_HELLOWORLD_URL', plugin_dir_url(__FILE__));

if( $atts['postscount'] == ''){
      $atts['postscount'] = get_option('postscount'); }
if( $atts['color'] == ''){
      $atts['color'] = get_option('color'); }
if( $atts['color_on_hover'] == ''){
      $atts['color_on_hover'] = get_option('color_on_hover'); }
add_shortcode('cat_subcat_tree','my1');

register_activation_hook(__FILE__, 'msp_helloworld_activation');
register_deactivation_hook(__FILE__, 'msp_helloworld_deactivation');

function msp_helloworld_activation() 
{
    // действие при активации
	register_uninstall_hook(__FILE__, 'msp_helloworld_uninstall');
}

function msp_helloworld_deactivation() 
{
    // при деактивации
}

function msp_helloworld_footer_notice()
{
	echo "<div id='msp-helloworld-notice'>Hello, I'm your custom notice</div>";
}

add_action('wp_footer', 'msp_helloworld_footer_notice');
$content = apply_filters('the_content', $content);

function msp_helloworld_post_footer($content) 
{
	$content .= "<div class='msp-helloworld-post-footer'><p>Hello, I'm your custom post footer</p></div>";
	return $content;
}

add_filter('the_content', 'msp_helloworld_post_footer', 100);
add_action( 'dynamic_sidebar', 'my1');

function my1($atts)
{ 	
	wp_enqueue_script('my-script', plugins_url('script.js', __FILE__), array('jquery'), '1.0', true);
	wp_enqueue_style('my-styles', plugins_url('style.css', __FILE__));	
	extract(shortcode_atts( array(
								 'color' => 'grey',
								 'color_on_hover' => 'orange',
								 'postscount' => '2',
								  ),
						    $atts 
						   ) 
			);
	$args = array(
				'show_option_none'   => __('No categories'),
				'orderby'            => 'name',
				'order'              => 'ASC',
				'show_last_update'   => 1,
				'hide_empty'         => 0,
				);
	$categories=get_categories($args);
	if ($categories) 
	{
		echo '<div style=" border:1px solid #96396a; background-color:white;width:180px; "> <div style="font-size:25px;font-style:italic;color:#96396a;margin-left:20px;">Categories:</div></div>';
		?>
		<div id='main_category' style="background-color:<?php echo $color; ?>">
		<?php	
		foreach($categories as $category)
		{
			if(!$category->parent)
			{
				?>
				<div class='div_for_each_category' id="<?php echo $category->name?>" >
				<?php
				echo ' <div class="branch" ><a  href="' . get_category_link( $category->term_id ) . '   " title="' . $category->count . '" style="color:white;" ' .'>'. $category->name.'</a>';echo "   ";echo $category->term_id;;
				draw_category_post_tree($category,$color);
				?>
				</div>
				</div> 
				<?php
			}
		}
		$args = array( 'posts_per_page' => -1);
		$myposts = get_posts( $args );
		foreach ( $myposts as $post )
		{
			setup_postdata( $post );
			$a=(get_the_category($post->ID));
			foreach($a as $c)
			{
				if(( $a[0]->name )=="Без рубрики")
				{
					echo '<div class="div_for_posts branch" > <a href="'.$post->guid.' "> '.$post->post_title.'</a></div>';
				}
			}
		}
		//wp_reset_postdata();
		?>
		</div>
		<?php
	}
}
function draw_category_post_tree($root,$col)
{	
	if(!($root))return;
	$args = array(
	'child_of' => $root->term_id,
	'show_option_none'   => __('No categories'),
	'order'              => 'ASC',
	'show_last_update'   => 1,
	'hide_empty'         => 0 );
	$subcategories=get_categories($args);
	$b=$root->term_id;
	$b+=0;	
	$args = array( 'posts_per_page' => -1,
				   'category'=> $b);
	$myposts = get_posts( $args );
	if($myposts) $k=1;
	else $k=0;
	if($k || $subcategories)
	{
		?>
		<div id="div_for_subcategory_of_<?php echo $root->name?>" class="div_for_subcategory" style="background-color:<?php echo $col; ?>" >
		<?php
		foreach ( $myposts as $post )
		{
			setup_postdata( $post );
			$a=(get_the_category($post->ID));
			foreach($a as $c)
			{
				if($c->cat_name==$root->name)
				{
					echo '<div class="div_for_posts branch" > <a href="'.$post->guid.' "> '.$post->post_title.'</a></div>';
				}
			}
		}
		wp_reset_postdata();
		if($subcategories)
		{
				foreach($subcategories as $cat)
				{	 if($cat->parent== $root->term_id)
					{
					echo'<div class="branch" >
					<a  href="' . get_category_link( $cat->term_id ) . '" title="' . $cat->count . '"  ' . '>' . $cat->name.'</a>';echo "   ";echo $cat->term_id;;
					draw_category_post_tree($cat,$col);
					echo '</div>';
					}
				}
		} 
		?> 
		</div> 
		<?php  
	}
}
?>