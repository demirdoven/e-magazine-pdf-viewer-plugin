<?php
/*
Plugin Name: E-Gazete Eklentisi
Plugin URI: http://devorion.work/
Description: Günlük Gazete Eklentisi
Version: 1.0
Author: Selman Demirdoven
Author URI: http://devorion.work
Licence: GPLv2 or later
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ){
	exit;
}

if ( ! defined( 'GUNLUK_GAZETE_PLG_DIR' ) ) {
	define( 'GUNLUK_GAZETE_PLG_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'GUNLUK_GAZETE_PLG_URL' ) ) {
	define( 'GUNLUK_GAZETE_PLG_URL', plugin_dir_url( __FILE__ ) );
}

function gunluk_gazete_admin_scripts(){
	//wp_enqueue_style('drp-custom-style-admin', DRP_PLUGIN_URL . 'admin/css/admin.css', '1.0.0', true);
	//wp_enqueue_script('drp-auto-complete', DRP_PLUGIN_URL . 'admin/js/auto-complete.js', array( 'suggest' ), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'gunluk_gazete_admin_scripts');

function gunluk_gazete_front_scripts() {
	//wp_enqueue_style('drp-custom-style', DRP_PLUGIN_URL . 'assets/css/drp-template.css');
	if ( ! did_action( 'wp_enqueue_media' ) ) {
					wp_enqueue_media();
			}
	wp_enqueue_style('jquery-ui-datepicker', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', array(), '1.8.5', 'all');
	wp_enqueue_script('egazete-custom-script-front', GUNLUK_GAZETE_PLG_URL . 'custom.js', array('jquery', 'jquery-ui-datepicker'), '21.06.2017', 'all');
	//wp_enqueue_script('pdf-js', 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.3.200/pdf.min.js', array('jquery'), '21.06.2017', true);
	wp_localize_script( 'egazete-custom-script-front', 'frontend_ajax_object',
        array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'gunluk_gazete_front_scripts' );

function eklee(){
	?>
	
	<style>
		#my_pdf_viewer {
			margin: 10px 0;
		}
		#canvas_container {
			width: 100%;
			max-width: 100%;
			height: 1000px;
			overflow: auto;
		}
		#canvas_container {
			background: #333;
			text-align: center;
			border: solid 3px;
		}
input#current_page {
    width: 80px;
    text-align: center;
}		
	</style>
<?php
}
add_action('wp_head', 'eklee');

function gunluk_gazete_sc ($atts, $content = null) {
	ob_start();
	date_default_timezone_set('Europe/Istanbul');
	
	$argzzz = array(
		'post_type'     =>  'egazete',
		'post_status'   =>  'publish',
		'posts_per_page'=> 	1,
		'order'			=> 'desc',
	);
	$egazete_loop = new WP_Query($argzzz);
	while($egazete_loop->have_posts()) : $egazete_loop->the_post();
		global $post;
		//$pdf_linki = get_post_meta( $post->ID, 'pdf_dosy', true );
		$drive_link = get_post_meta( $post->ID, 'drive_link', true );
		?>
		<div style="margin-bottom: 4px;">
			Tarih: <input type="text" id="tarih_sec" value="<?php echo get_the_date("d-m-Y"); ?>"/>
			<img src="<?php echo GUNLUK_GAZETE_PLG_URL.'loader.gif'; ?>" class="loaderr" style="display: none;width: 34px; line-height: 1; vertical-align: top; margin-left: 5px;"/>
			
		</div>
		<?php
		$pdf = get_post_meta($post->ID, 'wp_custom_attachment', true);
    
		if( $pdf && $pdf['url']!=='' ){
			?>
			<style>
#the-canvas {
  border:1px solid black;
  width: 100%;
  margin-top: 10px;
}
</style>				
<script src="//mozilla.github.io/pdf.js/build/pdf.js"></script>

<div>
  <button id="prev">Geri</button>
  <button id="next">İleri</button>
  &nbsp; &nbsp;
  <span>Sayfa: <span id="page_num"></span> / <span id="page_count"></span></span>
</div>

<canvas id="the-canvas"></canvas>

<script>
var url = '<?php echo $pdf["url"]; ?>';
var pdfjsLib = window['pdfjs-dist/build/pdf'];
pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';

var pdfDoc = null,
    pageNum = 1,
    pageRendering = false,
    pageNumPending = null,
    scale = 1,
    canvas = document.getElementById('the-canvas'),
    ctx = canvas.getContext('2d');

function renderPage(num) {
  pageRendering = true;
  pdfDoc.getPage(num).then(function(page) {
    var viewport = page.getViewport({scale: scale});
    canvas.height = viewport.height;
    canvas.width = viewport.width;

    var renderContext = {
      canvasContext: ctx,
      viewport: viewport
    };
    var renderTask = page.render(renderContext);

    renderTask.promise.then(function() {
      pageRendering = false;
      if (pageNumPending !== null) {
        renderPage(pageNumPending);
        pageNumPending = null;
      }
    });
  });

  document.getElementById('page_num').textContent = num;
}

function queueRenderPage(num) {
  if (pageRendering) {
    pageNumPending = num;
  } else {
    renderPage(num);
  }
}

function onPrevPage() {
  if (pageNum <= 1) {
    return;
  }
  pageNum--;
  queueRenderPage(pageNum);
}
document.getElementById('prev').addEventListener('click', onPrevPage);

function onNextPage() {
  if (pageNum >= pdfDoc.numPages) {
    return;
  }
  pageNum++;
  queueRenderPage(pageNum);
}
document.getElementById('next').addEventListener('click', onNextPage);

pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
  pdfDoc = pdfDoc_;
  document.getElementById('page_count').textContent = pdfDoc.numPages;

  renderPage(pageNum);
});

</script>
			<?php
		}
		
	endwhile;
	wp_reset_postdata();
	
	return ob_get_clean();
}
add_shortcode('gunluk_gazete', 'gunluk_gazete_sc');

function egazete_cpt(){

	$singular = 'E-Gazete';
	$plural = 'E-Gazeteler';
	$labels = array(
		'name' 				 => $singular,
		'singular_name' 	 => $singular,
		'menu_name'          => $plural,
		'add_name' 			 => 'Yeni '. $singular,
		'add_new_item' 		 => 'Yeni '. $singular,
		'edit' 				 => 'Düzenle',
		'edit_item' 		 => 'E-gazeteyi Düzenle',
		'new_item' 			 => 'Yeni ' . $singular,
		'view' 				 => 'İncele ' . $singular,
		'view_item' 		 => 'İncele ' . $singular,
		'all_items'			 => 'Tüm '.$plural,
		'search_term' 		 => 'Ara ' . $plural,
		'parent' 			 => 'Üst ' . $singular,
		'not_found' 		 => 'Hiç ' . $singular . ' yok',
		'not_found_in_trash' => 'Hiç ' . $singular . ' yok',
	);

	$args = array(
			'labels'              => $labels,
	        'public'              => true,
	        'publicly_queryable'  => true,
	        'exclude_from_search' => false,
	        'show_in_nav_menus'   => true,
	        'show_ui'             => true,
	        'show_in_menu'        => true,
	        'show_in_admin_bar'   => true,
	        'menu_position'       => 10,
	        'menu_icon'           => 'dashicons-slides',
	        'can_export'          => true,
	        'delete_with_user'    => false,
	        'hierarchical'        => false,
	        'has_archive'         => true,
	        'query_var'           => true,
	        'capability_type'     => 'page',
	        'map_meta_cap'        => true,
	        'rewrite'             => array(
	        	'slug' 		 => 'arsiv',
	        	'with_front' => true,
	        	'pages' 	 => true,
	        	'feeds' 	 => true,
	        ),
	        'supports'            => array(
				'title',
			),
	);
	register_post_type( 'egazete', $args);
}
add_action( 'init', 'egazete_cpt' );

function pdf_init(){
    add_meta_box("my-pdf", "PDF Dosyası", "pdf_link_cb", "egazete", "normal", "low");
}
add_action("add_meta_boxes", "pdf_init");

function pdf_link_cb(){
	wp_nonce_field(plugin_basename(__FILE__), 'wp_custom_attachment_nonce');
    global $post;
    $custom  = get_post_custom($post->ID);
    //$pdf_dosy = $custom["pdf_dosy"][0];
	//$urll = get_post_meta( $post->ID, 'saruman', true );
    //$drive_link = $custom["drive_link"][0];
	
	$pdf = get_post_meta($post->ID, 'wp_custom_attachment', true);
    
	if( $pdf && $pdf['url']!=='' ){
		echo '<div><a target="_blank" href="'.$pdf['url'].'">Kayıtlı PDF dosyasını görüntülemek için tıklayın.</a></div></br>';
	}
    $html .= '<input type="file" id="wp_custom_attachment" name="wp_custom_attachment" value="" size="25" />';
    echo $html;
	
}
function update_edit_form() {
    echo ' enctype="multipart/form-data"';
}
add_action('post_edit_form_tag', 'update_edit_form');



function save_custom_meta_data($id) {
 
    if(!wp_verify_nonce($_POST['wp_custom_attachment_nonce'], plugin_basename(__FILE__))) {
      return $id;
    }
       
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return $id;
    }
       
    if('page' == $_POST['post_type']) {
      if(!current_user_can('edit_page', $id)) {
        return $id;
      }
    } else {
        if(!current_user_can('edit_page', $id)) {
            return $id;
        }
    }
    
     if(!empty($_FILES['wp_custom_attachment']['name'])) {
         
        $supported_types = array('application/pdf');
         
        $arr_file_type = wp_check_filetype(basename($_FILES['wp_custom_attachment']['name']));
        $uploaded_type = $arr_file_type['type'];
         
        if(in_array($uploaded_type, $supported_types)) {
 
            $upload = wp_upload_bits($_FILES['wp_custom_attachment']['name'], null, file_get_contents($_FILES['wp_custom_attachment']['tmp_name']));
     
            if(isset($upload['error']) && $upload['error'] != 0) {
                wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
            } else {
                add_post_meta($id, 'wp_custom_attachment', $upload);
                update_post_meta($id, 'wp_custom_attachment', $upload);     
            }
 
        } else {
            wp_die("The file type that you've uploaded is not a PDF.");
        }
         
    }
     
}
add_action('save_post', 'save_custom_meta_data');

function save_pdf_link(){
    global $post;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){ return $post->ID; }
	
	
	if( !empty( $_FILES ) ){
        $file=$_FILES['file'];
        $attachment_id = upload_user_file( $file );
		update_post_meta($post->ID, "saruman", $attachment_id);
    }
	
	
    /*update_post_meta($post->ID, "pdf_dosy", $_POST["pdf_dosy"]);*/
	
	/*
	if( $_POST['drive_link'] ){
		update_post_meta($post->ID, "drive_link", $_POST["drive_link"]);
	}
    */
}
//add_action('save_post', 'save_pdf_link');

