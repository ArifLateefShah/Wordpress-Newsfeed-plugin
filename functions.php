<?php

function newsfeed_enqueue_style() {
    wp_enqueue_style( 'newsfeed-bootstrap-style', plugin_dir_url( __FILE__ )."css/bootstrap.css" ); 
    wp_enqueue_style( 'newsfeed-style', plugin_dir_url( __FILE__ )."css/styles.css" ); 
    
}
add_action( 'wp_enqueue_scripts', 'newsfeed_enqueue_style' );


function add_my_plugin_js(){
    wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
    wp_enqueue_script('prefix_bootstrap');
}

add_action('wp_print_scripts','add_my_plugin_js');


 function display_news_page($atts) {
    
    $newsTopic = isset($atts["topic"]) ? $atts["topic"] : "bitcoin";
    $fromDate = isset( $atts["fromDate"])? $atts["fromDate"] : "";
    $sortBy = isset($atts["sortBy"]) ? $atts["sortBy"] : "publishedAt";
    
     $value = get_option('myplugin_option_name');
     if($value){
        $request = wp_remote_get( 'http://newsapi.org/v2/everything?q='.$newsTopic.'&from='.$fromDate.'&sortBy='.$sortBy.'&apiKey='.$value );
        echo "<h2>From News Feed</h2>";
        
     }else{
         echo "<h2>Please add the API key in Settings</h2>";
     }

        

            if( is_wp_error( $request ) ) {
                return false; // Bail early
            }

            $body = wp_remote_retrieve_body( $request );
            $feed = "";
            $data = json_decode( $body );
                if( isset($data) && !empty($data) ) {
                    $feed .='<div class="container">';
                    $feed .= '<div class="row">';
                    if(isset($data->articles)){
                        foreach($data->articles as $article ) {
                            $feed .='<div class="col-md-4 tile">';
                            $feed .='<img src='.$article->urlToImage.' class="img-responsive">';
                            $feed .='<h5>'.$article->title.'</h5>' ;
                            $feed .= '<p>'.$article->description.'</p>';
                            $feed .= '</div>';
                        }
                    }
                    $feed .= '</div>';
                $feed .= '</div>';
                    return $feed;
                }
            }

 function news_admin_menu() {
   add_menu_page(
         'News Feed plugin',// page title
         'News',// menu title
         'manage_options',// capability
         'news',// menu slug
         'display_news_page' // callback function
     );
 }
 add_action('admin_menu', 'news_admin_menu');

add_shortcode('news', 'display_news_page'); 


function myplugin_register_settings() {
    add_option( 'myplugin_option_name', 'This is my option value.');
    register_setting( 'myplugin_options_group', 'myplugin_option_name', 'myplugin_callback' );
 }
 add_action( 'admin_init', 'myplugin_register_settings' );


 function myplugin_register_options_page() {
    add_options_page('Page Title', 'Plugin Menu', 'manage_options', 'myplugin', 'myplugin_options_page');
  }
  add_action('admin_menu', 'myplugin_register_options_page');




  function myplugin_options_page()
{
?>
  <div>
  <?php screen_icon(); ?>
  <h2>My Plugin Page Title</h2>
  <form method="post" action="options.php">
  <?php settings_fields( 'myplugin_options_group' ); ?>
  <h3>This is my option</h3>
  <p>Some text here.</p>
  <table>
  <tr valign="top">
  <th scope="row"><label for="myplugin_option_name">Label</label></th>
  <td><input type="text" id="myplugin_option_name" name="myplugin_option_name" value="<?php echo get_option('myplugin_option_name'); ?>" /></td>
  </tr>
  </table>
  <?php  submit_button(); ?>
  </form>
  </div>
<?php
} ?>