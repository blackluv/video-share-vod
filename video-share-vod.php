<?php
/*
Plugin Name: Video Share VOD
Plugin URI: http://www.videosharevod.com
Description: <strong>Video Share / Video on Demand (VOD)</strong> plugin allows WordPress users to share videos and others to watch on demand. Integrates with VideoWhisper Live Streaming.
Version: 1.1.2
Author: VideoWhisper.com
Author URI: http://www.videowhisper.com/
Contributors: videowhisper, VideoWhisper.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists("VWvideoShare"))
{
	class VWvideoShare {

		function VWvideoShare() { //constructor

		}

		static function install() {
			// do not generate any output here
			VWvideoShare::setupOptions();
			flush_rewrite_rules();
		}

		// Register Custom Post Type
		function video_post() {

			//only if missing
			if (post_type_exists('video')) return;

			$labels = array(
				'name'                => _x( 'Videos', 'Post Type General Name', 'text_domain' ),
				'singular_name'       => _x( 'Video', 'Post Type Singular Name', 'text_domain' ),
				'menu_name'           => __( 'Videos', 'text_domain' ),
				'parent_item_colon'   => __( 'Parent Video:', 'text_domain' ),
				'all_items'           => __( 'All Videos', 'text_domain' ),
				'view_item'           => __( 'View Video', 'text_domain' ),
				'add_new_item'        => __( 'Add New Video', 'text_domain' ),
				'add_new'             => __( 'New Video', 'text_domain' ),
				'edit_item'           => __( 'Edit Video', 'text_domain' ),
				'update_item'         => __( 'Update Video', 'text_domain' ),
				'search_items'        => __( 'Search Videos', 'text_domain' ),
				'not_found'           => __( 'No Videos found', 'text_domain' ),
				'not_found_in_trash'  => __( 'No Videos found in Trash', 'text_domain' ),
			);

			$args = array(
				'label'               => __( 'video', 'text_domain' ),
				'description'         => __( 'Video Videos', 'text_domain' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', ),
				'taxonomies'          => array( 'category', 'post_tag' ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'post',
			);

			register_post_type( 'video', $args );

			// Add new taxonomy, make it hierarchical (like categories)
			$labels = array(
				'name'              => _x( 'Archives', 'taxonomy general name' ),
				'singular_name'     => _x( 'Archive', 'taxonomy singular name' ),
				'search_items'      => __( 'Search Archives' ),
				'all_items'         => __( 'All Archives' ),
				'parent_item'       => __( 'Parent Archive' ),
				'parent_item_colon' => __( 'Parent Archive:' ),
				'edit_item'         => __( 'Edit Archive' ),
				'update_item'       => __( 'Update Archive' ),
				'add_new_item'      => __( 'Add New Archive' ),
				'new_item_name'     => __( 'New Archive Name' ),
				'menu_name'         => __( 'Archives' ),
			);

			$args = array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'archive' ),
			);

			register_taxonomy( 'archive', array( 'video' ), $args );

			flush_rewrite_rules();
		}

		function video_delete($video_id)
		{
			if (get_post_type( $video_id ) != 'video') return;

			//delete source video
			$videoPath = get_post_meta($post_id, 'video-source-file', true);
			if (file_exists($videoPath)) unlink($videoPath);

			//delete all generated video files
			$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
			if ($videoAdaptive) $videoAlts = $videoAdaptive;
			else $videoAlts = array();

			foreach ($videoAlts as $alt)
			{
				if (file_exists($alt['file'])) unlink($alt['file']);
			}
		}

		function adminMenu()
		{
			$options = get_option('VWvideoShareOptions');

			add_menu_page('Video Share VOD', 'Video Share VOD', 'manage_options', 'video-share', array('VWvideoShare', 'adminOptions'), '',81);
			add_submenu_page("video-share", "Video Share VOD", "Options", 'manage_options', "video-share", array('VWvideoShare', 'adminOptions'));
			if (class_exists("VWliveStreaming")) add_submenu_page('video-share', 'Live Streaming', 'Live Streaming', 'manage_options', 'video-share-ls', array('VWvideoShare', 'adminLiveStreaming'));
			add_submenu_page("video-share", "Documentation", "Documentation", 'manage_options', "video-share-docs", array('VWvideoShare', 'adminDocs'));
		}

		/*
		function updatePages()
		{
			$options = get_option('VWvideoShareOptions');

			//if not disabled create
			if ($options['disablePages']=='0')
			{
				global $user_ID;
				$page = array();
				$page['post_type']    = 'page';
				$page['post_content'] = '[videowhisper_video_import]';
				$page['post_parent']  = 0;
				$page['post_author']  = $user_ID;
				$page['post_status']  = 'publish';
				$page['post_title']   = 'Import Videos';
				$page['comment_status'] = 'closed';

				$page_id = get_option( "vwvs_page_import" );
				if ($page_id>0) $page['ID'] = $page_id;

				$pageid = wp_insert_post ($page);
				update_option( "vwvs_page_import", $pageid);
			}

		}

		function deletePages()
		{
			$options = get_option( 'VWvideoShareOptions' );

			if ($options['disablePage'])
			{
				$page_id = get_option( "vwvs_page_import" );
				if ($page_id > 0)
				{
					wp_delete_post($page_id);
					update_option( "vwvs_page_import", -1);
				}
			}

		}

*/


		function init()
		{
			$options = get_option('VWvideoShareOptions');


			add_action( 'wp_enqueue_scripts', array('VWvideoShare','scripts') );

			/* Fire our meta box setup function on the post editor screen. */
			add_action( 'load-post.php', array('VWvideoShare', 'post_meta_boxes_setup' ) );
			add_action( 'load-post-new.php', array( 'VWvideoShare', 'post_meta_boxes_setup' ) );

			add_filter('manage_video_posts_columns', array( 'VWvideoShare', 'columns_head_video') , 10);
			add_filter( 'manage_edit-video_sortable_columns', array('VWvideoShare', 'columns_register_sortable') );
			add_filter( 'request', array('VWvideoShare', 'duration_column_orderby') );
			add_action('manage_video_posts_custom_column', array( 'VWvideoShare', 'columns_content_video') , 10, 2);
			add_filter( 'parse_query', array( 'VWvideoShare', 'post_edit_screen') );

			add_action( 'before_delete_post',  array( 'VWvideoShare','video_delete') );

			//video post page
			add_filter( "the_content", array('VWvideoShare','video_page'));

			if (class_exists("VWliveStreaming"))  if ($options['vwls_channel']) add_filter( "the_content", array('VWvideoShare','channel_page'));

				//shortcodes
				add_shortcode('videowhisper_player', array( 'VWvideoShare', 'shortcode_player'));
			add_shortcode('videowhisper_videos', array( 'VWvideoShare', 'shortcode_videos'));

			//ajax videos
			add_action( 'wp_ajax_vwvs_videos', array('VWvideoShare','vwvs_videos'));
			add_action( 'wp_ajax_nopriv_vwvs_videos', array('VWvideoShare','vwvs_videos'));

			//Live Streaming support
			if (class_exists("VWliveStreaming")) if ($options['vwls_archive'])
				{
					add_filter('vw_ls_manage_channel', array('VWvideoShare', 'vw_ls_manage_channel' ), 10, 2);
					add_filter('vw_ls_manage_channels_head', array('VWvideoShare', 'vw_ls_manage_channels_head' ));
				}

			//check db and update if necessary
			/*
			$vw_db_version = "0.0";

			$installed_ver = get_option( "vwvs_db_version" );
			if( $installed_ver != $vw_db_version )
			{
				$tab_formats = $wpdb->prefix . "vwvs_formats";
				$tab_process = $wpdb->prefix . "vwvs_process";

				global $wpdb;
				$wpdb->flush();
				$sql = "";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
				if (!$installed_ver) add_option("vwvs_db_version", $vw_db_version);
				else update_option( "vwvs_db_version", $vw_db_version );
			}
			*/


		}

		function scripts()
		{
			wp_enqueue_script("jquery");

		}

		function shortcode_videos($atts)
		{

			$options = get_option('VWvideoShareOptions');

			$atts = shortcode_atts(
				array(
					'perpage'=> $options['perPage'],
					'perrow' => '',
					'archive' => ''
				),
				$atts, 'videowhisper_videos');


			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwvs_videos&pp=' . $atts['perpage'] . '&pr=' . $atts['perrow'] . '&archive=' . urlencode($atts['archive']);

			$htmlCode = <<<HTMLCODE
<script>
var aurl = '$ajaxurl';
var \$j = jQuery.noConflict();

	function loadVideos(){
		\$j.ajax({
			url: aurl,
			success: function(data) {
				\$j("#videowhisperVideos").html(data);
			}
		});
	}

	\$j(function(){
		loadVideos();
		setInterval("loadVideos()", 20000);
	});

</script>

<div id="videowhisperVideos">
    Loading Videos...
</div>

HTMLCODE;

			$htmlCode .= html_entity_decode(stripslashes($options['customCSS']));

			return $htmlCode;
		}

		function vwvs_videos()
		{
			$options = get_option('VWvideoShareOptions');

			$perPage = (int) $_GET['pp'];
			if (!$perPage) $perPage = $options['perPage'];

			$archive = sanitize_file_name($_GET['archive']);

			$page = (int) $_GET['p'];
			$offset = $page * $perPage;

			$perRow = (int) $_GET['pr'];


			$args=array(
				'post_type' => 'video',
				'post_status' => 'publish',
				'posts_per_page' => $perPage,
				'offset'           => $offset,
				'orderby'          => 'post_date',
				'order'            => 'DESC',
			);

			if ($archive)  $args['archive'] = $archive;

			$postslist = get_posts( $args );
			ob_clean();
			//output

			if (count($postslist)>0)
			{
				$k = 0;
				foreach ( $postslist as $item )
				{


					if ($perRow) if ($k) if ($k % $perRow == 0) echo '<br>';

							$videoDuration = get_post_meta($item->ID, 'video-duration', true);
						$imagePath = get_post_meta($item->ID, 'video-thumbnail', true);

					echo '<div class="videowhisperVideo">';
					echo '<div class="videowhisperTitle">' . $item->post_title. '</div>';
					echo '<div class="videowhisperTime">' . VWvideoShare::humanDuration($videoDuration) . '</div>';
					echo '<div class="videowhisperDate">' . VWvideoShare::humanAge(time() - strtotime($item->post_date)) . '</div>';


					if (!$imagePath || !file_exists($imagePath))
					{
						$imagePath = plugin_dir_path( __FILE__ ) . 'no_video.png';
						VWvideoShare::updatePostThumbnail($item->ID);
					}

					echo '<a href="' . get_permalink($item->ID) . '"><IMG src="' . VWvideoShare::path2url($imagePath) . $noCache .'" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px"></a>';

					echo '</div>
					';

					$k++;
				}


				$ajaxurl = admin_url() . 'admin-ajax.php?action=vwvs_videos&pp=' . $perPage .  '&pr=' .$perRow. '&archive=' . urlencode($archive);

				echo "<BR>";
				if ($page>0) echo ' <a class="videowhisperButton" href="JavaScript: void()" onclick="aurl=\'' . $ajaxurl.'&p='.($page-1). '\'; loadVideos();">Previous</a> ';

				if (count($items) == $perPage) echo ' <a class="videowhisperButton" href="JavaScript: void()" onclick="aurl=\'' . $ajaxurl.'&p='.($page+1). '\'; loadVideos();">Next</a> ';

			} else echo "No videos.";

			//output end
			die;

		}

		function shortcode_player($atts)
		{
			$atts = shortcode_atts(array('video' => '0'), $atts, 'videowhisper_player');

			$video_id = intval($atts['video']);
			if (!$video_id) return 'shortcode_player: Missing video id!';

			$video = get_post($video_id);
			if (!$video) return 'shortcode_player: Video #'. $video_id . ' not found!';

			//snap
			$imagePath = get_post_meta($video_id, 'video-snapshot', true);
			if ($imagePath)
				if (file_exists($imagePath))
				{
					$imageURL = VWvideoShare::path2url($imagePath);
					$posterVar = '&poster=' . urlencode($imageURL);
					$posterProp = ' poster="' . $imageURL . '"';
				} else VWvideoShare::updatePostThumbnail($update_id);


			$options = get_option( 'VWvideoShareOptions' );

			$player = $options['player_default'];

			//Detect special conditions devices
			$iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
			$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
			$iPad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
			//$Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");
			//$webOS   = stripos($_SERVER['HTTP_USER_AGENT'],"webOS");

			$Safari  = (stripos($_SERVER['HTTP_USER_AGENT'],"Safari") && !stripos($_SERVER['HTTP_USER_AGENT'], 'Chrome'));

			if ($Safari) $player = $options['player_default_safari'];

			if ($iPod || $iPhone || $iPad) $player = $options['player_default_ios'];

			switch ($player)
			{
			case 'strobe':

				$videoPath = get_post_meta($video_id, 'video-source-file', true);
				$videoURL = VWvideoShare::path2url($videoPath);

				$player_url = plugin_dir_url(__FILE__) . 'strobe/StrobeMediaPlayback.swf';
				$flashvars ='src=' .urlencode($videoURL). '&autoPlay=false' . $posterVar;

				$htmlCode .= '<object class="videoPlayer" width="480" height="360" type="application/x-shockwave-flash" data="' . $player_url . '"> <param name="movie" value="' . $player_url . '" /><param name="flashvars" value="' .$flashvars . '" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="wmode" value="direct" /></object>';
				break;

			case 'strobe-rtmp':
				$videoPath = get_post_meta($video_id, 'video-source-file', true);
				$ext = pathinfo($videoPath, PATHINFO_EXTENSION);


				if (in_array($ext, array('flv','mp4','m4v')))
				{
					//use source if compatible
					$stream = VWvideoShare::path2stream($videoPath);
				}
				else
				{
					//use conversion
					$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
					if ($videoAdaptive) $videoAlts = $videoAdaptive;
					else $videoAlts = array();

					if ($alt = $videoAlts['mobile'])
						if (file_exists($alt['file']))
						{
							$ext = pathinfo($alt['file'], PATHINFO_EXTENSION);
							$stream = VWvideoShare::path2stream($alt['file']);

						}else $htmlCode .= 'Mobile adaptive format file missing for this video!';
					else $htmlCode .= 'Mobile adaptive format missing for this video!';

				}

				if ($stream)
				{

					if ($ext == 'mp4') $stream = 'mp4:' . $stream;

					$player_url = plugin_dir_url(__FILE__) . 'strobe/StrobeMediaPlayback.swf';
					$flashvars ='src=' .urlencode($options['rtmpServer'] . '/' . $stream). '&autoPlay=false' . $posterVar;

					$htmlCode .= '<object class="videoPlayer" width="480" height="360" type="application/x-shockwave-flash" data="' . $player_url . '"> <param name="movie" value="' . $player_url . '" /><param name="flashvars" value="' .$flashvars . '" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="wmode" value="direct" /></object>';
				}
				else $htmlCode .= 'Stream not found!';

				break;

			case 'html5':
				$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
				if ($videoAdaptive) $videoAlts = $videoAdaptive;
				else $videoAlts = array();

				if ($alt = $videoAlts['mobile'])
					if (file_exists($alt['file']))
					{
						$videoURL = VWvideoShare::path2url($alt['file']);

						$htmlCode .='<video width="' . $alt['width'] . '" height="' . $alt['height'] . '"  autobuffer controls="controls"' .$posterProp . '>
 <source src="' . $videoURL . '" type="' . $alt['type'] . '">
     <div class="fallback">
	    <p>You must have an HTML5 capable browser.</p>
	</div>
</video>';

					}else $htmlCode .= 'Mobile adaptive format file missing for this video!';
				else $htmlCode .= 'Mobile adaptive format missing for this video!';
				break;

			case 'hls':

				//use conversion
				$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
				if ($videoAdaptive) $videoAlts = $videoAdaptive;
				else $videoAlts = array();

				if ($alt = $videoAlts['mobile'])
					if (file_exists($alt['file']))
					{
						$stream = VWvideoShare::path2stream($alt['file']);

					}else $htmlCode .= 'Mobile adaptive format file missing for this video!';
				else $htmlCode .= 'Mobile adaptive format missing for this video!';

				if ($stream)
				{
					$stream = 'mp4:' . $stream;

					$streamURL = $options['hlsServer'] . '_definst_/' . $stream . '/playlist.m3u8';

					$htmlCode .='<video width="' . $alt['width'] . '" height="' . $alt['height'] . '"  autobuffer controls="controls"' .$posterProp . '>
 <source src="' . $streamURL . '" type="' . $alt['type'] . '">
     <div class="fallback">
	    <p>You must have an HTML5 capable browser with HLS support (Ex. Safari) to open this live stream: ' . $streamURL . '</p>	</div>
</video>';

				} else $htmlCode .= 'Stream not found!';

				break;
			}


			return $htmlCode;
		}

		function video_page($content)
		{
			if (!is_single()) return $content;
			$postID = get_the_ID() ;

			if (get_post_type( $postID ) != 'video') return $content;

			$addCode = '' . '[videowhisper_player video="' . $postID . '"]';

			return $content . $addCode ;

		}

		function channel_page($content)
		{
			if (!is_single()) return $content;
			$postID = get_the_ID() ;

			if (get_post_type( $postID ) != 'channel') return $content;

			$channel = get_post( $postID );

			$addCode = '<h3>Channel Archive</h3>' . '[videowhisper_videos archive="' . $channel->post_name . '"]';

			return $addCode . $content;

		}

		function convertVideo($post_id, $overwrite = false)
		{

			if (!$post_id) return;

			$videoPath = get_post_meta($post_id, 'video-source-file', true);
			if (!$videoPath) return;

			$videoAdaptive = get_post_meta($post_id, 'video-adaptive', true);

			if ($videoAlts)
				if (is_array($videoAdaptive)) $videoAlts = $videoAdaptive;
				else $videoAlts = unserialize($videoAdaptive);
				else $videoAlts = array();

				$formats = array();
			$formats[0] = array
			(
				//Mobile: MP4/H.264, Baseline profile, 480×360, for wide compatibility
				'id' => 'mobile',
				'cmd' => '-s 480x360 -r 15 -vb 400k -vcodec libx264 -coder 0 -bf 0 -level 3.1 -g 30 -maxrate 440k -acodec libfaac -ac 2 -ar 22050 -ab 40k -x264opts vbv-maxrate=364:qpmin=4:ref=4',
				'width' => 480,
				'height' => 360,
				'bitrate' => 440,
				'type' => 'video/mp4',
				'extension' => 'mp4'
			);

			//HD Mobile: MP4/H.264, Main profile, 1280×720, for newer iOS devices (iPhone 4, iPad, Apple TV)


			$options = get_option( 'VWvideoShareOptions' );

			$path =  dirname($videoPath);

			foreach ($formats as $format)
				if (!$videoAlts[$format['id']] || $overwrite)
				{
					$alt = $format;
					unset($alt['cmd']);

					$newFile = md5(uniqid($post_id . $alt['id'], true))  . '.' . $alt['extension'];
					$alt['file'] = $path . '/' . $newFile;
					$logPath = $path . '/' . $post_id . '-' . $alt['id'] . '.txt';
					$cmdPath = $path . '/' . $post_id . '-' . $alt['id'] . '-cmd.txt';

					$videoAlts[$alt['id']] = $alt;

					$cmd = $options['ffmpegPath'] . ' -y '. $format['cmd'] . ' ' . $alt['file'] . ' -i ' . $videoPath . ' >&' . $logPath . ' &';

					exec($cmd, $output, $returnvalue);
					exec("echo '$cmd' >> $cmdPath", $output, $returnvalue);
				}

			update_post_meta( $post_id, 'video-adaptive', $videoAlts );

		}

		function generateSnapshots($post_id)
		{
			if (!$post_id) return;

			$videoPath = get_post_meta($post_id, 'video-source-file', true);
			if (!$videoPath) return;

			$options = get_option( 'VWvideoShareOptions' );

			$path =  dirname($videoPath);
			$imagePath =  $path . '/' . $post_id . '.jpg';
			$thumbPath =  $path . '/' . $post_id . '_thumb.jpg';
			$logPath = $path . '/' . $post_id . '-snap.txt';
			$cmdPath = $path . '/' . $post_id . '-snap-cmd.txt';

			$cmd = $options['ffmpegPath'] . ' -y -i "'.$videoPath.'" -ss 00:00:03.000 -f image2 -vframes 1 "' . $imagePath . '" >& ' . $logPath .' &';

			exec($cmd, $output, $returnvalue);
			exec("echo '$cmd' >> $cmdPath", $output, $returnvalue);

			update_post_meta( $post_id, 'video-snapshot', $imagePath );

			//probably source snap not ready, yet
			update_post_meta( $post_id, 'video-thumbnail', $thumbPath );

			list($width, $height) = VWvideoShare::generateThumbnail($imagePath, $thumbPath);
			if ($width) update_post_meta( $post_id, 'video-width', $width );
			if ($height) update_post_meta( $post_id, 'video-height', $height );
		}


		function generateThumbnail($src, $dest)
		{
			if (!file_exists($src)) return;

			$options = get_option( 'VWvideoShareOptions' );

			//generate thumb
			$thumbWidth = $options['thumbWidth'];
			$thumbHeight = $options['thumbHeight'];

			$srcImage = @imagecreatefromjpeg($src);
			if (!$srcImage) return;

			list($width, $height) = getimagesize($src);

			$destImage = imagecreatetruecolor($thumbWidth, $thumbHeight);

			imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
			imagejpeg($destImage, $dest, 95);

			//return source dimensions
			return array($width, $height);
		}


		function updatePostThumbnail($post_id, $overwrite = false)
		{
			$imagePath = get_post_meta($post_id, 'video-snapshot', true);
			$thumbPath = get_post_meta($post_id, 'video-thumbnail', true);

			if (!$imagePath) VWvideoShare::generateSnapshots($post_id);
			elseif (!file_exists($imagePath)) VWvideoShare::generateSnapshots($post_id);
			elseif ($overwrite) VWvideoShare::generateSnapshots($post_id);

			if (!$thumbPath) VWvideoShare::generateSnapshots($post_id);
			elseif (!file_exists($thumbPath)) VWvideoShare::generateThumbnail($imagePath, $thumbPath);
			else
			{
				if ($overwrite) VWvideoShare::generateThumbnail($imagePath, $thumbPath);

				if (!get_the_post_thumbnail($post_id)) //insert if missing
					{
					$wp_filetype = wp_check_filetype(basename($thumbPath), null );

					$attachment = array(
						'guid' => $thumbPath,
						'post_mime_type' => $wp_filetype['type'],
						'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $thumbPath, ".jpg" ) ),
						'post_content' => '',
						'post_status' => 'inherit'
					);

					// Insert the attachment.
					$attach_id = wp_insert_attachment( $attachment, $thumbPath, $post_id );
					set_post_thumbnail($post_id, $attach_id);
				}
				else //just update
					{
					$attach_id = get_post_thumbnail_id($post_id );
					//$thumbPath = get_attached_file($attach_id);
				}

				// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );


				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attach_id, $thumbPath );
				wp_update_attachment_metadata( $attach_id, $attach_data );

			}

		}

		function updatePostDuration($post_id, $overwrite = false)
		{
			if (!$post_id) return;

			$videoPath = get_post_meta($post_id, 'video-source-file', true);
			if (!$videoPath) return;

			$videoDuration = get_post_meta($post_id, 'video-duration', true);
			if ($videoDuration && !$overwrite) return;

			$options = get_option( 'VWvideoShareOptions' );

			$path =  dirname($videoPath);
			$logPath = $path . '/' . $post_id . '-dur.txt';
			$cmdPath = $path . '/' . $post_id . '-dur-cmd.txt';

			$cmd = $options['ffmpegPath'] . ' -y -i "'.$videoPath.'" 2>&1';

			$info = shell_exec($cmd);
			exec("echo '$info' >> $logPath", $output, $returnvalue);
			exec("echo '$cmd' >> $cmdPath", $output, $returnvalue);

			preg_match('/Duration: (.*?),/', $info, $matches);
			$duration = explode(':', $matches[1]);

			$videoDuration = intval($duration[0]) * 3600 + intval($duration[1]) * 60 + intval($duration[2]);
			if ($videoDuration) update_post_meta( $post_id, 'video-duration', $videoDuration );

			preg_match('/bitrate:\s(?<bitrate>\d+)\skb\/s/', $info, $matches);
			$videoBitrate = $matches['bitrate'];
			if ($videoBitrate) update_post_meta( $post_id, 'video-bitrate', $videoBitrate );

			$videoSize = filesize($videoPath);
			if ($videoSize) update_post_meta( $post_id, 'video-source-size', $videoSize );


			return $videoDuration;
		}

		function vw_ls_manage_channel($val, $cid)
		{
			$options = get_option( 'VWvideoShareOptions' );

			$htmlCode .= '<div class="w-actionbox color_alternate"><h4>Video Archive</h4>';

			$channel = get_post( $cid );
			$htmlCode .= '<p>Available '.$channel->post_title.' videos: ' . VWvideoShare::importFilesCount( $channel->post_title, array('flv', 'mp4', 'f4v'), $options['vwls_archive_path']) .'</p>';

			$link  = add_query_arg( array( 'archive_import' => $channel->post_title), get_permalink() );
			$htmlCode .= '<a class="videowhisperButton g-btn type_blue" href="' .$link.'">Import</a>';

			$htmlCode .= '<h4>Channel Videos</h4>';

			$htmlCode .= do_shortcode('[videowhisper_videos perpage="4" archive="'.$channel->post_name.'"]');

			$htmlCode .= '</div>';

			return $htmlCode;
		}

		function vw_ls_manage_channels_head($val)
		{
			$htmlCode = '';

			if ($channel_name = sanitize_file_name($_GET['archive_import']))
			{

				$options = get_option( 'VWvideoShareOptions' );

				$url  = add_query_arg( array( 'archive_import' => $channel_name), get_permalink() );


				$htmlCode .=  '<form id="videowhisperImport" name="videowhisperImport" action="' . $url . '" method="post">';

				$htmlCode .= "<h3>Import <b>" . $channel_name . "</b> Videos to Archive</h3>";

				$htmlCode .= VWvideoShare::importFilesSelect( $channel_name, array('flv', 'mp4', 'f4v'), $options['vwls_archive_path']);

				$htmlCode .=  '<input type="hidden" name="archive" id="archive" value="' . $channel_name . '">';
				$htmlCode .=   '<INPUT class="button button-primary" TYPE="submit" name="import" id="import" value="Import">';

				$htmlCode .=  ' <INPUT class="button button-primary" TYPE="submit" name="delete" id="delete" value="Delete">';

				$htmlCode .=  '</form>';
			}

			return $htmlCode;
		}

		function humanDuration($t,$f=':') // t = seconds, f = separator
			{
			return sprintf("%02d%s%02d%s%02d", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
		}

		function humanAge($t)
		{
			if ($t<30) return "NOW";
			return sprintf("%d%s%d%s%d%s", floor($t/86400), 'd ', ($t/3600)%24,'h ', ($t/60)%60,'m');
		}


		function humanFilesize($bytes, $decimals = 2) {
			$sz = 'BKMGTP';
			$factor = floor((strlen($bytes) - 1) / 3);
			return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
		}

		function path2url($file, $Protocol='http://') {
			return $Protocol.$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
		}

		function path2stream($path)
		{
			$options = get_option( 'VWvideoShareOptions' );

			$stream = substr($path, strlen($options['streamsPath']));
			if ($stream[0]=='/') $stream = substr($stream,1);

			if (!file_exists($options['streamsPath'] . '/' . $stream)) return '';
			else return $stream;
		}

		function importFilesSelect($prefix, $extensions, $folder)
		{
			if (!file_exists($folder)) return "<div class='error'>Video folder not found: $folder !</div>";

			$htmlCode .= '';

			//import files
			if ($_POST['import'])
			{

				if (count($importFiles = $_POST['importFiles']))
				{

					$owner = (int) $_POST['owner'];

					global $current_user;
					get_currentuserinfo();

					if (!$owner) $owner = $current_user->ID;
					elseif ($owner != $current_user->ID && !is_admin()) return "Only admin can import for others!";

					$archive = sanitize_file_name($_POST['archive']);
					if (!$archive) return "Importing requires an archive name!";


					foreach ($importFiles as $fileName)
					{
						$fileName = sanitize_file_name($fileName);
						$ext = pathinfo($fileName, PATHINFO_EXTENSION);
						if (!$ztime = filemtime($folder . $fileName)) $ztime = time();
						$videoName = basename($fileName, '.' . $ext) .' '. date("M j", $ztime);

						$htmlCode .= VWvideoShare::importFile($folder . $fileName, $videoName, $owner, $archive);
					}
				}else $htmlCode .= '<div class="warning">No files selected to import!</div>';

			}

			//delete files
			if ($_POST['delete'])
			{

				if (count($importFiles = $_POST['importFiles']))
				{
					foreach ($importFiles as $fileName)
					{
						$htmlCode .= '<BR>Deleting '.$fileName.' ... ';
						$fileName = sanitize_file_name($fileName);
						if (!unlink($folder . $fileName)) $htmlCode .= 'Removing file failed!';
						else $htmlCode .= 'Success.';

					}
				}else $htmlCode .= '<div class="warning">No files selected to delete!</div>';
			}

			//preview file
			if ($preview_name = sanitize_file_name($_GET['import_preview']))
			{
				$preview_url = VWvideoShare::path2url($folder . $preview_name);
				$player_url = plugin_dir_url(__FILE__) . 'strobe/StrobeMediaPlayback.swf';
				$flashvars ='src=' .urlencode($preview_url). '&autoPlay=true';

				$htmlCode .= '<h4>Preview '.$preview_name.'</h4>';

				$htmlCode .= '<object class="previewPlayer" width="480" height="360" type="application/x-shockwave-flash" data="' . $player_url . '"> <param name="movie" value="' . $player_url . '" /><param name="flashvars" value="' .$flashvars . '" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="wmode" value="direct" /></object>';
			}

			//list files
			$fileList = scandir($folder);

			$ignored = array('.', '..', '.svn', '.htaccess');

			$prefixL=strlen($prefix);

			//list by date
			$files = array();
			foreach ($fileList as $fileName)
			{

				if (in_array($fileName, $ignored)) continue;
				if (!in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $extensions  )) continue;
				if ($prefixL) if (substr($fileName,0,$prefixL) != $prefix) continue;

					$files[$fileName] = filemtime($folder . $fileName);
			}

			arsort($files);
			$fileList = array_keys($files);

			if (!$fileList) $htmlCode .=  "<div class='warning'>No matching videos found!</div>";
			else
			{
				$htmlCode .=
					'<script language="JavaScript">
function toggleImportBoxes(source) {
  var checkboxes = new Array();
  checkboxes = document.getElementsByName(\'importFiles\');
  for (var i = 0; i < checkboxes.length; i++)
    checkboxes[i].checked = source.checked;
}
</script>';
				$htmlCode .=  "<table class='widefat videowhisperTable'>";
				$htmlCode .=  '<thead class=""><tr><th><input type="checkbox" onClick="toggleImportBoxes(this)" /></th><th>File Name</th><th>Preview</th><th>Size</th><th>Date</th></tr></thead>';

				foreach ($fileList as $fileName)
				{
					$htmlCode .=  '<tr>';
					$htmlCode .= '<td><input type="checkbox" name="importFiles[]" value="' . $fileName .'"'. ($fileName==$preview_name?' checked':'').'></td>';
					$htmlCode .=  "<td>$fileName</td>";
					$htmlCode .=  '<td>';
					$link  = add_query_arg( array( 'archive_import' => $prefix, 'import_preview' => $fileName), get_permalink() );

					$htmlCode .=  " <a class='size_small g-btn type_blue' href='" . $link ."'>Play</a> ";
					echo '</td>';
					$htmlCode .=  '<td>' .  VWvideoShare::humanFilesize(filesize($folder . $fileName)) . '</td>';
					$htmlCode .=  '<td>' .  date('jS F Y H:i:s', filemtime($folder  . $fileName)) . '</td>';
					$htmlCode .=  '</tr>';
				}
				$htmlCode .=  "</table>";

			}
			return $htmlCode;

		}

		function importFilesCount($prefix, $extensions, $folder)
		{
			if (!file_exists($folder)) return '';

			$kS=$k=0;

			$fileList = scandir($folder);

			$ignored = array('.', '..', '.svn', '.htaccess');

			$prefixL=strlen($prefix);

			foreach ($fileList as $fileName)
			{

				if (in_array($fileName, $ignored)) continue;
				if (!in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $extensions  )) continue;
				if ($prefixL) if (substr($fileName,0,$prefixL) != $prefix) continue;

					$k++;
				$kS+=filesize($folder . $fileName);
			}

			return $k . ' ('.VWvideoShare::humanFilesize($kS).')';
		}


		function importFile($path, $name, $owner, $archive)
		{

			if (!file_exists($path)) return "<br>$name:File missing: $path";
			if (!$owner) return "<br>Missing owner!";
			if (!$archive) return "<br>Missing archive!";

			$htmlCode = '';

			$options = get_option( 'VWvideoShareOptions' );

			//uploads/owner/archive/src/file
			$dir = $options['uploadsPath'];
			if (!file_exists($dir)) mkdir($dir);

			$dir .= '/' . $owner;
			if (!file_exists($dir)) mkdir($dir);

			$dir .= '/' . $archive;
			if (!file_exists($dir)) mkdir($dir);

			//$dir .= '/src';
			//if (!file_exists($dir)) mkdir($dir);

			if (!$ztime = filemtime($path)) $ztime = time();

			$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
			$newFile = md5(uniqid($owner, true))  . '.' . $ext;
			$newPath = $dir . '/' . $newFile;

			$htmlCode .= "<br>Importing $name as $newFile ... ";

			if (!rename($path, $newPath))
			{
				$htmlCode .= 'Rename failed. Trying copy ...';
				if (!copy($path, $newPath))
				{
					$htmlCode .= 'Copy also failed. Import failed!';
					return $htmlCode;
				} else $htmlCode .= 'Copy success ...';

				if (!unlink($path)) $htmlCode .= 'Removing original file failed!';
			}
			$htmlCode .= 'Archived source file ...';

			$postdate = date("Y-m-d H:i:s", $ztime);

			$post = array(
				'post_name'      => $name,
				'post_title'     => $name,
				'post_author'    => $owner,
				'post_type'      => 'video',
				'post_status'    => 'publish',
				'post_date'   => $postdate

			);

			$post_id = wp_insert_post( $post);
			if ($post_id)
			{
				update_post_meta( $post_id, 'video-source-file', $newPath );
				wp_set_object_terms($post_id, $archive, 'archive');

				VWvideoShare::updatePostDuration($post_id, true);
				VWvideoShare::updatePostThumbnail($post_id, true);
				VWvideoShare::convertVideo($post_id, true);

				$htmlCode .= 'Video post created: <a href='.get_post_permalink($post_id).'> #'.$post_id.' '.$name.'</a> . Snapshot, video info and thumbnail will be processed shortly.' ;
			}
			else $htmlCode .= 'Video post creation failed!';

			return $htmlCode;
		}

		/* Meta box setup function. */
		function post_meta_boxes_setup() {
			/* Add meta boxes on the 'add_meta_boxes' hook. */
			add_action( 'add_meta_boxes', array( 'VWvideoShare', 'add_post_meta_boxes' ) );

			/* Save post meta on the 'save_post' hook. */
			add_action( 'save_post', array( 'VWvideoShare', 'save_post_meta'), 10, 2 );
		}


		/* Create one or more meta boxes to be displayed on the post editor screen. */
		function add_post_meta_boxes() {

			add_meta_box(
				'video-post',      // Unique ID
				esc_html__( 'Video Post' ),    // Title
				array( 'VWvideoShare', 'post_meta_box'),   // Callback function
				'video',         // Admin page (or post type)
				'normal',         // Context
				'high'         // Priority
			);
		}

		/* Display the post meta box. */
		function post_meta_box( $object, $box ) { ?>

  <?php wp_nonce_field( basename( __FILE__ ), 'video_post_nonce' ); ?>
  <p>
    <label for="video-source-file"><?php _e( "Path to source video file" ); ?></label>
    <br />
    <input class="widefat" type="text" name="video-source-file" id="video-source-file" value="<?php echo esc_attr( get_post_meta( $object->ID, 'video-source-file', true ) ); ?>" size="30" />
  </p>

<?php }

		/* Save the meta box's post metadata. */
		function save_post_meta( $post_id, $post ) {

			/* Verify the nonce before proceeding. */
			if ( !isset( $_POST['video_post_nonce'] ) || !wp_verify_nonce( $_POST['video_post_nonce'], basename( __FILE__ ) ) )
				return $post_id;

			/* Get the post type object. */
			$post_type = get_post_type_object( $post->post_type );

			/* Check if the current user has permission to edit the post. */
			if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
				return $post_id;

			foreach (array('video-source-file') as $meta_key)
			{
				/* Get the posted data and sanitize it for use as an HTML class. */
				$new_meta_value = ( isset( $_POST[$meta_key] ) ? $_POST[$meta_key] : '' );

				/* Get the meta value of the custom field key. */
				$meta_value = get_post_meta( $post_id, $meta_key, true );

				/* If a new meta value was added and there was no previous value, add it. */
				if ( $new_meta_value && '' == $meta_value )
					add_post_meta( $post_id, $meta_key, $new_meta_value, true );

				/* If the new meta value does not match the old value, update it. */
				elseif ( $new_meta_value && $new_meta_value != $meta_value )
					update_post_meta( $post_id, $meta_key, $new_meta_value );

				/* If there is no new meta value but an old value exists, delete it. */
				elseif ( '' == $new_meta_value && $meta_value )
					delete_post_meta( $post_id, $meta_key, $meta_value );
			}


		}

		function columns_head_video($defaults) {
			$defaults['featured_image'] = 'Thumbnail';
			$defaults['duration'] = 'Duration &amp; Info';

			return $defaults;
		}

		function columns_register_sortable( $columns ) {
			$columns['duration'] = 'duration';

			return $columns;
		}


		function columns_content_video($column_name, $post_id)
		{

			if ($column_name == 'featured_image')
			{
				$post_thumbnail_id = get_post_thumbnail_id($post_id);

				if ($post_thumbnail_id)
				{
					$post_featured_image = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview');

					if ($post_featured_image)
					{
						echo '<img src="' . $post_featured_image[0] . '" />';
					}

				}
				else
				{
					echo 'Generating ... ';
					VWvideoShare::updatePostThumbnail($post_id);

				}
			}

			if ($column_name == 'duration')
			{
				$videoDuration = get_post_meta($post_id, 'video-duration', true);
				if ($videoDuration)
				{
					echo 'Duration: ' . VWvideoShare::humanDuration($videoDuration);
					echo '<br>Resolution: ' . get_post_meta($post_id, 'video-width', true). 'x' . get_post_meta($post_id, 'video-height', true);
					echo '<br>Bitrate: '. get_post_meta($post_id, 'video-bitrate', true) . ' kbps';
					echo '<br>Source Size: ' . VWvideoShare::humanFilesize(get_post_meta($post_id, 'video-source-size', true));

					$url  = add_query_arg( array( 'updateVideo'  => $post_id), admin_url('edit.php?post_type=video') );

					echo '<br><a href="'.$url.'">Update Info</a>';
				}
				else
				{
					echo 'Retrieving Info...';
					VWvideoShare::updatePostDuration($update_id, true);
				}

			}

		}

		function post_edit_screen($query)
		{
			global $pagenow;

			if (is_admin() && $pagenow=='edit.php')
			{

				if ($update_id = (int) $_GET['updateVideo'])
				{
					//echo 'Updating #' .$update_id. '... <br>';
					VWvideoShare::updatePostDuration($update_id, true);
					VWvideoShare::updatePostThumbnail($update_id, true);
					VWvideoShare::convertVideo($update_id, true);
				}

			}
		}

		function duration_column_orderby( $vars ) {
			if ( isset( $vars['orderby'] ) && 'duration' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
						'meta_key' => 'video-duration',
						'orderby' => 'meta_value_num'
					) );
			}

			return $vars;
		}

		function setupOptions() {

			$root_url = get_bloginfo( "url" ) . "/";
			$upload_dir = wp_upload_dir();

			$adminOptions = array(
				'disablePage' => '0',
				'vwls_archive' => '1',
				'vwls_archive_path' =>'/home/youraccount/public_html/streams/',
				'vwls_channel' => '1',
				'ffmpegPath' => '/usr/local/bin/ffmpeg',
				'player_default' => 'strobe',
				'player_default_ios' => 'html5',
				'player_default_safari' => 'html5',
				'thumbWidth' => '240',
				'thumbHeight' => '180',
				'perPage' =>'6',
				'uploadsPath' => $upload_dir['basedir'] . '/vw_videoshare',
				'rtmpServer' => 'rtmp://your-site.com/videowhisper-x2',
				'streamsPath' =>'/home/youraccount/public_html/streams/',
				'hlsServer' =>'http://your-site.com:1935/videowhisper-x2/',
				'customCSS' => <<<HTMLCODE
<style type="text/css">

.videowhisperVideo
{
position: relative;
display:inline-block;

border:1px solid #aaa;
background-color:#777;
padding: 0px;
margin: 2px;

width: 240px;
height: 180px;
}

.videowhisperVideo:hover {
	border:1px solid #fff;
}

.videowhisperVideo IMG
{
padding: 0px;
margin: 0px;
border: 0px;
}

.videowhisperTitle
{
position: absolute;
top:5px;
left:5px;
font-size: 20px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperTime
{
position: absolute;
bottom:8px;
left:5px;
font-size: 15px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperDate
{
position: absolute;
bottom:8px;
right:5px;
font-size: 15px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperButton {
	-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
	box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-border-top-left-radius:6px;
	-moz-border-radius-topleft:6px;
	border-top-left-radius:6px;
	-webkit-border-top-right-radius:6px;
	-moz-border-radius-topright:6px;
	border-top-right-radius:6px;
	-webkit-border-bottom-right-radius:6px;
	-moz-border-radius-bottomright:6px;
	border-bottom-right-radius:6px;
	-webkit-border-bottom-left-radius:6px;
	-moz-border-radius-bottomleft:6px;
	border-bottom-left-radius:6px;
	text-indent:0;
	border:1px solid #dcdcdc;
	display:inline-block;
	color:#666666;
	font-family:Verdana;
	font-size:15px;
	font-weight:bold;
	font-style:normal;
	height:50px;
	line-height:50px;
	width:200px;
	text-decoration:none;
	text-align:center;
	text-shadow:1px 1px 0px #ffffff;
	background-color:#e9e9e9;

}

.videowhisperButton:hover {
	background-color:#f9f9f9;
}

.videowhisperButton:active {
	position:relative;
	top:1px;
}
</style>

HTMLCODE
				,

				'videowhisper' => 0
			);

			$options = get_option('VWvideoShareOptions');
			if (!empty($options)) {
				foreach ($options as $key => $option)
					$adminOptions[$key] = $option;
			}
			update_option('VWvideoShareOptions', $adminOptions);

			return $adminOptions;
		}

		function adminDocs()
		{
?>
		<div class="wrap">
<?php screen_icon(); ?>
		<h2>Video Share / Video on Demand (VOD)</h2>
		<h3>Shortcodes</h3>

		<h4>[videowhisper_videos archive="" perpage="" perrow=""]</h4>
		Displays video list. Loads and updates by AJAX. Optional parameters: video archive name, maximum videos per page, maximum videos per row.

		<h4>[videowhisper_player video="0"]</h4>
		Displays video player. Video post ID is required.
		</div>
		<?php
		}

		function adminOptions()
		{
			$options = VWvideoShare::setupOptions();

			if (isset($_POST))
			{
				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = trim($_POST[$key]);
					update_option('VWvideoShareOptions', $options);
			}

			/*
            $page_id = get_option("vwvs_page_import");
            if ($page_id != '-1' && $options['disablePage']!='0') VWvideoShare::deletePages();
*/


			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'server';
?>


<div class="wrap">
<?php screen_icon(); ?>
<h2>Video Share / Video on Demand (VOD)</h2>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo get_permalink(); ?>admin.php?page=video-share&tab=server" class="nav-tab <?php echo $active_tab=='server'?'nav-tab-active':'';?>">Server</a>
	<a href="<?php echo get_permalink(); ?>admin.php?page=video-share&tab=display" class="nav-tab <?php echo $active_tab=='display'?'nav-tab-active':'';?>">Display</a>
	<a href="<?php echo get_permalink(); ?>admin.php?page=video-share&tab=ls" class="nav-tab <?php echo $active_tab=='ls'?'nav-tab-active':'';?>">Live Streaming</a>
</h2>

<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

<?php
			switch ($active_tab)
			{
			case 'server':
?>
<h3>Server Configuration</h3>

<h4>Uploads Path</h4>
<p>Path where video files will be stored. Make sure you use a location outside plugin folder to avoid losing files on updates and plugin uninstallation.</p>
<input name="uploadsPath" type="text" id="uploadsPath" size="80" maxlength="256" value="<?php echo $options['uploadsPath']?>"/>
<br>Ex: /home/-your-account-/public_html/wp-content/uploads/vw_videoshare
<br>Ex: /home/-your-account-/public_html/streams/videoshare
<br>If you ever decide to change this, previous files must remain in old location.

<h4>FFMPEG Path</h4>
<p>Path to latest FFMPEG. Required for extracting snapshots, info and converting videos.</p>
<input name="ffmpegPath" type="text" id="ffmpegPath" size="100" maxlength="256" value="<?php echo $options['ffmpegPath']?>"/>
<?php
				echo "<BR>FFMPEG: ";
				$cmd = $options['ffmpegPath'] . ' -codecs';
				exec($cmd, $output, $returnvalue);
				if ($returnvalue == 127)  echo "not detected: $cmd"; else echo "detected";

				//detect codecs
				if ($output) if (count($output))
						foreach (array('h264','faac','speex', 'nellymoser') as $cod)
						{
							$det=0; $outd="";
							echo "<BR>$cod codec: ";
							foreach ($output as $outp) if (strstr($outp,$cod)) { $det=1; $outd=$outp; };
							if ($det) echo "detected ($outd)"; else echo "missing: please configure and install ffmpeg with $cod";
						}
?>

<h4>RTMP Address</h4>
<p>Optional: Required only for RTMP playback.
<br>RTMP application address for playback.</p>
<input name="rtmpServer" type="text" id="rtmpServer" size="80" maxlength="256" value="<?php echo $options['rtmpServer']?>"/>
<br>Ex: rtmp://your-site.com/videowhisper-x2
<br>Do not use a rtmp address that requires some form of authentication or verification done by another web script as player will not be able to connect.
<br>Avoid using a shared rtmp address. Setup a special rtmp application for playback of videos. For Wowza configure &lt;StreamType&gt;file&lt;/StreamType&gt;.

<h4>RTMP Streams Path</h4>
<p>Optional: Required only for RTMP playback.
<br>Path where rtmp server is configured to stream videos from. Uploads path must be a subfolder of this path to allow rtmp access to videos. </p>
<input name="streamsPath" type="text" id="streamsPath" size="80" maxlength="256" value="<?php echo $options['streamsPath']?>"/>
<br>This must be a substring of, or same as Uploads Path.
<br>Ex: /home/your-account/public_html/streams
<?php
					if (!strstr($options['uploadsPath'], $options['streamsPath']))
						echo '<br><b class="error">Current value seems wrong!</b>';
					else echo '<br>Current value seems fine.';
?>
<h4>HLS URL</h4>
<p>Optional: Required only for HLS playback.
<br>HTTP address to access by HTTP Live Streaming (HLS).</p>
<input name="hlsServer" type="text" id="hlsServer" size="80" maxlength="256" value="<?php echo $options['hlsServer']?>"/>
<br>Ex: http://your-site.com:1935/videowhisper-x2/
<br>For Wowza disable live packetizers: &lt;LiveStreamPacketizers&gt;&lt;/LiveStreamPacketizers&gt;.
<?php
					break;
			case 'ls':
?>
<h3>Live Streaming</h3>
<p>
VideoWhisper Live Streaming is a plugin that allows users to broadcast live video channels.
<br>Detection:
<?php
				if (class_exists("VWliveStreaming")) echo "Installed."; else echo "Not detected. Please install and activate plugin to use this functionality."
?>
</p>

<h4>Import Live Streaming Archives</h4>
Enables Live Streaming channel owners to import archived streams. Videos must be archived locally.
<br><select name="vwls_archive" id="vwls_archive">
  <option value="1" <?php echo $options['vwls_archive']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['vwls_archive']?"":"selected"?>>No</option>
</select>

<h4>List Channel Videos</h4>
List videos on channel.
<br><select name="vwls_channel" id="vwls_channel">
  <option value="1" <?php echo $options['vwls_channel']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['vwls_channel']?"":"selected"?>>No</option>
</select>

<h4>Path to Video Archive</h4>
<input name="vwls_archive_path" type="text" id="vwls_archive_path" size="80" maxlength="256" value="<?php echo $options['vwls_archive_path']; ?>"/>
<br>Ex: /home/your-account/public_html/streams/
<br>When using Wowza Streaming Engine configure [install-dir]/conf/Server.xml to save as FLV instead of MP4:
<br>&lt;DefaultStreamPrefix&gt;flv&lt;/DefaultStreamPrefix&gt;
<br>FLV includes support for web based flash audio codecs.

<?php
				break;
			case 'display':

				$options['customCSS'] = htmlentities(stripslashes($options['customCSS']));
?>
<h3>Display &amp; Listings</h3>


<h4>Default Player</h4>
<select name="player_default" id="player_default">
  <option value="strobe" <?php echo $options['player_default']=='strobe'?"selected":""?>>Strobe</option>
  <option value="html5" <?php echo $options['player_default']=='html5'?"selected":""?>>HTML5</option>
   <option value="strobe-rtmp" <?php echo $options['player_default']=='strobe-rtmp'?"selected":""?>>Strobe RTMP</option>
</select>

<h4>Default Player on Safari</h4>
<select name="player_default_safari" id="player_default_safari">
  <option value="strobe" <?php echo $options['player_default_safari']=='strobe'?"selected":""?>>Strobe</option>
  <option value="html5" <?php echo $options['player_default_safari']=='html5'?"selected":""?>>HTML5</option>
   <option value="strobe-rtmp" <?php echo $options['player_default_safari']=='strobe-rtmp'?"selected":""?>>Strobe RTMP</option>
   <option value="hls" <?php echo $options['player_default_safari']=='hls'?"selected":""?>>HTML5 HLS</option>
</select>

<h4>Default Player on iOS</h4>
<select name="player_default_ios" id="player_default_ios">
  <option value="html5" <?php echo $options['player_default_ios']=='html5'?"selected":""?>>HTML5</option>
   <option value="hls" <?php echo $options['player_default_ios']=='hls'?"selected":""?>>HTML5 HLS</option>
</select>




<h4>Default Videos Per Page</h4>
<input name="perPage" type="text" id="perPage" size="3" maxlength="3" value="<?php echo $options['perPage']?>"/>


<h4>Thumbnail Width</h4>
<input name="thumbWidth" type="text" id="thumbWidth" size="4" maxlength="4" value="<?php echo $options['thumbWidth']?>"/>

<h4>Thumbnail Height</h4>
<input name="thumbHeight" type="text" id="thumbHeight" size="4" maxlength="4" value="<?php echo $options['thumbHeight']?>"/>

<h4>Custom CSS</h4>
<textarea name="customCSS" id="customCSS" cols="64" rows="5"><?php echo $options['customCSS']?></textarea>
<BR>Styling used in elements added by this plugin. Must include CSS container &lt;style type=&quot;text/css&quot;&gt; &lt;/style&gt; .
<?php
				break;
			}

			if (!in_array($active_tab, array( 'shortcodes')) ) submit_button(); ?>

</form>
</div>
	 <?php
		}

		function adminLiveStreaming()
		{
			$options = get_option( 'VWvideoShareOptions' );

			echo '<h3>Import Archived Channel Videos</h3>';


			if ($channel_name = sanitize_file_name($_GET['archive_import']))
			{

				$url  = add_query_arg( array( 'archive_import' => $channel_name), admin_url('admin.php?page=video-share-ls') );


				echo '<form action="' . $url . '" method="post">';
				echo "<h4>Import <b>" . $channel_name . "</b> Videos to Archive</h4>";
				echo VWvideoShare::importFilesSelect( $channel_name, array('flv', 'mp4', 'f4v'), $options['vwls_archive_path']);
				echo '<INPUT class="button button-primary" TYPE="submit" name="import" id="import" value="Import">';
				global $wpdb;
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . sanitize_file_name($channel_name) . "' and post_type='channel' LIMIT 0,1" );
				if ($postID)
				{
					$channel = get_post( $postID );
					$owner = $channel->post_author;
				}
				else
				{
					global $current_user;
					get_currentuserinfo();
					$owner = $current_user->ID;
					echo ' as ' . $current_user->display_name;
				}

				echo '<input type="hidden" name="archive" id="archive" value="' . $channel_name . '">';
				echo '<input type="hidden" name="owner" id="owner" value="' . $owner . '">';

				echo ' <INPUT class="button button-primary" TYPE="submit" name="delete" id="delete" value="Delete">';

				echo '</form>';
			}


			echo "<h4>Recent Activity</h4>";

			function format_age($t)
			{
				if ($t<30) return "LIVE";
				return sprintf("%d%s%d%s%d%s", floor($t/86400), 'd ', ($t/3600)%24,'h ', ($t/60)%60,'m');
			}


			global $wpdb;
			$table_name3 = $wpdb->prefix . "vw_lsrooms";
			$items =  $wpdb->get_results("SELECT * FROM `$table_name3` ORDER BY edate DESC LIMIT 0, 100");
			echo "<table class='wp-list-table widefat'><thead><tr><th>Channel</th><th>Videos</th><th>Actions</th><th>Last Access</th><th>Type</th></tr></thead>";
			if ($items) foreach ($items as $item)
					if (($fcount = VWvideoShare::importFilesCount( $item->name, array('flv', 'mp4', 'f4v'), $options['vwls_archive_path']))!='0 (0.00B)')
					{
						echo "<tr><th>" . $item->name . "</th>";

						echo "<td>". $fcount . "</td>";

						$link  = add_query_arg( array( 'archive_import' => $item->name), admin_url('admin.php?page=video-share-ls') );

						echo '<td><a href="' .$link.'">Import</a></td>';
						echo "<td>".format_age(time() - $item->edate)."</td>";
						echo '<td>' . ($item->type==2?"Premium":"Standard") . '</td>';
						echo "</tr>";
					}
				echo '<tr><th>Total</th><th colspan="4">' . VWvideoShare::importFilesCount( '', array('flv', 'mp4', 'f4v'), $options['vwls_archive_path']) . '</th></tr>';
			echo "</table>";
		}
		//fc above
	}
}

//instantiate
if (class_exists("VWvideoShare")) {
	$videoShare = new VWvideoShare();
}

//Actions and Filters
if (isset($videoShare)) {

	register_activation_hook( __FILE__, array(&$videoShare, 'install' ) );

	add_action( 'init', array(&$videoShare, 'video_post'));
	add_action('admin_menu', array(&$videoShare, 'adminMenu'));
	add_action("plugins_loaded", array(&$videoShare , 'init'));

}
?>