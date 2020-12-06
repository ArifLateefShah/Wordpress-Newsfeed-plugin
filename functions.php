<?php

// Adding bootstrap and custom styles
function newsfeed_enqueue_style() {
    wp_enqueue_style( 'newsfeed-bootstrap-style', plugin_dir_url( __FILE__ )."css/bootstrap.css" ); 
    wp_enqueue_style( 'newsfeed-style', plugin_dir_url( __FILE__ )."css/styles.css" ); 
    
}
add_action( 'wp_enqueue_scripts', 'newsfeed_enqueue_style' );

// Adding bootstrap and custom Javasctipt
function add_newsfeed_js(){
    wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
    wp_enqueue_script('prefix_bootstrap');
}
add_action('wp_print_scripts','add_newsfeed_js');


// Function to display news feed 
 function display_news_page($atts) {
    
    $newsTopic = isset($atts["topic"]) ? $atts["topic"] : "";
    $fromDate = isset( $atts["fromDate"])? $atts["fromDate"] : "";
    $sortBy = isset($atts["sortBy"]) ? $atts["sortBy"] : "";
    
     $value = get_option('newsfeed_option_name');
     if($value){
        $request = wp_remote_get( 'http://newsapi.org/v2/everything?q='.$newsTopic.'&from='.$fromDate.'&sortBy='.$sortBy.'&apiKey='.$value );
       
        if( is_wp_error( $request ) ) {
            return false; 
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
        } else {
            echo "<h2>Something is wrong, Can you check the API key in Configuration page</h2>";
        }
}

 add_shortcode('news', 'display_news_page'); 


function newsfeed_register_settings() {
    add_option( 'newsfeed_option_name', 'This is my option value.');
    register_setting( 'newsfeed_options_group', 'newsfeed_option_name', 'newsfeed_callback' );
 }
 add_action( 'admin_init', 'newsfeed_register_settings' );


 function newsfeed_register_options_page() {
    add_menu_page(
        'Newsfeed Configurations Section',
        'Newsfeed',
        'manage_options',
        'newsfeed',
        'newsfeed_options_page'
    );
  }
  add_action('admin_menu', 'newsfeed_register_options_page');




  function newsfeed_options_page(){
?>
  <div>
    <?php screen_icon(); ?>
    <h2>Newsfeed Configurations</h2>
    <h3>Shortcode : [news] </h3>
    <h4>You can add add news category in shortcode as <br>
    <ol>
        <li>bitcoin</li>
        <li>apple</li>
     </ol>
        Shorcode will become <span style="color:blue"> [news topic=”bitcoin” ] </span>
     </h4>
     <h4>You can sort the newsfeed by 
     <ol>
        <li>author</li>
        <li>publishedDate</li>
        <li>title</li>
     </ol>
        Shorcode will become <span style="color:blue"> [news topic=”bitcoin” sortBy="publishedDate"] </span>
     </h4>


        <form method="post" action="options.php">
            <?php settings_fields( 'newsfeed_options_group' ); ?>
            <p>Add your newsfeed API key here.You can generate API key on <a href="https://newsapi.org/" target="_blank">Newsfeed Website</a>.</p>
            <table>
                <tr valign="top">
                    <th scope="row"><label for="newsfeed_option_name">API Key</label></th>
                    <td><input type="text" class="form-control" id="newsfeed_option_name" name="newsfeed_option_name" value="<?php echo get_option('newsfeed_option_name'); ?>" /></td>
                </tr>
            </table>
            <?php  submit_button(); ?>
        </form>
  </div>
<?php
} ?>