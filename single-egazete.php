<?php
/**
 * The Template for displaying pages.
 *
 * @license For the full license information, please view the Licensing folder
 * that was distributed with this source code.
 *
 * @package Bimber_Theme 4.10
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

get_header();
?>

	<div class="g1-primary-max">
		<div id="content" role="main">

			<?php
			while ( have_posts() ) : the_post();

				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemscope=""
						itemtype="<?php echo esc_attr( bimber_get_entry_microdata_itemtype() ); ?>">

					<header class="page-header page-header-01 g1-row g1-row-layout-page">
						<div class="g1-row-inner">
							<div class="g1-column">
									<h1 class="g1-alpha g1-alpha-2nd page-title">E-GAZETE</h1>
							</div>
						</div>
						<div class="g1-row-background"></div>
					</header>

					<div <?php bimber_render_page_body_class(); ?>>
						<div class="g1-row-background">
						</div>
						<div class="g1-row-inner">

							<div id="primary" class="g1-column">
								<?php
								bimber_render_entry_featured_media( array(
									'size'          => 'bimber-grid-2of3',
									'class'         => 'entry-featured-media-main',
									'use_microdata' => true,
									'apply_link'    => false,
									'allow_video'   => true,
								) );
								?>

								<div class="entry-content" <?php bimber_render_microdata( array( 'itemprop' => 'text' ) ); ?>>
									<?php
										date_default_timezone_set('Europe/Istanbul');
										global $post;
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
										?>
										<!--<iframe src="https://drive.google.com/file/d/<?php /*echo $last;*/ ?>/preview" width="100%" height="1200"></iframe>-->
								</div><!-- .entry-content -->
							</div>

						</div>
					</div>

				</article>
				<?php
			endwhile;
			?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer();