function pdf_css() {
    echo '<style type="text/css">
    .pdf_select{
        font-weight:bold;
        background:#e5e5e5;
        }
    .pdf_count{
        font-size:9px;
        color:#0066ff;
        text-transform:uppercase;
        background:#f3f3f3;
        border-top:solid 1px #e5e5e5;
        padding:6px 6px 6px 12px;
        margin:0px -6px -8px -6px;
        -moz-border-radius:0px 0px 6px 6px;
        -webkit-border-radius:0px 0px 6px 6px;
        border-radius:0px 0px 6px 6px;
        }
    .pdf_count span{color:#666;}
        </style>';
}
add_action( 'admin_head', 'pdf_css' );

function pdf_file_url(){
    global $wp_query;
    $custom = get_post_custom($wp_query->post->ID);
    echo $custom['pdf_dosy'][0];
}

function egazete_template( $template ) {
    $post_type = 'egazete';

    if ( is_singular( $post_type ) && ! file_exists( get_stylesheet_directory() . '/single-egazete.php' ) )
        $template = plugin_dir_path(__FILE__) .'single-egazete.php';

    return $template;
}
add_filter( 'template_include', 'egazete_template' );

function ilgili_tarihin_egazetesine_yonlendir(){
	$date = @$_REQUEST['date'];
	
	if( $date != '' ){
		
		$date_array = explode('-', $date);
			
		$argzzz = array(
			'post_type'     =>  'egazete',
			'post_status'   =>  'publish',
			'posts_per_page'=> 	1,
			'order'			=> 'desc',
			'date_query' => array(
				array(
					'year'  => $date_array[2],
					'month' => $date_array[1],
					'day'   => $date_array[0],
				),
			),
		);
		
		$url = 'none';
		
		$custom_query = new WP_Query($argzzz);
		
		if( !empty($custom_query) ){
			while($custom_query->have_posts()) : $custom_query->the_post();
				
				$egazete_id = get_the_ID();
				$url = get_permalink($egazete_id);
				
			endwhile;wp_reset_postdata();
			
		}else{
			
			$url = 'none';
			
		}
	}else{
		$url = 'none';
	}
	
	echo $url;
	wp_die();
}
add_action( 'wp_ajax_ilgili_tarihin_egazetesine_yonlendir', "ilgili_tarihin_egazetesine_yonlendir" );
add_action( 'wp_ajax_nopriv_ilgili_tarihin_egazetesine_yonlendir', "ilgili_tarihin_egazetesine_yonlendir" );

/*****************************************/

/*
function egazete_admin_columns( $columns ) {
  unset(
    $columns['id'],
	$columns['featured_image']
  );

  return $columns;
}
add_filter( 'manage_egazete_posts_columns', 'egazete_admin_columns' );

function egazete_column_names( $columns ){
	$columns['title'] = 'Başlık';
	$columns['onizleme'] = 'Önizleme';
	$columns['date'] = 'Tarih';
	return $columns;
}
add_filter('manage_egazete_posts_columns', 'egazete_column_names', 5);

function egazete_column_content( $column, $id ){

	if ( $column == 'onizleme' ) {
		$drive_link = get_post_meta( $id, 'drive_link', true );
		if( $drive_link ){
			
			$url = $drive_link;
			$parts = parse_url($url);
			parse_str($parts['query'], $query);
			$drive_id = $query['id'];
			$usp = $query['usp'];
			
			if( $drive_id ){
				$last = $drive_id;
			}
			if( $usp && $usp == 'sharing' ){
				
				$xx = explode( '/', $url );
				$last = $xx[5];
			}
			?>
			<img src="https://drive.google.com/thumbnail?id=<?php echo $last; ?>" style="max-width: 80px;"/>
			<?php
			
		}
	}
	
}
add_action('manage_egazete_posts_custom_column', 'egazete_column_content', 5, 2);
*/
?>