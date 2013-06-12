<?php
/*
Plugin Name: Bulk Creator
Plugin URI: http://capnjeremy.com
Description: Allows bulk creation of posts, pages, and custom post types.
Version: 1.0
Author: Jeremy Wilson
Author URI: http://capnjeremy.com
License: WTFPL
*/



add_action( 'admin_menu', 'add_bp_pages' );



function add_bp_pages(){
    
    
    if ( current_user_can( 'manage_options' ) )  {
    
        add_utility_page( 'Bulk Creator', 'Bulk Creator', 'manage_options', 'bulk-creator', 'bulk_creator', plugin_dir_url().'bulk-creator/images/bulk-creator.png' );
    
    }


}






function bulk_creator() {



    /* TODO
    
        -- Taxonomies
        -- Post Meta? Maybe pull in from ACF?
    
    */


    if ( !current_user_can( 'manage_options' ) )  {
    
    	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    
    }
    
    // these are the default post types that are supported
    $allowed_types = array(
    
        'post' => 'Posts',
        'page' => 'Pages'
    
    );
    
    
    // get a list of the custom post types
    $custom_post_types = get_post_types( array('_builtin'=>false), 'objects' ); 
    

    // add the custom post types to the $allowed_types array
    foreach($custom_post_types as $post_type){
    
    
        $allowed_types[$post_type->name] = $post_type->labels->name;
    
    }
    
    
    echo '<link rel="stylesheet" type="text/css" href="'.plugin_dir_url().'bulk-creator/css/bulk-creator.css">';
    echo '<script src="'.plugin_dir_url().'bulk-creator/js/bulk-creator.js"></script>';
    
    
    	
	// begin main output
	echo '<div class="wrap">';
	
	    
	    ?>
	    
	        <div id="icon-edit" class="icon32 icon-bulk-creator"></div>
	        

	        <h2>Bulk Creator</h2>
	        
	        
	        <nav class="post-types">
	            
	            <p><strong>Choose a Post Type</strong> 
	            
	            <?php
	            
	                foreach($allowed_types as $type=>$label) {
	                
	                    $active_class = "";
	                    
	                    if($_GET["post_type"] == $type) {
	                    
	                        $active_class = "active";
	                    
	                    }
	                    
	                    echo '<a class="button '.$active_class.'" href="?page='.$_GET['page'].'&post_type='.$type.'">'.$label.'</a>';
	                
	                }
	            
	            ?>
	            
	            </p>
	            
	        
	        </nav>
	        
	        
	        <?php
	        
	            if( isset($_POST['pages-list']) && $_POST['pages-list'] != '' ){
	                 
                	if(preg_match_all('/(\d+\|(-|new)?\d+\|[^\n]*)/',$_POST['pages-list'],$match_page)){
                	
                		$newpage = array();
                		
                		foreach($match_page[0] as $page_result){
                		
                			if(preg_match('/((\d+)\|((-|new)?\d+)\|(.*))/',$page_result,$rres)){
                			
                				$parent = -1;
                				
                				if($rres[4]=='new'){
                				
                					$parent = $newpage[str_ireplace('new','',$rres[3])];
                					
                				}else{
                				
                					$parent = $rres[3];
                					
                				}
                				
                				if($parent==-1) $parent = 0;
                				
                				$pcontent = '';
                				
                				if($_POST['d-content'] != ''){
                				
                   					$_POST['d-content'] = str_ireplace('[pagetitle]','<h2>' . htmlentities($rres[5]) . '</h2>',$_POST['d-content']);
                					
                					$_POST['d-content'] = str_ireplace('[lipsum]', '<p><strong>Pellentesque habitant morbi tristique</strong> senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. <em>Aenean ultricies mi vitae est.</em> Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, <code>commodo vitae</code>, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. <a href="#">Donec non enim</a> in turpis pulvinar facilisis. Ut felis.</p>
                					
                					<h2>Header Level 2</h2>
                						       
                					<ol>
                					   <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
                					   <li>Aliquam tincidunt mauris eu risus.</li>
                					</ol>
                					
                					<blockquote><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus magna. Cras in mi at felis aliquet congue. Ut a est eget ligula molestie gravida. Curabitur massa. Donec eleifend, libero at sagittis mollis, tellus est malesuada tellus, at luctus turpis elit sit amet quam. Vivamus pretium ornare est.</p></blockquote>
                					
                					<h3>Header Level 3</h3>
                					
                					<ul>
                					   <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
                					   <li>Aliquam tincidunt mauris eu risus.</li>
                					</ul>', $_POST['d-content']);
                					
                					$pcontent = $_POST['d-content'];
                					
                				}
                				
                				$params = array( 
                					'post_type' => $_GET["post_type"],
                					'post_status' => 'publish',
                					'post_parent' => $parent,
                					'post_title' => rtrim($rres[5]),
                					'post_content' => $pcontent
                				);
                				
                				global $wpdb;
                				
                				$params['menu_order'] = $wpdb->get_var("SELECT MAX(menu_order)+1 AS menu_order FROM {$wpdb->posts} WHERE post_type='".$_GET["post_type"]."'");
                				
                				$wpdb->flush();
                				
                				$newpage[$rres[2]] = wp_insert_post($params);
                				
                				//form submitted
                				$tax_query = array();
                				
                				if(count($_POST["taxonomy"]) > 0) {
                				
                    				foreach($_POST["taxonomy"] as $taxx) {
                    				
                    				    $this_cat = explode("|", $taxx);
                    				    
                                        wp_set_object_terms($newpage[$rres[2]], $this_cat[1], $this_cat[0], true);
                    				    
                    				    //$tax_query[$this_cat[0]][] = $this_cat[1];
                    				    
                    				}
                    			
                    			}
                				
                				
                				
                				
                			}
                		} 
                		
                		echo '<script type="text/javascript">window.location=\'admin.php?page=bulk-creator&saved=1&post_type='.$_GET['post_type'].'\';</script>';
                		
                	} 
                	
                }
	        
	        if(isset($_GET['post_type']) && $_GET['post_type'] !='') { 
	        
	            $type = get_post_type_object($_GET["post_type"]);
	        
	        
	        
	        if(isset($_GET["saved"])) { ?>
	        
	            
	            <div id="message" class="updated below-h2">
	            
	                <p><?php echo $type->labels->name; ?> Added!</p>
	                
	            </div>
	            
	        
	        <?php } ?>
	        
	        
	        <div id="poststuff">
	        
	            <form id="bulk-add" method="post" action="?page=<?php echo $_GET['page']; ?>&post_type=<?php echo $_GET['post_type']; ?>">
	            
	            <div class="add-page postbox">
	                
    	            <h3>Create New <span class="wpblue"><?php echo $type->labels->name; ?></span></h3>
    	            
    	            <div class="wrap">
                            
                            <label><strong><?php echo $type->labels->singular_name; ?> Title(s):</strong> 
                                <textarea name="titles" id="titles" class="titles"></textarea>
                                <span>Enter comma separated post titles (e.g.:  Post1, Post2, Post3)</span>
                            </label>
                            
                            
                            <?php
                            
                                if(is_post_type_hierarchical( $_GET['post_type'] )) {
                            
                            ?>
                            
                                <label>
                                    <strong><?php echo $type->labels->singular_name; ?> Parent:</strong>
                                    <?php wp_dropdown_pages('sort_column=menu_order&post_status=draft,publish&show_option_none=(No Parent)&post_type='.$_GET['post_type']); ?>
                                    <span>Choose a parent for these <?php echo $type->labels->name; ?></span>
                                </label>
                                
                                <br>
                            
                            <?php } ?>
                            
                            
                            <label><strong>Default Content:</strong>
                                <textarea name="d-content" id="d-content" class="d-content"></textarea>
                                <span>Enter default content here.  Merge tags include: <abbr title="Inserts the page title wrapped in an h2">[pagetitle]</abbr>, <abbr title="Inserts a variety of dummy content">[lipsum]</abbr></span>
                            </label>

                            
                            <div class="term-list">
                                <?php 
                                
                                    $tax = get_object_taxonomies($_GET['post_type']); 
                                    
                                    foreach($tax as $tax_name) {
                                    
                                        if(is_taxonomy_hierarchical($tax_name)) {
                                    
                                            echo '<h4>' . ucwords(str_replace(array("_", "-"), " ", $tax_name)) . '</h4>';
                                        
                                            $t_args = array(
                                                "hide_empty" => false
                                            );  
                                            
                                            $terms = get_terms($tax_name, $t_args);
                                            
                                            foreach($terms as $term) {
                                            
                                                echo '<label><input type="checkbox" name="taxonomy[]" value="'.$tax_name.'|'.$term->slug.'">' . $term->name . '</label>';
                                            
                                            }
                                            
                                        }
                                    
                                    }
                                
                                ?>
                            </div>
                            
                            
                            <textarea id="pages-list" name="pages-list" style="display:none;"></textarea>
                            
                            <input class="button button-large add-posts-button" type="button" id="add-pages" value="Add <?php echo $type->labels->name; ?>">
                        
                    </div>
                
                </div><!-- add-page -->
                
                
                <div class="page-list postbox">
                       
                        <h3>Current <?php echo $type->labels->name; ?></h3>
                    
                        <div class="wrap">
                    
                            <ul>
                            <?php
                                
                                if(is_post_type_hierarchical( $_GET['post_type'] )) {
                                    
                                    $args = array(
                                        'title_li' => '',
                                        'post_status' => 'publish,draft',
                                        'echo' => 0,
                                        'post_type' => $_GET['post_type'],
                                        'posts_per_page' => -1
                                    
                                    );
                                        	                    
                            	
                            	    // strip links off of <li>s
                                	echo preg_replace('/<a[^>]*>([^<]*)<\/a>/','\\1',wp_list_pages($args));
                            	
                            	
                            	} else {
                            	
                                    $args = array(
                                        'post_status' => 'publish,draft',
                                        'post_type' => $_GET['post_type'],
                                        'hierarchical' => '0',
                                        'posts_per_page' => -1
                                    );
                                    
                                	$post_list = get_posts($args);
                            
                                	foreach($post_list as $p) {
                                	
                                	    echo '<li class="page_item page-item-'.$p->ID.'">' . $p->post_title . '</li>';
                                	
                                	}
                            
                            	
                            	}
                            
                                ?>
                            
                            </ul>
                            
                            <input type="submit" class="button button-primary button-large update-site" value="Apply Changes">
                            
                        </div>
                    
                
                    </div><!-- page-list -->
                    
                    
                    </form>
                
                
	        </div><!-- poststuff -->
	        
        
        <?php 
        
        } 
	    
	echo '</div>';
}